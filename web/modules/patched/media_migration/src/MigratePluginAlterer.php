<?php

namespace Drupal\media_migration;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\field\Plugin\migrate\source\d7\Field;
use Drupal\field\Plugin\migrate\source\d7\FieldInstance;
use Drupal\field\Plugin\migrate\source\d7\ViewMode;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Plugin\MigrateSourcePluginManager;
use Drupal\migrate\Plugin\MigrationDeriverTrait;
use Drupal\migrate\Plugin\MigrationPluginManager;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\FieldMigration;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;
use Psr\Log\LoggerInterface;

/**
 * MigratePluginAlterer service.
 */
class MigratePluginAlterer {

  use MigrationDeriverTrait;

  /**
   * The plugin.manager.migration service.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManager
   */
  protected $pluginManagerMigration;

  /**
   * The Migrate source plugin manager service.
   *
   * @var \Drupal\migrate\Plugin\MigrateSourcePluginManager
   */
  protected $sourceManager;

  /**
   * The plugin.manager.media_wysiwyg service.
   *
   * @var \Drupal\media_migration\MediaWysiwygPluginManager
   */
  protected $pluginManagerMediaWysiwyg;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The type of the embed token transformation.
   *
   * This is 'entity_embed' or NULL, depending on whether the destination
   * module is available or not.
   *
   * @var string|null
   */
  protected $embedTokenTransformType;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a MigratePluginAlterer object.
   *
   * @param \Drupal\migrate\Plugin\MigrationPluginManager $plugin_manager_migration
   *   The migration plugin manager.
   * @param \Drupal\migrate\Plugin\MigrateSourcePluginManager $source_manager
   *   The Migrate source plugin manager.
   * @param \Drupal\media_migration\MediaWysiwygPluginManager $plugin_manager_media_wysiwyg
   *   The Media WYSIWYG plugin manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(MigrationPluginManager $plugin_manager_migration, MigrateSourcePluginManager $source_manager, MediaWysiwygPluginManager $plugin_manager_media_wysiwyg, LoggerInterface $logger, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager) {
    $this->pluginManagerMigration = $plugin_manager_migration;
    $this->sourceManager = $source_manager;
    $this->pluginManagerMediaWysiwyg = $plugin_manager_media_wysiwyg;
    $this->logger = $logger;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Alters migrate plugins.
   *
   * @param array $migrations
   *   The array of migration plugins.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If a plugin cannot be found.
   */
  public function alter(array &$migrations) {
    $this->alterFieldMigrations($migrations);
    $this->addMediaWysiwygProcessor($migrations);
    $this->alterFilterFormatMigration($migrations);
  }

  /**
   * Alters field migrations from file_entity/media in 7 to media in 8.
   *
   * @param array $migrations
   *   The array of migration plugins.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If a plugin cannot be found.
   */
  protected function alterFieldMigrations(array &$migrations) {
    // Collect all file entity -> media entity migration which have additional
    // fields. Although these migrations depend on the "d7_field_instance"
    // migration (and implicitly on "d7_field" migration), it isn't enough:
    // the parent media type entity has to exist prior its all field storage
    // entity being created, so "d7_field" (and maybe "d7_field_instance") has
    // to depend on the media type migrations which have additional fields.
    $media_types_with_fields_migration_ids = array_reduce($migrations, function (array $carry, array $migration_def) {
      $deps_required = isset($migration_def['migration_dependencies']['required'])
        ? $migration_def['migration_dependencies']['required']
        : [];

      if (!in_array('d7_field_instance', $deps_required, TRUE)) {
        return $carry;
      }

      foreach ($deps_required as $requirement) {
        $requirement_parts = explode(PluginBase::DERIVATIVE_SEPARATOR, $requirement);
        if ($requirement_parts[0] === 'd7_file_entity_type') {
          $carry[$requirement] = $requirement;
        }
      }
      return $carry;
    }, []);

    foreach ($migrations as &$migration) {
      // If this is not a Drupal 7 migration, we can skip processing it.
      if (!in_array('Drupal 7', $migration['migration_tags'] ?? [])) {
        continue;
      }
      $migration_stub = $this->pluginManagerMigration->createStubMigration($migration);
      $source = NULL;
      $configuration = $migration['source'];
      if (!empty($migration['source']['plugin'])) {
        $source = $this->sourceManager->createInstance($migration['source']['plugin'], $configuration, $migration_stub);
        if (is_a($migration['class'], FieldMigration::class, TRUE)) {

          // Field storage, instance, widget and formatter migrations.
          if (is_a($source, Field::class) || is_a($source, FieldInstance::class)) {
            static::mapMigrationProcessValueToMedia($migration, 'entity_type');
          }
        }

        // View Modes.
        if (is_a($source, ViewMode::class)) {
          static::mapMigrationProcessValueToMedia($migration, 'targetEntityType');
        }

        // D7 field storage and field instance migrations should depend on
        // media types which have extra fields, because field storage config
        // entities require their host entity.
        $field_storage_and_instance_source_plugin_ids = [
          'd7_field',
          'd7_field_instance',
        ];
        if (
          in_array($migration['source']['plugin'], $field_storage_and_instance_source_plugin_ids) &&
          !empty($media_types_with_fields_migration_ids)
        ) {
          $required_migration_deps = isset($migration['migration_dependencies']['required'])
            ? $migration['migration_dependencies']['required']
            : [];
          $migration['migration_dependencies']['required'] = array_unique(
            array_merge(
              $required_migration_deps,
              array_values($media_types_with_fields_migration_ids)
            )
          );
        }
      }
    }
  }

  /**
   * Appends a processor to transform media_wysiwyg tokens to entity_embeds.
   *
   * Find field instances with text processing and pass them to a
   * MediaWysiwyg plugin that will add processors to the respective
   * migrations.
   *
   * @see \Drupal\media_migration\Plugin\MediaWysiwyg\Node
   */
  protected function addMediaWysiwygProcessor(array &$migrations) {
    $field_instance_migration = static::getSourcePlugin('d7_field_instance');

    try {
      assert($field_instance_migration instanceof DrupalSqlBase);
      $field_instance_migration->checkRequirements();
    }
    catch (RequirementsException $e) {
      // This exception happens when we run migrations with migrate:import or
      // check the status with migrate:update. We just we need to run the
      // code below when we are generating migrations with migrate:upgrade so
      // it is safe to just return here if we reach to this point.
      return;
    }

    $entity_destination_plugins = [
      'entity',
      'entity_revision',
      'entity_complete',
      'entity_reference_revisions',
    ];
    $content_entity_migrations = array_filter($migrations, function (array $migration) use ($entity_destination_plugins) {
      if (!in_array('Drupal 7', $migration['migration_tags'] ?? [], TRUE)) {
        return FALSE;
      }
      $destination_parts = explode(PluginBase::DERIVATIVE_SEPARATOR, $migration['destination']['plugin']);
      if (
        count($destination_parts) !== 2 ||
        !in_array($destination_parts[0], $entity_destination_plugins)
      ) {
        return FALSE;
      }
      $destination_entity_type = $destination_parts[1];
      $definition = $this->entityTypeManager->getDefinition($destination_entity_type, FALSE);
      if ($definition instanceof ContentEntityTypeInterface) {
        return TRUE;
      }

      return FALSE;
    });
    $content_entity_destinations = [];
    foreach ($content_entity_migrations as $migration_plugin_id => $content_entity_migration) {
      $destination_entity_type = explode(PluginBase::DERIVATIVE_SEPARATOR, $content_entity_migration['destination']['plugin'])[1];
      $content_entity_destinations[$destination_entity_type][] = $migration_plugin_id;
    }

    foreach ($field_instance_migration as $row) {
      assert($row instanceof Row);
      if ($row->getSourceProperty('settings/text_processing') === 1) {
        $entity_type_id = $row->getSourceProperty('entity_type');
        $plugin_id = array_key_exists($entity_type_id, $content_entity_destinations) ? 'fallback' : $entity_type_id;

        try {
          $plugin = $this->pluginManagerMediaWysiwyg->createInstance($plugin_id);
          assert($plugin instanceof MediaWysiwygInterface);
          $migrations = $plugin->process($migrations, $row, $content_entity_destinations[$entity_type_id] ?? []);
          continue;
        }
        catch (PluginException $e) {
        }
        $this->logger->warning(sprintf('Could not find a MediaWysiwyg plugin "%s" for field "%s". You probably need to create a new one. Have a look at \Drupal\media_migration\Plugin\MediaWysiwyg\FieldCollectionItem for an example.', $entity_type_id, $row->getSourceProperty('field_name')));
      }
    }
  }

  /**
   * Maps Drupal 7 media_filter filter plugin to a Drupal 8|9 filter plugin.
   *
   * If Entity Embed module is installed on the destination site, this method
   * maps the media_embed filter plugin (Drupal 7 Media WYSIWYG module) to
   * entity_embed filter plugin (from the Entity Embed module).
   * If Entity Embed is unavailable, the media_filter filter will be mapped to
   * media_embed filter (from core Media Library module).
   *
   * @param array $migrations
   *   The array of migration plugins.
   */
  protected function alterFilterFormatMigration(array &$migrations) {
    $destination_filter_plugin_id = MediaMigration::getEmbedTokenDestinationFilterPlugin();
    // If entity_embed is not installed, the destination entity type of the
    // "d7_embed_button_media" migration is missing.
    if (!$this->moduleHandler->moduleExists('entity_embed')) {
      unset($migrations['d7_embed_button_media']);
    }

    if (isset($migrations['d7_filter_format']) && MediaMigration::embedTokenDestinationFilterPluginIsValid($destination_filter_plugin_id)) {
      $migrations['d7_filter_format']['process']['filters']['process']['id']['map']['media_filter'] = $destination_filter_plugin_id;
    }
    else {
      // We don't know the transform type or the filter format migration does
      // not exist.
      return;
    }

    if (MediaMigration::MEDIA_TOKEN_DESTINATION_FILTER_ENTITY_EMBED == $destination_filter_plugin_id && isset($migrations['d7_filter_format']) && isset($migrations['d7_embed_button_media'])) {
      $migrations['d7_filter_format']['migration_dependencies']['required'][] = 'd7_embed_button_media';
    }

    // We have to add <drupal-entity> or <drupal-media> to the allowed html
    // tag's list.
    if (isset($migrations['d7_filter_format'])) {
      $filter_plugin_settings_processes = static::makeAssociative($migrations['d7_filter_format']['process']['filters']['process']['settings']);
      $filter_plugin_settings_processes[] = [
        'plugin' => 'filter_settings_embed_media',
      ];
      $migrations['d7_filter_format']['process']['filters']['process']['settings'] = $filter_plugin_settings_processes;
    }
  }

  /**
   * Ensures that a plugin process mapping is an associative array.
   *
   * @param array|string $plugin_process
   *   The plugin process mapping.
   *
   * @return array
   *   The plugin process mapping as an associative array.
   */
  public static function makeAssociative($plugin_process) {
    if (!is_array($plugin_process)) {
      $plugin_process = [
        [
          'plugin' => 'get',
          'source' => $plugin_process,
        ],
      ];
    }
    elseif (array_key_exists('plugin', $plugin_process)) {
      $plugin_process = [$plugin_process];
    }

    return $plugin_process;
  }

  /**
   * Maps a migration's property from "file" to "media".
   *
   * @param array $migration
   *   The migration to alter.
   * @param string $property
   *   The property to change.
   */
  public static function mapMigrationProcessValueToMedia(array &$migration, string $property) {
    if (!empty($migration['source'][__FUNCTION__])) {
      return;
    }

    try {
      $value = static::getSourceValueOfMigrationProcess($migration, $property);
      switch ($value) {
        case 'file':
          $migration['source']["media_migration_$property"] = 'media';
          $migration['process'][$property] = "media_migration_$property";
          break;

        case NULL:
          // The value of the property cannot be determined, it might be a
          // dynamic value.
          $entity_type_process = static::makeAssociative($migration['process'][$property]);
          $entity_type_process[] = [
            'plugin' => 'static_map',
            'map' => [
              'file' => 'media',
            ],
            'bypass' => TRUE,
          ];
          $migration['process'][$property] = $entity_type_process;
          break;
      }
    }
    catch (\LogicException $e) {
      // The process property does not exists, nothing to do.
    }

    $migration['source'][__FUNCTION__] = TRUE;
  }

  /**
   * Gets the value of a process property if it is not dynamically calculated.
   *
   * @param array $migration
   *   The migration plugin's definition array.
   * @param string $process_property_key
   *   The property to check.
   *
   * @return mixed|null
   *   The value of the property if it can be determined, or NULL if it seems
   *   to be dynamic.
   *
   * @throws \LogicException.
   *   When the process property does not exists.
   */
  public static function getSourceValueOfMigrationProcess(array $migration, string $process_property_key) {
    if (
      !array_key_exists('process', $migration) ||
      !is_array($migration['process']) ||
      !array_key_exists($process_property_key, $migration['process'])
    ) {
      throw new \LogicException('No corresponding process found');
    }

    $property_processes = static::makeAssociative($migration['process'][$process_property_key]);
    $the_first_process = reset($property_processes);
    $property_value = NULL;

    if (
      !array_key_exists('source', $migration) ||
      count($property_processes) !== 1 ||
      $the_first_process['plugin'] !== 'get' ||
      empty($the_first_process['source'])
    ) {
      return NULL;
    }

    $process_value_source = $the_first_process['source'];

    // Parsing string values like "whatever" or "constants/whatever/key".
    // If the property is set to an already available value (e.g. a constant),
    // we don't need our special mapping applied.
    $property_value = NestedArray::getValue($migration['source'], explode(Row::PROPERTY_SEPARATOR, $process_value_source), $key_exists);

    // Migrations using the "embedded_data" source plugin actually contain
    // rows with source values.
    if (!$key_exists && $migration['source']['plugin'] === 'embedded_data') {
      $embedded_rows = $migration['source']['data_rows'] ?? [];
      $embedded_property_values = array_reduce($embedded_rows, function (array $carry, array $row) use ($process_value_source) {
        $embedded_value = NestedArray::getValue($row, explode(Row::PROPERTY_SEPARATOR, $process_value_source));
        $carry = array_unique(array_merge($carry, [$embedded_value]));
        return $carry;
      }, []);
      return count($embedded_property_values) === 1
        ? $embedded_property_values[0]
        : NULL;
    }

    return $key_exists ? $property_value : NULL;
  }

}

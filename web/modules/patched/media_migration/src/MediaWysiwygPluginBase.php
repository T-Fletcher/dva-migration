<?php

namespace Drupal\media_migration;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for media_wysiwyg plugins.
 */
abstract class MediaWysiwygPluginBase extends PluginBase implements MediaWysiwygInterface, ContainerFactoryPluginInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a MediaWysiwygPlugin instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
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
  protected function makeAssociative($plugin_process) {
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
   * Appends the media wysiwyg migrate processor to a field.
   *
   * @param array $migrations
   *   The aray of migrations.
   * @param string $migration_id
   *   The migration to adjust.
   * @param string $field_name_in_source
   *   The migration field name.
   *
   * @return array
   *   The updated array of migrations.
   */
  protected function appendProcessor(array $migrations, string $migration_id, string $field_name_in_source) {
    $extra_processes = [
      [
        'plugin' => 'media_wysiwyg_filter',
      ],
    ];
    $migration_processes = $migrations[$migration_id]['process'] ?? [];
    $processes_needs_extra_processor = [];

    // The field might be renamed or completely removed by others: Media
    // Migration should check the processes' source values.
    // @todo: Check subprocesses and find a way to handle array sources.
    foreach ($migration_processes as $process_key => $original_process) {
      $associative_process = $this->makeAssociative($original_process);

      foreach ($associative_process as $process_plugin_key => $process_plugin_config) {
        if (isset($process_plugin_config['source']) && $process_plugin_config['source'] === $field_name_in_source) {
          $processes_needs_extra_processor[$process_key][] = $process_plugin_key;
          $migration_processes[$process_key] = $associative_process;
        }
      }
    }

    // Add the "media_wysiwyg_filter" processor to the corresponding processes.
    foreach ($processes_needs_extra_processor as $process_key => $process_plugin_keys) {
      foreach ($process_plugin_keys as $process_plugin_key) {
        // The process should be added right after the collected key (since the
        // field value array might be converted to a string value what the
        // process plugin does not handle). Since the process pipeline not
        // always have auto-incremented integer keys, Media Migration has to
        // work with the "real" key positions.
        $real_process_key_position = array_search($process_plugin_key, array_keys($migration_processes[$process_key])) + 1;
        $leading_processes = array_slice($migration_processes[$process_key], 0, $real_process_key_position);
        $trailing_processes = array_slice($migration_processes[$process_key], $real_process_key_position);
        $migrations[$migration_id]['process'][$process_key] = array_merge(
          $leading_processes,
          $extra_processes,
          $trailing_processes
        );
      }
    }

    return $migrations;
  }

}

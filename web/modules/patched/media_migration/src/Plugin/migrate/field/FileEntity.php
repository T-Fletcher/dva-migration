<?php

namespace Drupal\media_migration\Plugin\migrate\field;

use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Field Plugin for file_entity to media migrations.
 *
 * @MigrateField(
 *   id = "file_entity",
 *   core = {7},
 *   type_map = {
 *     "file_entity" = "entity_reference",
 *   },
 *   source_module = "file",
 *   destination_module = "media",
 * )
 */
class FileEntity extends MediaMigrationFieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function alterFieldMigration(MigrationInterface $migration) {
    $settings = [
      'file_entity' => [
        'plugin' => 'file_entity_field_settings',
      ],
    ];
    $migration->mergeProcessOfProperty('settings', $settings);

    parent::alterFieldMigration($migration);
  }

  /**
   * {@inheritdoc}
   */
  public function alterFieldInstanceMigration(MigrationInterface $migration) {
    $settings = [
      'file_entity' => [
        'plugin' => 'file_entity_field_instance_settings',
      ],
    ];
    $migration->mergeProcessOfProperty('settings', $settings);

    // @todo In Drupal 7, when no media types are explicitly enabled on this
    // field, that means that every media type is allowed. For handling these
    // cases we have to make this migration depend on the media type migrations.
    // @see \Drupal\media_migration\Plugin\migrate\process\FileEntityFieldInstanceSettings::transform()
    parent::alterFieldInstanceMigration($migration);
  }

  /**
   * {@inheritdoc}
   */
  public function defineValueProcessPipeline(MigrationInterface $migration, $field_name, $data) {
    // The "media_migration_delta_sort" plugin sorts field values for PostgreSQL
    // sources.
    // @see \Drupal\media_migration\Plugin\migrate\process\MediaMigrationDeltaSort
    // @todo remove when https://drupal.org/i/3164520 is fixed.
    $process = [
      [
        'plugin' => 'media_migration_delta_sort',
        'source' => $field_name,
      ],
    ];

    $process[] = [
      'plugin' => 'sub_process',
      'process' => [
        'target_id' => 'fid',
      ],
    ];

    $migration->setProcessOfProperty($field_name, $process);
  }

  /**
   * {@inheritdoc}
   */
  public function alterFieldFormatterMigration(MigrationInterface $migration) {
    $settings = [
      'file_entity' => [
        'plugin' => 'file_entity_field_formatter_settings',
      ],
    ];
    $migration->mergeProcessOfProperty('options/settings', $settings);

    parent::alterFieldFormatterMigration($migration);
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldWidgetMap() {
    $mapping = [];
    if (
      $this->moduleHandler->moduleExists('media_library') &&
      $this->fieldWidgetManager->hasDefinition('media_library_widget')
    ) {
      $mapping = [
        'file_generic' => 'media_library_widget',
        'media_generic' => 'media_library_widget',
      ];
    }
    return $mapping + parent::getFieldWidgetMap();
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldFormatterMap() {
    return [
      'file_image_picture' => 'media_responsive_thumbnail',
      'file_image_image' => 'media_thumbnail',
      'file_rendered' => 'entity_reference_entity_view',
      'file_download_link' => 'entity_reference_label',
      'file_audio' => 'entity_reference_entity_view',
      'file_video' => 'entity_reference_entity_view',
      'file_default' => 'entity_reference_entity_view',
      'file_table' => 'entity_reference_entity_view',
      'file_url_plain' => 'entity_reference_label',
    ] + parent::getFieldFormatterMap();
  }

}

<?php

namespace Drupal\Tests\media_migration\Functional;

use Drupal\Core\Database\Driver\pgsql\Connection as PostgreSqlConnection;
use Drupal\media_migration\MediaMigration;
use Drupal\migrate_plus\Entity\MigrationInterface as MigrationEntityInterface;

/**
 * Tests Migrate Upgrade compatibility and verifies usage steps of README.
 *
 * @group media_migration
 */
class DrushWithMigrateUpgradeFromMediaTest extends DrushWithCoreMigrationsFromMediaTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'migrate_upgrade',
  ];

  /**
   * Tests migrations with Migrate Upgrade, Drush and Migrate Tools.
   */
  public function testMigrationWithDrush() {
    // Execute the migrate upgrade drush command from the README.
    // @code
    // drush migrate:upgrade\
    //   --configure-only\
    //   --legacy-db-key=[db-key-of-source-site]\
    //   --legacy-root=[path-to-source-site]
    // @endcode
    $this->drush('migrate:upgrade', ['--configure-only'], [
      'legacy-db-key' => $this->sourceDatabase->getKey(),
      'legacy-root' => drupal_get_path('module', 'media_migration') . '/tests/fixtures',
    ]);

    $migrations = $this->container->get('entity_type.manager')
      ->getStorage('migration')
      ->loadMultiple();

    // "D7_field" migration should depend on file entity type image because the
    // "image" media type has an additional number field.
    $this->assertD7FieldMigration($migrations['upgrade_d7_field']);

    // Check the IDs of migrations belonging to media migration.
    $media_migrations = array_filter($migrations, function (MigrationEntityInterface $migration_config) {
      $entity_array = $migration_config->toArray();
      return in_array(MediaMigration::MIGRATION_TAG_MAIN, $entity_array['migration_tags']);
    });
    $this->assertSame([
      'upgrade_d7_file_entity_audio_public',
      'upgrade_d7_file_entity_document_public',
      'upgrade_d7_file_entity_formatter_audio',
      'upgrade_d7_file_entity_formatter_document',
      'upgrade_d7_file_entity_formatter_image',
      'upgrade_d7_file_entity_formatter_remote_video',
      'upgrade_d7_file_entity_formatter_video',
      'upgrade_d7_file_entity_image_public',
      'upgrade_d7_file_entity_source_field_audio',
      'upgrade_d7_file_entity_source_field_config_audio',
      'upgrade_d7_file_entity_source_field_config_document',
      'upgrade_d7_file_entity_source_field_config_image',
      'upgrade_d7_file_entity_source_field_config_remote_video',
      'upgrade_d7_file_entity_source_field_config_video',
      'upgrade_d7_file_entity_source_field_document',
      'upgrade_d7_file_entity_source_field_image',
      'upgrade_d7_file_entity_source_field_remote_video',
      'upgrade_d7_file_entity_source_field_video',
      'upgrade_d7_file_entity_type_audio',
      'upgrade_d7_file_entity_type_document',
      'upgrade_d7_file_entity_type_image',
      'upgrade_d7_file_entity_type_remote_video',
      'upgrade_d7_file_entity_type_video',
      'upgrade_d7_file_entity_video_public',
      'upgrade_d7_file_entity_video_vimeo',
      'upgrade_d7_file_entity_video_youtube',
      'upgrade_d7_file_entity_widget_audio',
      'upgrade_d7_file_entity_widget_document',
      'upgrade_d7_file_entity_widget_image',
      'upgrade_d7_file_entity_widget_remote_video',
      'upgrade_d7_file_entity_widget_video',
      'upgrade_d7_media_view_modes',
    ], array_keys($media_migrations));

    $this->assertAudioMediaMigrations($media_migrations);
    $this->assertDocumentMediaMigrations($media_migrations);
    $this->assertImageMediaMigrations($media_migrations);
    $this->assertRemoteVideoMediaMigrations($media_migrations);
    $this->assertVideoMediaMigrations($media_migrations);

    // Execute the migrate import "config" drush command from the README.
    // @code
    // drush migrate:import\
    //   --execute-dependencies\
    //   --group=migrate_drupal_7\
    //   --tag=Configuration
    // @endcode
    $this->drush('migrate:import', ['--execute-dependencies'], [
      'group' => 'migrate_drupal_7',
      'tag' => 'Configuration',
    ]);

    // Execute the migrate import "content" drush command from the README.
    // @code
    // drush migrate:import\
    //   --execute-dependencies\
    //   --group=migrate_drupal_7\
    //   --tag=Content
    // @endcode
    $this->drush('migrate:import', ['--execute-dependencies'], [
      'group' => 'migrate_drupal_7',
      'tag' => 'Content',
    ]);

    $this->assertMedia1FieldValues();
    $this->assertMedia2FieldValues();
    $this->assertMedia3FieldValues();
    $this->assertMedia4FieldValues();
    $this->assertMedia5FieldValues();
    $this->assertMedia6FieldValues();
    $this->assertMedia7FieldValues();
    $this->assertMedia8FieldValues();
    $this->assertMedia9FieldValues();
    $this->assertMedia10FieldValues();
    $this->assertMedia11FieldValues();
    $this->assertMedia12FieldValues();
  }

  /**
   * Tests the Drupal 7 field storage migration.
   *
   * @param \Drupal\migrate_plus\Entity\MigrationInterface $d7_field_migration
   *   The Drupal 7 field storage migration entity.
   */
  public function assertD7FieldMigration(MigrationEntityInterface $d7_field_migration) {
    $this->assertEquals([
      'id' => 'upgrade_d7_field',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
      ],
      'source' => [
        'plugin' => 'd7_field',
        'constants' => [
          'status' => TRUE,
          'langcode' => 'und',
        ],
        'mapMigrationProcessValueToMedia' => TRUE,
      ],
      'process' => [
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'entity_type',
          ],
          [
            'plugin' => 'static_map',
            'map' => [
              'file' => 'media',
            ],
            'bypass' => TRUE,
          ],
        ],
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'constants/status',
          ],
        ],
        'langcode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/langcode',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'field_name',
          ],
        ],
        'type' => [
          [
            'plugin' => 'process_field',
            'source' => 'type',
            'method' => 'getFieldType',
            'map' => [
              'd7_text' => [
                'd7_text' => 'd7_text',
              ],
              'media_image' => [
                'media_image' => 'media_image',
              ],
              'file_entity' => [
                'file_entity' => 'file_entity',
              ],
              'number_default' => [
                'number_default' => 'number_default',
              ],
            ],
          ],
        ],
        'cardinality' => [
          [
            'plugin' => 'get',
            'source' => 'cardinality',
          ],
        ],
        'settings' => [
          0 => [
            'plugin' => 'd7_field_settings',
          ],
          'media_image' => [
            'plugin' => 'media_image_field_settings',
          ],
          'file_entity' => [
            'plugin' => 'file_entity_field_settings',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:field_storage_config',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_image',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($d7_field_migration));
  }

  /**
   * Tests audio media migrations.
   *
   * @param \Drupal\migrate_plus\Entity\MigrationInterface[] $media_migrations
   *   Array of migration entities tagged with MediaMigration::MIGRATION_TAG.
   */
  public function assertAudioMediaMigrations(array $media_migrations) {
    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_type_audio',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_type',
        'constants' => [
          'status' => TRUE,
        ],
        'schemes' => 'public',
        'types' => 'audio',
        'destination_media_type_id' => 'audio',
      ],
      'process' => [
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'constants/status',
          ],
        ],
        'id' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'label' => [
          [
            'plugin' => 'get',
            'source' => 'bundle_label',
          ],
        ],
        'source' => [
          [
            'plugin' => 'get',
            'source' => 'source_plugin_id',
          ],
        ],
        'source_configuration/source_field' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media_type',
      ],
      'migration_dependencies' => [
        'required' => [],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_type_audio']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_source_field_audio',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_source_field_storage',
        'constants' => [
          'entity_type_id' => 'media',
          'status' => TRUE,
          'langcode' => 'und',
          'cardinality' => 1,
        ],
        'schemes' => 'public',
        'types' => 'audio',
        'destination_media_type_id' => 'audio',
      ],
      'process' => [
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'constants/status',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'langcode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/langcode',
          ],
        ],
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'type' => [
          [
            'plugin' => 'get',
            'source' => 'field_type',
          ],
        ],
        'cardinality' => [
          [
            'plugin' => 'get',
            'source' => 'constants/cardinality',
          ],
        ],
        'settings' => [
          [
            'plugin' => 'get',
            'source' => 'settings',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:field_storage_config',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_audio',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_source_field_audio']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_source_field_config_audio',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_source_field_instance',
        'constants' => [
          'entity_type_id' => 'media',
          'required' => TRUE,
        ],
        'schemes' => 'public',
        'types' => 'audio',
        'destination_media_type_id' => 'audio',
      ],
      'process' => [
        'label' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_label',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'required' => [
          [
            'plugin' => 'get',
            'source' => 'constants/required',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'settings' => [
          [
            'plugin' => 'get',
            'source' => 'settings',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:field_config',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_audio',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_source_field_config_audio']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_formatter_audio',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_field_formatter',
        'constants' => [
          'entity_type_id' => 'media',
          'view_mode' => 'default',
        ],
        'schemes' => 'public',
        'types' => 'audio',
        'destination_media_type_id' => 'audio',
      ],
      'process' => [
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'view_mode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/view_mode',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'field_name',
          ],
        ],
        'hidden' => [
          [
            'plugin' => 'get',
            'source' => 'hidden',
          ],
        ],
        'options' => [
          [
            'plugin' => 'get',
            'source' => 'options',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'component_entity_display',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_config_audio',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_formatter_audio']));

    // Widget settings migration.
    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_widget_audio',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_field_widget',
        'constants' => [
          'entity_type_id' => 'media',
          'form_mode' => 'default',
        ],
        'schemes' => 'public',
        'types' => 'audio',
        'destination_media_type_id' => 'audio',
      ],
      'process' => [
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'form_mode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/form_mode',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'options' => [
          [
            'plugin' => 'get',
            'source' => 'options',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'component_entity_form_display',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_config_audio',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_widget_audio']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_audio_public',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Content',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONTENT,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_item',
        'type' => 'audio',
        'scheme' => 'public',
        'destination_media_type_id' => 'audio',
      ],
      'process' => [
        'uuid' => [
          [
            'plugin' => 'media_migrate_uuid',
            'source' => 'fid',
          ],
          [
            'plugin' => 'skip_on_empty',
            'method' => 'process',
          ],
        ],
        'mid' => [
          [
            'plugin' => 'get',
            'source' => 'fid',
          ],
        ],
        'uid' => [
          [
            'plugin' => 'get',
            'source' => 'uid',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'name' => [
          [
            'plugin' => 'get',
            'source' => 'filename',
          ],
        ],
        'created' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'changed' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'status',
          ],
        ],
        'field_media_audio_file/target_id' => [
          [
            'plugin' => 'get',
            'source' => 'fid',
          ],
        ],
        'field_media_audio_file/display' => [
          [
            'plugin' => 'get',
            'source' => 'display',
          ],
        ],
        'field_media_audio_file/description' => [
          [
            'plugin' => 'get',
            'source' => 'description',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_audio',
          'upgrade_d7_file_entity_source_field_config_audio',
          'upgrade_d7_user',
          'upgrade_d7_file',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_audio_public']));
  }

  /**
   * Tests document media migrations.
   *
   * @param \Drupal\migrate_plus\Entity\MigrationInterface[] $media_migrations
   *   Array of migration entities tagged with MediaMigration::MIGRATION_TAG.
   */
  public function assertDocumentMediaMigrations(array $media_migrations) {
    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_type_document',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_type',
        'constants' => [
          'status' => TRUE,
        ],
        'schemes' => 'public',
        'types' => 'document',
        'destination_media_type_id' => 'document',
      ],
      'process' => [
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'constants/status',
          ],
        ],
        'id' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'label' => [
          [
            'plugin' => 'get',
            'source' => 'bundle_label',
          ],
        ],
        'source' => [
          [
            'plugin' => 'get',
            'source' => 'source_plugin_id',
          ],
        ],
        'source_configuration/source_field' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media_type',
      ],
      'migration_dependencies' => [
        'required' => [],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_type_document']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_source_field_document',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_source_field_storage',
        'constants' => [
          'entity_type_id' => 'media',
          'status' => TRUE,
          'langcode' => 'und',
          'cardinality' => 1,
        ],
        'schemes' => 'public',
        'types' => 'document',
        'destination_media_type_id' => 'document',
      ],
      'process' => [
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'constants/status',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'langcode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/langcode',
          ],
        ],
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'type' => [
          [
            'plugin' => 'get',
            'source' => 'field_type',
          ],
        ],
        'cardinality' => [
          [
            'plugin' => 'get',
            'source' => 'constants/cardinality',
          ],
        ],
        'settings' => [
          [
            'plugin' => 'get',
            'source' => 'settings',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:field_storage_config',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_document',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_source_field_document']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_source_field_config_document',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_source_field_instance',
        'constants' => [
          'entity_type_id' => 'media',
          'required' => TRUE,
        ],
        'schemes' => 'public',
        'types' => 'document',
        'destination_media_type_id' => 'document',
      ],
      'process' => [
        'label' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_label',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'required' => [
          [
            'plugin' => 'get',
            'source' => 'constants/required',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'settings' => [
          [
            'plugin' => 'get',
            'source' => 'settings',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:field_config',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_document',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_source_field_config_document']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_formatter_document',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_field_formatter',
        'constants' => [
          'entity_type_id' => 'media',
          'view_mode' => 'default',
        ],
        'schemes' => 'public',
        'types' => 'document',
        'destination_media_type_id' => 'document',
      ],
      'process' => [
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'view_mode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/view_mode',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'field_name',
          ],
        ],
        'hidden' => [
          [
            'plugin' => 'get',
            'source' => 'hidden',
          ],
        ],
        'options' => [
          [
            'plugin' => 'get',
            'source' => 'options',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'component_entity_display',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_config_document',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_formatter_document']));

    // Widget settings migration.
    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_widget_document',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_field_widget',
        'constants' => [
          'entity_type_id' => 'media',
          'form_mode' => 'default',
        ],
        'schemes' => 'public',
        'types' => 'document',
        'destination_media_type_id' => 'document',
      ],
      'process' => [
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'form_mode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/form_mode',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'options' => [
          [
            'plugin' => 'get',
            'source' => 'options',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'component_entity_form_display',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_config_document',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_widget_document']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_document_public',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Content',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONTENT,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_item',
        'type' => 'document',
        'scheme' => 'public',
        'destination_media_type_id' => 'document',
      ],
      'process' => [
        'uuid' => [
          [
            'plugin' => 'media_migrate_uuid',
            'source' => 'fid',
          ],
          [
            'plugin' => 'skip_on_empty',
            'method' => 'process',
          ],
        ],
        'mid' => [
          [
            'plugin' => 'get',
            'source' => 'fid',
          ],
        ],
        'uid' => [
          [
            'plugin' => 'get',
            'source' => 'uid',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'name' => [
          [
            'plugin' => 'get',
            'source' => 'filename',
          ],
        ],
        'created' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'changed' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'status',
          ],
        ],
        'field_media_document/target_id' => [
          [
            'plugin' => 'get',
            'source' => 'fid',
          ],
        ],
        'field_media_document/display' => [
          [
            'plugin' => 'get',
            'source' => 'display',
          ],
        ],
        'field_media_document/description' => [
          [
            'plugin' => 'get',
            'source' => 'description',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_document',
          'upgrade_d7_file_entity_source_field_config_document',
          'upgrade_d7_user',
          'upgrade_d7_file',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_document_public']));
  }

  /**
   * Tests image media migrations.
   *
   * @param \Drupal\migrate_plus\Entity\MigrationInterface[] $media_migrations
   *   Array of migration entities tagged with MediaMigration::MIGRATION_TAG.
   */
  public function assertImageMediaMigrations(array $media_migrations) {
    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_type_image',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_type',
        'constants' => [
          'status' => TRUE,
        ],
        'schemes' => 'public',
        'types' => 'image',
        'destination_media_type_id' => 'image',
      ],
      'process' => [
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'constants/status',
          ],
        ],
        'id' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'label' => [
          [
            'plugin' => 'get',
            'source' => 'bundle_label',
          ],
        ],
        'source' => [
          [
            'plugin' => 'get',
            'source' => 'source_plugin_id',
          ],
        ],
        'source_configuration/source_field' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media_type',
      ],
      'migration_dependencies' => [
        'required' => [],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_type_image']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_source_field_image',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_source_field_storage',
        'constants' => [
          'entity_type_id' => 'media',
          'status' => TRUE,
          'langcode' => 'und',
          'cardinality' => 1,
        ],
        'schemes' => 'public',
        'types' => 'image',
        'destination_media_type_id' => 'image',
      ],
      'process' => [
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'constants/status',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'langcode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/langcode',
          ],
        ],
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'type' => [
          [
            'plugin' => 'get',
            'source' => 'field_type',
          ],
        ],
        'cardinality' => [
          [
            'plugin' => 'get',
            'source' => 'constants/cardinality',
          ],
        ],
        'settings' => [
          [
            'plugin' => 'get',
            'source' => 'settings',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:field_storage_config',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_image',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_source_field_image']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_source_field_config_image',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_source_field_instance',
        'constants' => [
          'entity_type_id' => 'media',
          'required' => TRUE,
        ],
        'schemes' => 'public',
        'types' => 'image',
        'destination_media_type_id' => 'image',
      ],
      'process' => [
        'label' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_label',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'required' => [
          [
            'plugin' => 'get',
            'source' => 'constants/required',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'settings' => [
          [
            'plugin' => 'get',
            'source' => 'settings',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:field_config',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_image',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_source_field_config_image']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_formatter_image',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_field_formatter',
        'constants' => [
          'entity_type_id' => 'media',
          'view_mode' => 'default',
        ],
        'schemes' => 'public',
        'types' => 'image',
        'destination_media_type_id' => 'image',
      ],
      'process' => [
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'view_mode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/view_mode',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'field_name',
          ],
        ],
        'hidden' => [
          [
            'plugin' => 'get',
            'source' => 'hidden',
          ],
        ],
        'options' => [
          [
            'plugin' => 'get',
            'source' => 'options',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'component_entity_display',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_config_image',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_formatter_image']));

    // Widget settings migration.
    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_widget_image',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_field_widget',
        'constants' => [
          'entity_type_id' => 'media',
          'form_mode' => 'default',
        ],
        'schemes' => 'public',
        'types' => 'image',
        'destination_media_type_id' => 'image',
      ],
      'process' => [
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'form_mode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/form_mode',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'options' => [
          [
            'plugin' => 'get',
            'source' => 'options',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'component_entity_form_display',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_config_image',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_widget_image']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_image_public',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Content',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONTENT,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_item',
        'type' => 'image',
        'scheme' => 'public',
        'destination_media_type_id' => 'image',
      ],
      'process' => [
        'uuid' => [
          [
            'plugin' => 'media_migrate_uuid',
            'source' => 'fid',
          ],
          [
            'plugin' => 'skip_on_empty',
            'method' => 'process',
          ],
        ],
        'mid' => [
          [
            'plugin' => 'get',
            'source' => 'fid',
          ],
        ],
        'uid' => [
          [
            'plugin' => 'get',
            'source' => 'uid',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'name' => [
          [
            'plugin' => 'get',
            'source' => 'filename',
          ],
        ],
        'created' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'changed' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'status',
          ],
        ],
        'field_media_image/target_id' => [
          [
            'plugin' => 'get',
            'source' => 'fid',
          ],
        ],
        'field_media_image/display' => [
          [
            'plugin' => 'get',
            'source' => 'display',
          ],
        ],
        'field_media_image/description' => [
          [
            'plugin' => 'get',
            'source' => 'description',
          ],
        ],
        'field_media_integer' => [
          [
            'plugin' => 'get',
            'source' => 'field_media_integer',
          ],
        ],
        'field_media_image/width' => [
          [
            'plugin' => 'get',
            'source' => 'width',
          ],
        ],
        'field_media_image/height' => [
          [
            'plugin' => 'get',
            'source' => 'height',
          ],
        ],
        'thumbnail/target_id' => [
          [
            'plugin' => 'get',
            'source' => 'fid',
          ],
        ],
        'thumbnail/width' => [
          [
            'plugin' => 'get',
            'source' => 'width',
          ],
        ],
        'thumbnail/height' => [
          [
            'plugin' => 'get',
            'source' => 'height',
          ],
        ],
        'alt_from_media' => [
          [
            'plugin' => 'skip_on_empty',
            'source' => 'field_file_image_alt_text',
            'method' => 'process',
          ],
          [
            'plugin' => 'extract',
            'index' => ['0', 'value'],
            'default' => '',
          ],
          [
            'plugin' => 'default_value',
            'default_value' => NULL,
          ],
        ],
        'thumbnail/alt' => [
          [
            'plugin' => 'null_coalesce',
            'source' => [
              'alt',
              '@alt_from_media',
            ],
            'default_value' => NULL,
          ],
        ],
        'field_media_image/alt' => [
          [
            'plugin' => 'null_coalesce',
            'source' => [
              'alt',
              '@alt_from_media',
            ],
            'default_value' => NULL,
          ],
        ],
        'title_from_media' => [
          [
            'plugin' => 'skip_on_empty',
            'source' => 'field_file_image_title_text',
            'method' => 'process',
          ],
          [
            'plugin' => 'extract',
            'index' => ['0', 'value'],
            'default' => '',
          ],
          [
            'plugin' => 'default_value',
            'default_value' => NULL,
          ],
        ],
        'thumbnail/title' => [
          [
            'plugin' => 'null_coalesce',
            'source' => [
              'title',
              '@title_from_media',
            ],
            'default_value' => NULL,
          ],
        ],
        'field_media_image/title' => [
          [
            'plugin' => 'null_coalesce',
            'source' => [
              'title',
              '@title_from_media',
            ],
            'default_value' => NULL,
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_image',
          'upgrade_d7_file_entity_source_field_config_image',
          'upgrade_d7_user',
          'upgrade_d7_field_instance',
          'upgrade_d7_file',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_image_public']));
  }

  /**
   * Tests remote video (Vimeo and YouTube) media migrations.
   *
   * @param \Drupal\migrate_plus\Entity\MigrationInterface[] $media_migrations
   *   Array of migration entities tagged with MediaMigration::MIGRATION_TAG.
   */
  public function assertRemoteVideoMediaMigrations(array $media_migrations) {
    // PostgreSQL returns differently sorted results.
    $remote_video_schemes = $this->sourceDatabase instanceof PostgreSqlConnection
      ? 'vimeo::youtube'
      : 'youtube::vimeo';

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_type_remote_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_type',
        'constants' => [
          'status' => TRUE,
        ],
        'schemes' => $remote_video_schemes,
        'types' => 'video',
        'destination_media_type_id' => 'remote_video',
      ],
      'process' => [
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'constants/status',
          ],
        ],
        'id' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'label' => [
          [
            'plugin' => 'get',
            'source' => 'bundle_label',
          ],
        ],
        'source' => [
          [
            'plugin' => 'get',
            'source' => 'source_plugin_id',
          ],
        ],
        'source_configuration/source_field' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media_type',
      ],
      'migration_dependencies' => [
        'required' => [],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_type_remote_video']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_source_field_remote_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_source_field_storage',
        'constants' => [
          'entity_type_id' => 'media',
          'status' => TRUE,
          'langcode' => 'und',
          'cardinality' => 1,
        ],
        'schemes' => $remote_video_schemes,
        'types' => 'video',
        'destination_media_type_id' => 'remote_video',
      ],
      'process' => [
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'constants/status',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'langcode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/langcode',
          ],
        ],
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'type' => [
          [
            'plugin' => 'get',
            'source' => 'field_type',
          ],
        ],
        'cardinality' => [
          [
            'plugin' => 'get',
            'source' => 'constants/cardinality',
          ],
        ],
        'settings' => [
          [
            'plugin' => 'get',
            'source' => 'settings',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:field_storage_config',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_remote_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_source_field_remote_video']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_source_field_config_remote_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_source_field_instance',
        'constants' => [
          'entity_type_id' => 'media',
          'required' => TRUE,
        ],
        'schemes' => $remote_video_schemes,
        'types' => 'video',
        'destination_media_type_id' => 'remote_video',
      ],
      'process' => [
        'label' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_label',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'required' => [
          [
            'plugin' => 'get',
            'source' => 'constants/required',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'settings' => [
          [
            'plugin' => 'get',
            'source' => 'settings',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:field_config',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_remote_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_source_field_config_remote_video']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_formatter_remote_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_field_formatter',
        'constants' => [
          'entity_type_id' => 'media',
          'view_mode' => 'default',
        ],
        'schemes' => $remote_video_schemes,
        'types' => 'video',
        'destination_media_type_id' => 'remote_video',
      ],
      'process' => [
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'view_mode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/view_mode',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'field_name',
          ],
        ],
        'hidden' => [
          [
            'plugin' => 'get',
            'source' => 'hidden',
          ],
        ],
        'options' => [
          [
            'plugin' => 'get',
            'source' => 'options',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'component_entity_display',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_config_remote_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_formatter_remote_video']));

    // Widget settings migration.
    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_widget_remote_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_field_widget',
        'constants' => [
          'entity_type_id' => 'media',
          'form_mode' => 'default',
        ],
        'schemes' => $remote_video_schemes,
        'types' => 'video',
        'destination_media_type_id' => 'remote_video',
      ],
      'process' => [
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'form_mode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/form_mode',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'options' => [
          [
            'plugin' => 'get',
            'source' => 'options',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'component_entity_form_display',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_config_remote_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_widget_remote_video']));

    // Vimeo migration.
    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_video_vimeo',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Content',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONTENT,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_item',
        'type' => 'video',
        'scheme' => 'vimeo',
        'destination_media_type_id' => 'remote_video',
      ],
      'process' => [
        'uuid' => [
          [
            'plugin' => 'media_migrate_uuid',
            'source' => 'fid',
          ],
          [
            'plugin' => 'skip_on_empty',
            'method' => 'process',
          ],
        ],
        'mid' => [
          [
            'plugin' => 'get',
            'source' => 'fid',
          ],
        ],
        'uid' => [
          [
            'plugin' => 'get',
            'source' => 'uid',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'name' => [
          [
            'plugin' => 'get',
            'source' => 'filename',
          ],
        ],
        'created' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'changed' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'status',
          ],
        ],
        'field_media_oembed_video/value' => [
          [
            'plugin' => 'media_internet_field_value',
            'source' => 'uri',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_remote_video',
          'upgrade_d7_file_entity_source_field_config_remote_video',
          'upgrade_d7_user',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_video_vimeo']));

    // Youtube migration.
    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_video_youtube',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Content',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONTENT,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_item',
        'type' => 'video',
        'scheme' => 'youtube',
        'destination_media_type_id' => 'remote_video',
      ],
      'process' => [
        'uuid' => [
          [
            'plugin' => 'media_migrate_uuid',
            'source' => 'fid',
          ],
          [
            'plugin' => 'skip_on_empty',
            'method' => 'process',
          ],
        ],
        'mid' => [
          [
            'plugin' => 'get',
            'source' => 'fid',
          ],
        ],
        'uid' => [
          [
            'plugin' => 'get',
            'source' => 'uid',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'name' => [
          [
            'plugin' => 'get',
            'source' => 'filename',
          ],
        ],
        'created' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'changed' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'status',
          ],
        ],
        'field_media_oembed_video/value' => [
          [
            'plugin' => 'media_internet_field_value',
            'source' => 'uri',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_remote_video',
          'upgrade_d7_file_entity_source_field_config_remote_video',
          'upgrade_d7_user',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_video_youtube']));
  }

  /**
   * Tests video media migrations (of locally stored videos).
   *
   * @param \Drupal\migrate_plus\Entity\MigrationInterface[] $media_migrations
   *   Array of migration entities tagged with MediaMigration::MIGRATION_TAG.
   */
  public function assertVideoMediaMigrations(array $media_migrations) {
    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_type_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_type',
        'constants' => [
          'status' => TRUE,
        ],
        'schemes' => 'public',
        'types' => 'video',
        'destination_media_type_id' => 'video',
      ],
      'process' => [
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'constants/status',
          ],
        ],
        'id' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'label' => [
          [
            'plugin' => 'get',
            'source' => 'bundle_label',
          ],
        ],
        'source' => [
          [
            'plugin' => 'get',
            'source' => 'source_plugin_id',
          ],
        ],
        'source_configuration/source_field' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media_type',
      ],
      'migration_dependencies' => [
        'required' => [],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_type_video']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_source_field_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_source_field_storage',
        'constants' => [
          'entity_type_id' => 'media',
          'status' => TRUE,
          'langcode' => 'und',
          'cardinality' => 1,
        ],
        'schemes' => 'public',
        'types' => 'video',
        'destination_media_type_id' => 'video',
      ],
      'process' => [
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'constants/status',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'langcode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/langcode',
          ],
        ],
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'type' => [
          [
            'plugin' => 'get',
            'source' => 'field_type',
          ],
        ],
        'cardinality' => [
          [
            'plugin' => 'get',
            'source' => 'constants/cardinality',
          ],
        ],
        'settings' => [
          [
            'plugin' => 'get',
            'source' => 'settings',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:field_storage_config',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_source_field_video']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_source_field_config_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_source_field_instance',
        'constants' => [
          'entity_type_id' => 'media',
          'required' => TRUE,
        ],
        'schemes' => 'public',
        'types' => 'video',
        'destination_media_type_id' => 'video',
      ],
      'process' => [
        'label' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_label',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'required' => [
          [
            'plugin' => 'get',
            'source' => 'constants/required',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'settings' => [
          [
            'plugin' => 'get',
            'source' => 'settings',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:field_config',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_source_field_config_video']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_formatter_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_field_formatter',
        'constants' => [
          'entity_type_id' => 'media',
          'view_mode' => 'default',
        ],
        'schemes' => 'public',
        'types' => 'video',
        'destination_media_type_id' => 'video',
      ],
      'process' => [
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'view_mode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/view_mode',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'field_name',
          ],
        ],
        'hidden' => [
          [
            'plugin' => 'get',
            'source' => 'hidden',
          ],
        ],
        'options' => [
          [
            'plugin' => 'get',
            'source' => 'options',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'component_entity_display',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_config_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_formatter_video']));

    // Widget settings migration.
    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_widget_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_field_widget',
        'constants' => [
          'entity_type_id' => 'media',
          'form_mode' => 'default',
        ],
        'schemes' => 'public',
        'types' => 'video',
        'destination_media_type_id' => 'video',
      ],
      'process' => [
        'entity_type' => [
          [
            'plugin' => 'get',
            'source' => 'constants/entity_type_id',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'form_mode' => [
          [
            'plugin' => 'get',
            'source' => 'constants/form_mode',
          ],
        ],
        'field_name' => [
          [
            'plugin' => 'get',
            'source' => 'source_field_name',
          ],
        ],
        'options' => [
          [
            'plugin' => 'get',
            'source' => 'options',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'component_entity_form_display',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_source_field_config_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_widget_video']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_entity_video_public',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Content',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONTENT,
      ],
      'source' => [
        'plugin' => 'd7_file_entity_item',
        'type' => 'video',
        'scheme' => 'public',
        'destination_media_type_id' => 'video',
      ],
      'process' => [
        'uuid' => [
          [
            'plugin' => 'media_migrate_uuid',
            'source' => 'fid',
          ],
          [
            'plugin' => 'skip_on_empty',
            'method' => 'process',
          ],
        ],
        'mid' => [
          [
            'plugin' => 'get',
            'source' => 'fid',
          ],
        ],
        'uid' => [
          [
            'plugin' => 'get',
            'source' => 'uid',
          ],
        ],
        'bundle' => [
          [
            'plugin' => 'get',
            'source' => 'bundle',
          ],
        ],
        'name' => [
          [
            'plugin' => 'get',
            'source' => 'filename',
          ],
        ],
        'created' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'changed' => [
          [
            'plugin' => 'get',
            'source' => 'timestamp',
          ],
        ],
        'status' => [
          [
            'plugin' => 'get',
            'source' => 'status',
          ],
        ],
        'field_media_video_file/target_id' => [
          [
            'plugin' => 'get',
            'source' => 'fid',
          ],
        ],
        'field_media_video_file/display' => [
          [
            'plugin' => 'get',
            'source' => 'display',
          ],
        ],
        'field_media_video_file/description' => [
          [
            'plugin' => 'get',
            'source' => 'description',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_entity_type_video',
          'upgrade_d7_file_entity_source_field_config_video',
          'upgrade_d7_user',
          'upgrade_d7_file',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_entity_video_public']));
  }

}

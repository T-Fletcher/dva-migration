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
class DrushWithMigrateUpgradeFromFileTest extends DrushWithCoreMigrationsFromFileTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'migrate_upgrade',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getFixtureFilePath() {
    return drupal_get_path('module', 'media_migration') . '/tests/fixtures/drupal7_nomedia.php';
  }

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
      'upgrade_d7_file_plain_formatter_audio',
      'upgrade_d7_file_plain_formatter_document',
      'upgrade_d7_file_plain_formatter_image',
      'upgrade_d7_file_plain_formatter_video',
      'upgrade_d7_file_plain_public_application',
      'upgrade_d7_file_plain_public_audio',
      'upgrade_d7_file_plain_public_image',
      'upgrade_d7_file_plain_public_text',
      'upgrade_d7_file_plain_public_video',
      'upgrade_d7_file_plain_source_field_audio',
      'upgrade_d7_file_plain_source_field_config_audio',
      'upgrade_d7_file_plain_source_field_config_document',
      'upgrade_d7_file_plain_source_field_config_image',
      'upgrade_d7_file_plain_source_field_config_video',
      'upgrade_d7_file_plain_source_field_document',
      'upgrade_d7_file_plain_source_field_image',
      'upgrade_d7_file_plain_source_field_video',
      'upgrade_d7_file_plain_type_audio',
      'upgrade_d7_file_plain_type_document',
      'upgrade_d7_file_plain_type_image',
      'upgrade_d7_file_plain_type_video',
      'upgrade_d7_file_plain_widget_audio',
      'upgrade_d7_file_plain_widget_document',
      'upgrade_d7_file_plain_widget_image',
      'upgrade_d7_file_plain_widget_video',
    ], array_keys($media_migrations));

    $this->assertAudioMediaMigrations($media_migrations);
    $this->assertDocumentMediaMigrations($media_migrations);
    $this->assertImageMediaMigrations($media_migrations);
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

    // Execute the migrate import "config" drush command from the README.
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

    $this->assertNonMediaToMedia1FieldValues();
    $this->assertNonMediaToMedia2FieldValues();
    $this->assertNonMediaToMedia3FieldValues();
    $this->assertNonMediaToMedia6FieldValues();
    $this->assertNonMediaToMedia7FieldValues();
    $this->assertNonMediaToMedia8FieldValues();
    $this->assertNonMediaToMedia9FieldValues();
    $this->assertNonMediaToMedia10FieldValues();
    $this->assertNonMediaToMedia11FieldValues();
    $this->assertNonMediaToMedia12FieldValues();
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
        'required' => [],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($d7_field_migration));
  }

  /**
   * Tests audio file to media migrations.
   *
   * @param \Drupal\migrate_plus\Entity\MigrationInterface[] $media_migrations
   *   Array of migration entities tagged with MediaMigration::MIGRATION_TAG.
   */
  public function assertAudioMediaMigrations(array $media_migrations) {
    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_type_audio',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_type',
        'constants' => [
          'status' => TRUE,
        ],
        'mimes' => 'audio',
        'schemes' => 'public',
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
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_type_audio']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_source_field_audio',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_source_field_storage',
        'constants' => [
          'entity_type_id' => 'media',
          'status' => TRUE,
          'langcode' => 'und',
          'cardinality' => 1,
        ],
        'mimes' => 'audio',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_type_audio',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_source_field_audio']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_source_field_config_audio',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_source_field_instance',
        'constants' => [
          'entity_type_id' => 'media',
          'required' => TRUE,
        ],
        'mimes' => 'audio',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_audio',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_source_field_config_audio']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_formatter_audio',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_field_formatter',
        'constants' => [
          'entity_type_id' => 'media',
          'view_mode' => 'default',
        ],
        'mimes' => 'audio',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_config_audio',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_formatter_audio']));

    // Widget settings migration.
    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_widget_audio',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_field_widget',
        'constants' => [
          'entity_type_id' => 'media',
          'form_mode' => 'default',
        ],
        'mimes' => 'audio',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_config_audio',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_widget_audio']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_public_audio',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Content',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONTENT,
      ],
      'source' => [
        'plugin' => 'd7_file_plain',
        'mime' => 'audio',
        'scheme' => 'public',
        'destination_media_type_id' => 'audio',
      ],
      'process' => [
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
          'upgrade_d7_file_plain_type_audio',
          'upgrade_d7_file_plain_source_field_config_audio',
          'upgrade_d7_user',
          'upgrade_d7_file',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_public_audio']));
  }

  /**
   * Tests "document" file to media migrations.
   *
   * @param \Drupal\migrate_plus\Entity\MigrationInterface[] $media_migrations
   *   Array of migration entities tagged with MediaMigration::MIGRATION_TAG.
   */
  public function assertDocumentMediaMigrations(array $media_migrations) {
    $document_mimes = $this->sourceDatabase instanceof PostgreSqlConnection
      ? 'application::text'
      : 'text::application';

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_type_document',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_type',
        'constants' => [
          'status' => TRUE,
        ],
        'mimes' => $document_mimes,
        'schemes' => 'public',
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
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_type_document']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_source_field_document',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_source_field_storage',
        'constants' => [
          'entity_type_id' => 'media',
          'status' => TRUE,
          'langcode' => 'und',
          'cardinality' => 1,
        ],
        'mimes' => $document_mimes,
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_type_document',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_source_field_document']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_source_field_config_document',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_source_field_instance',
        'constants' => [
          'entity_type_id' => 'media',
          'required' => TRUE,
        ],
        'mimes' => $document_mimes,
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_document',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_source_field_config_document']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_formatter_document',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_field_formatter',
        'constants' => [
          'entity_type_id' => 'media',
          'view_mode' => 'default',
        ],
        'mimes' => $document_mimes,
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_config_document',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_formatter_document']));

    // Widget settings migration.
    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_widget_document',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_field_widget',
        'constants' => [
          'entity_type_id' => 'media',
          'form_mode' => 'default',
        ],
        'mimes' => $document_mimes,
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_config_document',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_widget_document']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_public_application',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Content',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONTENT,
      ],
      'source' => [
        'plugin' => 'd7_file_plain',
        'mime' => 'application',
        'scheme' => 'public',
        'destination_media_type_id' => 'document',
      ],
      'process' => [
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
          'upgrade_d7_file_plain_type_document',
          'upgrade_d7_file_plain_source_field_config_document',
          'upgrade_d7_user',
          'upgrade_d7_file',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_public_application']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_public_text',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Content',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONTENT,
      ],
      'source' => [
        'plugin' => 'd7_file_plain',
        'mime' => 'text',
        'scheme' => 'public',
        'destination_media_type_id' => 'document',
      ],
      'process' => [
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
          'upgrade_d7_file_plain_type_document',
          'upgrade_d7_file_plain_source_field_config_document',
          'upgrade_d7_user',
          'upgrade_d7_file',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_public_text']));
  }

  /**
   * Tests image file to media migrations.
   *
   * @param \Drupal\migrate_plus\Entity\MigrationInterface[] $media_migrations
   *   Array of migration entities tagged with MediaMigration::MIGRATION_TAG.
   */
  public function assertImageMediaMigrations(array $media_migrations) {
    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_type_image',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_type',
        'constants' => [
          'status' => TRUE,
        ],
        'mimes' => 'image',
        'schemes' => 'public',
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
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_type_image']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_source_field_image',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_source_field_storage',
        'constants' => [
          'entity_type_id' => 'media',
          'status' => TRUE,
          'langcode' => 'und',
          'cardinality' => 1,
        ],
        'mimes' => 'image',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_type_image',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_source_field_image']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_source_field_config_image',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_source_field_instance',
        'constants' => [
          'entity_type_id' => 'media',
          'required' => TRUE,
        ],
        'mimes' => 'image',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_image',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_source_field_config_image']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_formatter_image',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_field_formatter',
        'constants' => [
          'entity_type_id' => 'media',
          'view_mode' => 'default',
        ],
        'mimes' => 'image',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_config_image',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_formatter_image']));

    // Widget settings migration.
    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_widget_image',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_field_widget',
        'constants' => [
          'entity_type_id' => 'media',
          'form_mode' => 'default',
        ],
        'mimes' => 'image',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_config_image',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_widget_image']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_public_image',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Content',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONTENT,
      ],
      'source' => [
        'plugin' => 'd7_file_plain',
        'mime' => 'image',
        'scheme' => 'public',
        'destination_media_type_id' => 'image',
      ],
      'process' => [
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
        'thumbnail/alt' => [
          [
            'plugin' => 'get',
            'source' => 'alt',
          ],
        ],
        'field_media_image/alt' => [
          [
            'plugin' => 'get',
            'source' => 'alt',
          ],
        ],
        'thumbnail/title' => [
          [
            'plugin' => 'get',
            'source' => 'title',
          ],
        ],
        'field_media_image/title' => [
          [
            'plugin' => 'get',
            'source' => 'title',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media',
      ],
      'migration_dependencies' => [
        'required' => [
          'upgrade_d7_file_plain_type_image',
          'upgrade_d7_file_plain_source_field_config_image',
          'upgrade_d7_user',
          'upgrade_d7_file',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_public_image']));
  }

  /**
   * Tests video file to media migrations (of locally stored videos).
   *
   * @param \Drupal\migrate_plus\Entity\MigrationInterface[] $media_migrations
   *   Array of migration entities tagged with MediaMigration::MIGRATION_TAG.
   */
  public function assertVideoMediaMigrations(array $media_migrations) {
    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_type_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_type',
        'constants' => [
          'status' => TRUE,
        ],
        'mimes' => 'video',
        'schemes' => 'public',
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
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_type_video']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_source_field_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_source_field_storage',
        'constants' => [
          'entity_type_id' => 'media',
          'status' => TRUE,
          'langcode' => 'und',
          'cardinality' => 1,
        ],
        'mimes' => 'video',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_type_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_source_field_video']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_source_field_config_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_source_field_instance',
        'constants' => [
          'entity_type_id' => 'media',
          'required' => TRUE,
        ],
        'mimes' => 'video',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_source_field_config_video']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_formatter_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_field_formatter',
        'constants' => [
          'entity_type_id' => 'media',
          'view_mode' => 'default',
        ],
        'mimes' => 'video',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_config_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_formatter_video']));

    // Widget settings migration.
    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_widget_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONFIG,
      ],
      'source' => [
        'plugin' => 'd7_file_plain_field_widget',
        'constants' => [
          'entity_type_id' => 'media',
          'form_mode' => 'default',
        ],
        'mimes' => 'video',
        'schemes' => 'public',
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
          'upgrade_d7_file_plain_source_field_config_video',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_widget_video']));

    $this->assertEquals([
      'id' => 'upgrade_d7_file_plain_public_video',
      'migration_group' => 'migrate_drupal_7',
      'migration_tags' => [
        'Drupal 7',
        'Content',
        MediaMigration::MIGRATION_TAG_MAIN,
        MediaMigration::MIGRATION_TAG_CONTENT,
      ],
      'source' => [
        'plugin' => 'd7_file_plain',
        'mime' => 'video',
        'scheme' => 'public',
        'destination_media_type_id' => 'video',
      ],
      'process' => [
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
          'upgrade_d7_file_plain_type_video',
          'upgrade_d7_file_plain_source_field_config_video',
          'upgrade_d7_user',
          'upgrade_d7_file',
        ],
        'optional' => [],
      ],
      'dependencies' => [],
    ], $this->getImportantEntityProperties($media_migrations['upgrade_d7_file_plain_public_video']));
  }

}

<?php

namespace Drupal\Tests\media_migration\Functional;

use Drupal\media_migration\MediaMigration;
use Drupal\Tests\media_migration\Traits\MediaMigrationAssertionsForNonMediaSourceTrait;

/**
 * Tests Migrate Tools and Drush compatibility – verifies usage steps in README.
 *
 * @group media_migration
 */
class DrushWithCoreMigrationsFromFileTest extends DrushTestBase {

  use MediaMigrationAssertionsForNonMediaSourceTrait;

  /**
   * {@inheritdoc}
   */
  protected function getFixtureFilePath() {
    return drupal_get_path('module', 'media_migration') . '/tests/fixtures/drupal7_nomedia.php';
  }

  /**
   * Test migrations provided by core Migrate API with Drush and Migrate Tools.
   */
  public function testMigrationWithDrush() {
    // Copy source files to the destination.
    // This is required for the "d7_file" migration.
    $source_dir = DRUPAL_ROOT . DIRECTORY_SEPARATOR . drupal_get_path('module', 'media_migration') . '/tests/fixtures/sites/default/files';
    $this->sourceDatabase->upsert('variable')
      ->key('name')
      ->fields([
        'name' => 'file_public_path',
        'value' => serialize($source_dir),
      ])
      ->execute();

    // Verify that the expected migrations are generated.
    // @code
    // drush migrate:status\
    //   --names-only\
    //   --group=default
    //   --tag="Media Migration"
    // @endcode
    $this->drush('migrate:status', ['--names-only'], [
      'group' => 'default',
      'tag' => MediaMigration::MIGRATION_TAG_MAIN,
    ]);

    $this->assertDrushMigrateStatusOutputHasAllLines([
      'Group: Default (default) d7_file_plain_type:image',
      'Group: Default (default) d7_file_plain_source_field:image',
      'Group: Default (default) d7_file_plain_source_field_config:image',
      'Group: Default (default) d7_file_plain_type:document',
      'Group: Default (default) d7_file_plain_source_field:document',
      'Group: Default (default) d7_file_plain_source_field_config:document',
      'Group: Default (default) d7_file_plain_type:video',
      'Group: Default (default) d7_file_plain_source_field:video',
      'Group: Default (default) d7_file_plain_source_field_config:video',
      'Group: Default (default) d7_file_plain_type:audio',
      'Group: Default (default) d7_file_plain_source_field:audio',
      'Group: Default (default) d7_file_plain_source_field_config:audio',
      'Group: Default (default) d7_file_plain_widget:image',
      'Group: Default (default) d7_file_plain_widget:document',
      'Group: Default (default) d7_file_plain_widget:video',
      'Group: Default (default) d7_file_plain_widget:audio',
      'Group: Default (default) d7_file_plain:public:image',
      'Group: Default (default) d7_file_plain:public:text',
      'Group: Default (default) d7_file_plain:public:application',
      'Group: Default (default) d7_file_plain:public:video',
      'Group: Default (default) d7_file_plain:public:audio',
      'Group: Default (default) d7_file_plain_formatter:image',
      'Group: Default (default) d7_file_plain_formatter:document',
      'Group: Default (default) d7_file_plain_formatter:video',
      'Group: Default (default) d7_file_plain_formatter:audio',
    ]);

    // Execute the migrate import "media config" drush command.
    // @code
    // drush migrate:import\
    //   --execute-dependencies\
    //   --group=default\
    //   --tag="Media Configuration"
    // @endcode
    $this->drush('migrate:import', ['--execute-dependencies'], [
      'group' => 'default',
      'tag' => MediaMigration::MIGRATION_TAG_CONFIG,
    ]);

    // Execute the migrations of media entities.
    // @code
    // drush migrate:import\
    //   --execute-dependencies\
    //   --group=default\
    //   --tag="Media Entity"
    // @endcode
    $this->drush('migrate:import', ['--execute-dependencies'], [
      'group' => 'default',
      'tag' => MediaMigration::MIGRATION_TAG_CONTENT,
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

}

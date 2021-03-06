<?php

namespace Drupal\Tests\media_migration\Kernel\Migrate;

use Drupal\Core\Site\Settings;
use Drupal\media_migration\MediaMigration;
use Drupal\Tests\media_migration\Traits\MediaMigrationTestTrait;
use Drupal\Tests\migrate_drupal\Kernel\MigrateDrupalTestBase;

/**
 * Base class for Media Migration kernel tests.
 */
abstract class MediaMigrationTestBase extends MigrateDrupalTestBase {

  use MediaMigrationTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->loadFixture($this->getFixtureFilePath());
    $module_handler = \Drupal::moduleHandler();

    $this->installEntitySchema('file');
    $this->installSchema('file', 'file_usage');
    if ($module_handler->moduleExists('node')) {
      $this->installEntitySchema('node');
      $this->installSchema('node', 'node_access');
    }
    if ($module_handler->moduleExists('entity_embed')) {
      $this->installEntitySchema('embed_button');
    }
    $this->installEntitySchema('media');
    if ($module_handler->moduleExists('comment')) {
      $this->installEntitySchema('comment');
      $this->installSchema('comment', 'comment_entity_statistics');
    }
    $this->installSchema('media_migration', MediaMigration::MEDIA_UUID_PROPHECY_TABLE);
  }

  /**
   * Changes the entity embed token transform destination filter plugin.
   *
   * @param string $new_filter_plugin_id
   *   The new token transform destination plugin ID.
   */
  protected function setEmbedTokenDestinationFilterPlugin($new_filter_plugin_id) {
    $current_filter_plugin_id = MediaMigration::getEmbedTokenDestinationFilterPlugin();

    if ($new_filter_plugin_id !== $current_filter_plugin_id) {
      $this->setSetting(MediaMigration::MEDIA_TOKEN_DESTINATION_FILTER_SETTINGS, $new_filter_plugin_id);
    }
  }

  /**
   * Sets the method of the embed media reference.
   *
   * @param string $new_reference_method
   *   The reference method to set. This can be 'id', or 'uuid'.
   */
  protected function setEmbedMediaReferenceMethod($new_reference_method) {
    $current_method = Settings::get(MediaMigration::MEDIA_REFERENCE_METHOD_SETTINGS);

    if ($current_method !== $new_reference_method) {
      $this->setSetting(MediaMigration::MEDIA_REFERENCE_METHOD_SETTINGS, $new_reference_method);
    }
  }

  /**
   * Sets the type of the node migration.
   *
   * @param bool $classic_node_migration
   *   Whether nodes should be migrated with the 'classic' way. If this is
   *   FALSE, and the current Drupal instance has the 'complete' migration, then
   *   the complete node migration will be used.
   */
  protected function setClassicNodeMigration(bool $classic_node_migration) {
    $current_method = Settings::get('migrate_node_migrate_type_classic', FALSE);

    if ($current_method !== $classic_node_migration) {
      $this->setSetting('migrate_node_migrate_type_classic', $classic_node_migration);
    }
  }

  /**
   * Executes migrations of the media source database.
   *
   * @param bool $classic_node_migration
   *   Whether the classic node migration has to be executed or not.
   */
  protected function executeMediaMigrations(bool $classic_node_migration = FALSE) {
    // The Drupal 8|9 entity revision migration causes a file not found
    // exception without properly migrated files. For this test, it is enough to
    // properly migrate the public files.
    $fs_fixture_path = implode(DIRECTORY_SEPARATOR, [
      DRUPAL_ROOT,
      drupal_get_path('module', 'media_migration'),
      'tests',
      'fixtures',
    ]);
    $file_migration = $this->getMigration('d7_file');
    $source = $file_migration->getSourceConfiguration();
    $source['constants']['source_base_path'] = $fs_fixture_path;
    $file_migration->set('source', $source);

    $this->executeMigration($file_migration);

    $this->executeMediaConfigurationMigrations();

    $this->executeMigrations([
      'd7_view_modes',
      'd7_field',
      'd7_comment_type',
      'd7_node_type',
      'd7_field_instance',
      'd7_field_formatter_settings',
      'd7_field_instance_widget_settings',
      'd7_embed_button_media',
      'd7_filter_format',
      'd7_user_role',
      'd7_user',
      $classic_node_migration ? 'd7_node' : 'd7_node_complete',
      'd7_file_entity',
    ]);
  }

  /**
   * Executes the media configuration migrations (types, fields etc).
   */
  protected function executeMediaConfigurationMigrations() {
    $this->executeMigrations([
      // @todo: Every migration that uses the "media_wysiwyg_filter" process
      // plugin should depend on "d7_media_view_modes".
      'd7_media_view_modes',
      'd7_file_entity_type',
      'd7_file_entity_source_field',
      'd7_file_entity_source_field_config',
      'd7_file_entity_formatter',
      'd7_file_entity_widget',
    ]);
  }

}

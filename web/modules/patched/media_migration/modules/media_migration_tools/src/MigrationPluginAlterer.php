<?php

namespace Drupal\media_migration_tools;

use Drupal\media_migration\MediaMigration;

/**
 * Migration plugin alterer for "fixing" migrations provided by Migrate Tools.
 */
class MigrationPluginAlterer {

  /**
   * Alters migrate plugins.
   *
   * @param array $migrations
   *   The array of migration plugins.
   */
  public function alter(array &$migrations) {
    $this->switchIdMapPlugin($migrations);
    $this->addRequirementsKey($migrations);
  }

  /**
   * Re-adds ID map plugin config "smart_sql" which can handle long IDs.
   *
   * This method is required only because migrate_plus does not define the ID
   * map plugin property configuration (or scheme) for its migration entities.
   *
   * @param array $migrations
   *   The array of migration plugins.
   */
  protected function switchIdMapPlugin(array &$migrations) {
    // Collect all derived media migrations.
    $file_to_media_migrations = array_filter($migrations, function (array $migration_definition) {
      $migration_tags = $migration_definition['migration_tags'] ?? [];
      return in_array(MediaMigration::MIGRATION_TAG_MAIN, $migration_tags, TRUE) && !empty($migration_definition['source']['destination_media_type_id']);
    });
    // Re-add the missing ID map plugin configuration.
    foreach ($file_to_media_migrations as $migration_plugin_id => $file_to_media_migration_def) {
      $migrations[$migration_plugin_id]['idMap'] = [
        'plugin' => 'smart_sql',
      ];
    }
  }

  /**
   * Adds a "requirements" key to media migration plugins.
   *
   * This method only required by migrate_tools since it searches migration
   * dependencies in this key.
   *
   * @param array $migrations
   *   The array of migration plugins.
   */
  protected function addRequirementsKey(array &$migrations) {
    foreach ($migrations as $migration_plugin_id => $migration_definition) {
      if (
        empty($migration_definition['migration_tags']) ||
        !in_array(MediaMigration::MIGRATION_TAG_MAIN, $migration_definition['migration_tags'], TRUE) &&
        empty($migration_definition['migration_dependencies']['required'])
      ) {
        continue;
      }
      $migrations[$migration_plugin_id]['requirements'] = $this->getRecursiveMigrationDependencies($migrations, $migration_plugin_id);
    }
  }

  /**
   * Returnas every dependency of a migration.
   *
   * @param array $all_migration_plugins
   *   Array of every available migration plugin definitions, keyed by their ID.
   * @param string $migration_plugin_id
   *   The ID of the migration plugin whichs dependecies should be discovered.
   *
   * @return array
   *   An array of the migration plugin IDs the given migration depends on.
   */
  private function getRecursiveMigrationDependencies(array $all_migration_plugins, string $migration_plugin_id): array {
    $main_migration_dependencies = $all_migration_plugins[$migration_plugin_id]['migration_dependencies']['required'] ?? [];
    $extra_dependencies = [];

    if (!empty($main_migration_dependencies)) {
      foreach ($main_migration_dependencies as $migration_dependency) {
        $extra_dependencies = array_unique(
          array_merge(
            $extra_dependencies,
            $this->getRecursiveMigrationDependencies($all_migration_plugins, $migration_dependency)
          )
        );
      }
    }

    $all_dependencies = array_unique(
      array_merge(
        array_values($main_migration_dependencies),
        array_values($extra_dependencies)
      )
    );

    // Migrate plus adds a "migration_config_deriver:" prefix for migration
    // entity based migrations. But that is not the migration plugin ID; the
    // the real ID begins after that migration deriver ID.
    // See migrate_plus_migration_plugins_alter().
    return array_unique(
      array_map(function ($migration_plugin_id) {
        return preg_replace('/^migration_config_deriver:/', '', $migration_plugin_id);
      }, $all_dependencies)
    );
  }

}

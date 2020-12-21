<?php

namespace Drupal\media_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides a Media Migration Null Coalesce process plugin.
 *
 * This migrate process plugin is the copy of
 * \Drupal\migrate\Plugin\migrate\process\NullCoalesce from Drupal 8.8.x.
 *
 * Given a set of values provided to the plugin, the plugin will return the
 * first non-null value.
 *
 * Available configuration keys:
 * - source: The input array.
 * - default_value: (optional) The value to return if all values are NULL.
 *   if not provided, NULL is returned if all values are NULL.
 *
 * Example:
 * Given source keys of foo, bar, and baz:
 *
 * process_key:
 *   plugin: media_migration_null_coalesce
 *   source:
 *     - foo
 *     - bar
 *     - baz
 *
 * This plugin will return the equivalent of `foo ?? bar ?? baz`
 *
 * @todo Remove when Drupal core 8.7.x security support ends.
 *
 * @MigrateProcessPlugin(
 *   id = "media_migration_null_coalesce"
 * )
 */
class MediaMigrationNullCoalesce extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!is_array($value)) {
      throw new MigrateException("The input value should be an array.");
    }
    foreach ($value as $val) {
      if (NULL !== $val) {
        return $val;
      }
    }
    if (isset($this->configuration['default_value'])) {
      return $this->configuration['default_value'];
    }
    return NULL;
  }

}

<?php

namespace Drupal\media_migration;

use Drupal\migrate\Row;

/**
 * Interface for media_wysiwyg plugins.
 */
interface MediaWysiwygInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label();

  /**
   * Processes migrations.
   *
   * @param array $migrations
   *   The migrations array.
   * @param \Drupal\migrate\Row $row
   *   A field instance row.
   * @param array $migration_plugin_ids
   *   An array of the migration plugin IDs that should be processed. Optional.
   *
   * @return array
   *   The processed migrations.
   */
  public function process(array $migrations, Row $row, array $migration_plugin_ids = []);

}

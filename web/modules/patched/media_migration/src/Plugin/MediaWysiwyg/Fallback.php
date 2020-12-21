<?php

namespace Drupal\media_migration\Plugin\MediaWysiwyg;

use Drupal\media_migration\MediaWysiwygPluginBase;
use Drupal\migrate\Row;

/**
 * Fallback Media WYSIWYG plugin for entities whose ID remains the same.
 *
 * @MediaWysiwyg(
 *   id = "fallback",
 *   label = @Translation("Fallback"),
 *   description = @Translation("Fallback plugin.")
 * )
 */
class Fallback extends MediaWysiwygPluginBase {

  /**
   * {@inheritdoc}
   */
  public function process(array $migrations, Row $row, array $migration_plugin_ids = []) {
    foreach ($migration_plugin_ids as $migration_plugin_id) {
      if (isset($migrations[$migration_plugin_id])) {
        $migrations = $this->appendProcessor($migrations, $migration_plugin_id, $row->getSourceProperty('field_name'));
      }
    }

    return $migrations;
  }

}

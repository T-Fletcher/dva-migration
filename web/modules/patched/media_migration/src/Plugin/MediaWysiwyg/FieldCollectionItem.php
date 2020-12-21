<?php

namespace Drupal\media_migration\Plugin\MediaWysiwyg;

use Drupal\media_migration\MediaWysiwygPluginBase;
use Drupal\migrate\Row;

/**
 * FieldCollection Media WYSIWYG plugin.
 *
 * @MediaWysiwyg(
 *   id = "field_collection_item",
 *   label = @Translation("Field Collection"),
 *   description = @Translation("Field Collection plugin.")
 * )
 */
class FieldCollectionItem extends MediaWysiwygPluginBase {

  /**
   * {@inheritdoc}
   */
  public function process(array $migrations, Row $row, array $migration_plugin_ids = []) {
    foreach (['d7_field_collection', 'd7_field_collection_revisions'] as $migration_id) {
      // The field collection name is the bundle without 'field_'.
      $field_collection = str_replace('field_', '', $row->getSourceProperty('bundle'));
      $derived_migration = $migration_id . ':' . $field_collection;
      if (isset($migrations[$derived_migration])) {
        $migrations = $this->appendProcessor($migrations, $derived_migration, $row->getSourceProperty('field_name'));
      }
    }

    return $migrations;
  }

}

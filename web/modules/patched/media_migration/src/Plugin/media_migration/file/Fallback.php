<?php

namespace Drupal\media_migration\Plugin\media_migration\file;

use Drupal\Core\Database\Connection;
use Drupal\media_migration\FileDealerBase;
use Drupal\migrate\Row;

/**
 * General plugin for any kind of file.
 *
 * @FileDealer(
 *   id = "fallback"
 * )
 */
class Fallback extends FileDealerBase {

  /**
   * {@inheritdoc}
   */
  public function getDestinationMediaSourcePluginId() {
    switch ($this->configuration['mime']) {
      case 'audio':
        return 'audio_file';

      case 'image':
        return 'image';

      case 'video':
        return 'video_file';

      default:
        return 'file';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDestinationMediaTypeIdBase() {
    switch ($this->configuration['mime']) {
      case 'audio':
      case 'image':
      case 'video':
        return $this->configuration['mime'];

      default:
        return 'document';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDestinationMediaSourceFieldName() {
    // Document's field name should be field_media_document[_private].
    if ($this->getDestinationMediaTypeIdBase() === 'document') {
      return implode('_', array_filter([
        'field',
        'media',
        'document',
        $this->configuration['scheme'] === 'public' ? NULL : $this->configuration['scheme'],
      ]));
    }
    return parent::getDestinationMediaSourceFieldName();
  }

  /**
   * {@inheritdoc}
   */
  public function getDestinationMediaTypeSourceFieldLabel() {
    switch ($this->getDestinationMediaSourcePluginId()) {
      case 'audio_file':
        return 'Audio file';

      case 'video_file':
        return 'Video file';
    }

    return parent::getDestinationMediaTypeSourceFieldLabel();
  }

  /**
   * {@inheritdoc}
   */
  public function alterMediaEntityMigrationDefinition(array &$migration_definition, Connection $connection): void {
    parent::alterMediaEntityMigrationDefinition($migration_definition, $connection);
    $source_field_name = $this->getDestinationMediaSourceFieldName();
    $migration_definition['process'][$source_field_name . '/display'] = 'display';
    $migration_definition['process'][$source_field_name . '/description'] = 'description';
  }

  /**
   * {@inheritdoc}
   */
  public function prepareMediaSourceFieldInstanceRow(Row $row, Connection $connection): void {
    parent::prepareMediaSourceFieldInstanceRow($row, $connection);
    $show_description_field = FALSE;
    foreach ($this->getFileFieldData($connection, FALSE) as $data) {
      if (!empty($data['field_instance_data']['settings']['description_field'])) {
        $show_description_field = TRUE;
        break 1;
      }
    }
    $row->setSourceProperty('settings/description_field', $show_description_field);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareMediaSourceFieldFormatterRow(Row $row, Connection $connection): void {
    parent::prepareMediaSourceFieldFormatterRow($row, $connection);
    $original_options = $row->getSourceProperty('options') ?? [];

    switch ($this->getDestinationMediaSourcePluginId()) {
      case 'audio_file':
        $options = [
          'type' => 'file_audio',
          'settings' => [
            'controls' => TRUE,
            'autoplay' => FALSE,
            'loop' => FALSE,
            'multiple_file_display_type' => 'tags',
          ],
        ] + $original_options;
        $row->setSourceProperty('options', $options);
        break;

      case 'video_file':
        $options = [
          'type' => 'file_video',
          'settings' => [
            'muted' => FALSE,
            'width' => 640,
            'height' => 480,
          ],
        ] + $original_options;
        $row->setSourceProperty('options', $options);
        break;

      case 'image':
        $original_options['settings'] = [
          'image_style' => 'large',
        ];
        $row->setSourceProperty('options', $original_options);
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function prepareMediaEntityRow(Row $row, Connection $connection): void {
    parent::prepareMediaEntityRow($row, $connection);

    foreach ($this->getFileData($connection, $row->getSourceProperty('fid')) as $data_key => $data_value) {
      $row->setSourceProperty($data_key, $data_value);
    }
  }

  /**
   * Get the name of the file fields from the source database.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection of the source Drupal 7 instance.
   * @param bool $field_names_only
   *   Whether only the name of the file fields should be returned. Defaults to
   *   TRUE.
   *
   * @return array
   *   The array of the available file fields.
   */
  protected function getFileFieldData(Connection $connection, bool $field_names_only = TRUE): array {
    $field_query = $connection->select('field_config', 'fs')
      ->fields('fs', ['field_name'])
      ->condition('fs.type', 'file')
      ->condition('fs.active', 1)
      ->condition('fs.deleted', 0)
      ->condition('fs.storage_active', 1)
      ->condition('fi.deleted', 0);
    $field_query->join('field_config_instance', 'fi', 'fs.id = fi.field_id');

    if ($field_names_only) {
      return array_keys($field_query->execute()->fetchAllAssoc('field_name'));
    }

    $field_query->addField('fs', 'data', 'field_storage_data');
    $field_query->addField('fi', 'data', 'field_instance_data');

    $fields_data = [];
    foreach ($field_query->execute()->fetchAll(\PDO::FETCH_ASSOC) as $item) {
      foreach (['field_storage_data', 'field_instance_data'] as $data_key) {
        $item[$data_key] = unserialize($item[$data_key]);
      }
      $fields_data[] = $item;
    }

    return $fields_data;
  }

  /**
   * Returns display and description properties of the specified file.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection of the source Drupal 7 instance.
   * @param string|int $file_id
   *   The ID of the file.
   *
   * @return array
   *   An array of those properties whose value is not empty.
   */
  protected function getFileData(Connection $connection, $file_id): array {
    foreach ($this->getFileFieldData($connection) as $field_name) {
      $field_table_name = "field_data_$field_name";
      $data_query = $connection->select($field_table_name, $field_name);
      $data_query->addField($field_name, "{$field_name}_display", 'display');
      $data_query->addField($field_name, "{$field_name}_description", 'description');
      $data_query->condition("{$field_name}_fid", $file_id);

      if (!empty($results = $data_query->execute()->fetchAll(\PDO::FETCH_ASSOC))) {
        $result = reset($results);
        return array_filter($result);
      }
    }

    return [];
  }

}

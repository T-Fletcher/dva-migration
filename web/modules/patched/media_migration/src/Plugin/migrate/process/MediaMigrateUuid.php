<?php

namespace Drupal\media_migration\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\media_migration\MediaMigrationUuidOracle;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates the prophesized UUID for media items.
 *
 * @MigrateProcessPlugin(
 *   id = "media_migrate_uuid"
 * )
 */
class MediaMigrateUuid extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The media UUID oracle.
   *
   * @var \Drupal\media_migration\MediaMigrationUuidOracle
   */
  protected $mediaUuidOracle;

  /**
   * Constructs a MediaMigrateUuid instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\media_migration\MediaMigrationUuidOracle $media_uuid_oracle
   *   The media UUID oracle.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MediaMigrationUuidOracle $media_uuid_oracle) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mediaUuidOracle = $media_uuid_oracle;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('media_migration.media_uuid_oracle')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return $this->mediaUuidOracle->getMediaUuidProphecy((int) $value);
  }

}

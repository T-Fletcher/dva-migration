<?php

namespace Drupal\media_migration;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Media Migration's UUID oracle.
 *
 * Predicts the UUID property of a media entity that does not yet exist.
 */
final class MediaMigrationUuidOracle {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The media entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * The UUID generator service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * Constructs MediaMigrationUuidOracle.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   A database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_generator
   *   The UUID generator service.
   */
  public function __construct(Connection $database, EntityTypeManagerInterface $entity_type_manager, UuidInterface $uuid_generator) {
    $this->database = $database;
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->uuidGenerator = $uuid_generator;
  }

  /**
   * Returns the UUID of a media entity based on its source ID.
   *
   * @param int $source_id
   *   The original ID of the media entity in the source database.
   *
   * @throws \Exception
   */
  public function getMediaUuid(int $source_id) {
    // If the media entity already exist, return its UUID.
    if ($media = $this->mediaStorage->load($source_id)) {
      return $media->uuid();
    }

    // If the media does not exist, try to get its UUID from our prophecy table.
    if (!($uuid_prophecy = $this->getMediaUuidProphecy($source_id))) {
      $uuid_prophecy = $this->setMediaProphecy($source_id);
    }

    return $uuid_prophecy;
  }

  /**
   * Returns the UUID prophecy if it exists.
   *
   * @param int $source_id
   *   The source media entity's identifier.
   *
   * @return string|null
   *   The UUID, or NULL if it does not exist at the moment.
   */
  public function getMediaUuidProphecy(int $source_id) {
    // @todo Before this, we should check the source database for entity UUIDs
    //   somehow, to support the UUID module.
    $results = $this->database->select(MediaMigration::MEDIA_UUID_PROPHECY_TABLE, 'mupt')
      ->fields('mupt')
      ->condition('mupt.' . MediaMigration::MEDIA_UUID_PROPHECY_SOURCEID_COL, $source_id)
      ->execute()->fetchAll();

    return isset($results[0]->{MediaMigration::MEDIA_UUID_PROPHECY_UUID_COL}) ?
      $results[0]->{MediaMigration::MEDIA_UUID_PROPHECY_UUID_COL} :
      NULL;
  }

  /**
   * Saves a UUID prophecy if it doesn't exist.
   *
   * @param int $source_id
   *   The source media entity's identifier.
   *
   * @return string
   *   The UUID to save.
   *
   * @throws \Exception
   */
  private function setMediaProphecy(int $source_id) {
    if (!$this->getMediaUuidProphecy($source_id)) {
      $uuid = $this->uuidGenerator->generate();
      $this->database->insert(MediaMigration::MEDIA_UUID_PROPHECY_TABLE)
        ->fields([
          MediaMigration::MEDIA_UUID_PROPHECY_SOURCEID_COL => $source_id,
          MediaMigration::MEDIA_UUID_PROPHECY_UUID_COL => $uuid,
        ])
        ->execute();

      return $uuid;
    }

    throw new \Exception(sprintf('Cannot create prophecy for the media entity with source id %i', $source_id));
  }

}

<?php

namespace Drupal\media_migration;

use Drupal\Core\Database\Connection;

/**
 * Base implementation of file dealer plugins.
 */
abstract class FileDealerBase extends MediaDealerBase implements FileDealerPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function alterMediaEntityMigrationDefinition(array &$migration_definition, Connection $connection): void {
    $migration_definition['process'][$this->getDestinationMediaSourceFieldName() . '/target_id'] = 'fid';
  }

}

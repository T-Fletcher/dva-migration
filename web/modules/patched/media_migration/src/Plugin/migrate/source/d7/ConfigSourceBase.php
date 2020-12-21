<?php

namespace Drupal\media_migration\Plugin\migrate\source\d7;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Base class for media configurations.
 */
abstract class ConfigSourceBase extends DrupalSqlBase {

  use MediaMigrationDatabaseTrait;

  const MULTIPLE_SEPARATOR = '::';

}

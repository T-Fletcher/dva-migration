<?php

namespace Drupal\Tests\media_migration\Functional;

use Drupal\Core\Database\Database;
use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Base class for testing media migrations executed with Drush.
 */
abstract class DrushTestBase extends BrowserTestBase {

  use DrushTestTrait;

  /**
   * The source database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $sourceDatabase;

  /**
   * {@inheritdoc}
   */
  public $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'file',
    'image',
    'media',
    'media_migration',
    'media_migration_test_oembed',
    'migrate_tools',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getFixtureFilePath() {
    return drupal_get_path('module', 'media_migration') . '/tests/fixtures/drupal7_media.php';
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createMigrationConnection();
    $this->sourceDatabase = Database::getConnection('default', 'migrate');
    $this->loadFixture($this->getFixtureFilePath());

    // Delete preexisting media types.
    $media_types = $this->container->get('entity_type.manager')
      ->getStorage('media_type')
      ->loadMultiple();
    foreach ($media_types as $media_type) {
      $media_type->delete();
    }

    // Delete preexisting node types.
    $node_types = $this->container->get('entity_type.manager')
      ->getStorage('node_type')
      ->loadMultiple();
    foreach ($node_types as $node_type) {
      $node_type->delete();
    }
  }

  /**
   * Loads a database fixture into the source database connection.
   *
   * This method is the exact copy of
   * Drupal\Tests\migrate\Kernel\MigrateTestBase::loadFixture().
   *
   * @param string $path
   *   Path to the dump file.
   */
  protected function loadFixture($path) {
    $default_db = Database::getConnection()->getKey();
    Database::setActiveConnection($this->sourceDatabase->getKey());

    if (substr($path, -3) == '.gz') {
      $path = 'compress.zlib://' . $path;
    }
    require $path;

    Database::setActiveConnection($default_db);
  }

  /**
   * Changes the database connection to the prefixed one.
   *
   * This method is the exact copy of
   * Drupal\Tests\migrate\Kernel\MigrateTestBase::createMigrationConnection().
   *
   * @todo Remove when we don't use global. https://www.drupal.org/node/2552791
   */
  private function createMigrationConnection() {
    // If the backup already exists, something went terribly wrong.
    // This case is possible, because database connection info is a static
    // global state construct on the Database class, which at least persists
    // for all test methods executed in one PHP process.
    if (Database::getConnectionInfo('simpletest_original_migrate')) {
      throw new \RuntimeException("Bad Database connection state: 'simpletest_original_migrate' connection key already exists. Broken test?");
    }

    // Clone the current connection and replace the current prefix.
    $connection_info = Database::getConnectionInfo('migrate');
    if ($connection_info) {
      Database::renameConnection('migrate', 'simpletest_original_migrate');
    }
    $connection_info = Database::getConnectionInfo('default');
    foreach ($connection_info as $target => $value) {
      $prefix = is_array($value['prefix']) ? $value['prefix']['default'] : $value['prefix'];
      // Simpletest uses 7 character prefixes at most so this can't cause
      // collisions.
      $connection_info[$target]['prefix']['default'] = $prefix . '0';

      // Add the original simpletest prefix so SQLite can attach its database.
      // @see \Drupal\Core\Database\Driver\sqlite\Connection::init()
      $connection_info[$target]['prefix'][$value['prefix']['default']] = $value['prefix']['default'];
    }
    Database::addConnectionInfo('migrate', 'default', $connection_info['default']);

    $this->writeSettings([
      'databases' => [
        "migrate']['default" => (object) [
          'value' => $connection_info['default'],
          'required' => TRUE,
        ],
      ],
    ]);
  }

  /**
   * Asserts that the expected rows are present in the output of migrate:status.
   *
   * @param string[] $expected_lines
   *   The expected lines of the migrate:status command's response.
   */
  protected function assertDrushMigrateStatusOutputHasAllLines(array $expected_lines) {
    $drush_output_array = explode("\n", $this->getSimplifiedOutput());
    $filtered_output = array_reduce($drush_output_array, function (array $carry, $output_line) {
      if (!preg_match('/^[-\s]+$/', $output_line)) {
        $carry[] = $output_line;
      }
      return $carry;
    }, []);
    $missing_from_output = array_diff($filtered_output, $expected_lines);
    $extra_output = array_diff($expected_lines, $filtered_output);

    $this->assertEmpty($extra_output);
    $this->assertEquals(['Group Migration ID'], $missing_from_output);
  }

}

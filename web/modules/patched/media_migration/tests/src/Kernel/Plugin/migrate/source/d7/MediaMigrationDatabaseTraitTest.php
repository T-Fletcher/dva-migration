<?php

namespace Drupal\Tests\media_migration\Kernel\Plugin\migrate\source\d7;

use Drupal\media_migration\Plugin\migrate\source\d7\MediaMigrationDatabaseTrait;
use Drupal\Tests\migrate\Kernel\MigrateTestBase;

/**
 * @coversDefaultClass \Drupal\media_migration\Plugin\migrate\source\d7\MediaMigrationDatabaseTrait
 *
 * @group media_migration
 */
class MediaMigrationDatabaseTraitTest extends MigrateTestBase {

  /**
   * The trait we test.
   *
   * @var \Drupal\media_migration\Plugin\migrate\source\d7\MediaMigrationDatabaseTrait
   */
  protected $mediaMigrationDatabaseTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createSourceDatabaseStructure();
    $this->mediaMigrationDatabaseTrait = new TestMediaMigrationDatabaseTrait();
  }

  /**
   * @covers ::getFilePlainBaseQuery
   *
   * @dataProvider providerTest
   */
  public function testGetFilePlainBaseQuery(array $db_records, array $expected_results) {
    $class = new TestMediaMigrationDatabaseTrait();

    // Add records to the source database.
    $this->addRecordsToSourceDatabase($db_records);

    $query = $class->getFilePlainBaseQuery($this->sourceDatabase, FALSE);
    $query->fields('fm', ['fid', 'filename'])->orderBy('fm.fid');
    $results = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $this->assertEquals($expected_results['Plain file'], $results);
  }

  /**
   * @covers ::getFileEntityBaseQuery
   *
   * @dataProvider providerTest
   */
  public function testGetFileEntityBaseQuery(array $db_records, array $expected_results) {
    $class = new TestMediaMigrationDatabaseTrait();

    // Add records to the source database.
    $this->updateSourceDbWithFileTypeColumn();
    $this->addRecordsToSourceDatabase($db_records);

    $query = $class->getFileEntityBaseQuery($this->sourceDatabase, FALSE);
    $query->fields('fm', ['fid', 'filename'])->orderBy('fm.fid');
    $results = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $this->assertEquals($expected_results['File entity'], $results);
  }

  /**
   * Data provider for the tests.
   */
  public function providerTest() {
    $blue_png = [
      'fid' => 1,
      'filename' => 'blue.png',
      'scheme' => 'public',
      'mime' => 'image',
    ];
    $blue_png_fe = [
      'fid' => 1,
      'filename' => 'blue.png',
      'scheme' => 'public',
      'type' => 'image',
    ];
    $green_jpg = [
      'fid' => 2,
      'filename' => 'green.jpg',
      'scheme' => 'public',
      'mime' => 'image',
    ];
    $green_jpg_fe = [
      'fid' => 2,
      'filename' => 'green.jpg',
      'scheme' => 'public',
      'type' => 'image',
    ];
    $licence_txt = [
      'fid' => 3,
      'filename' => 'LICENSE.txt',
      'scheme' => 'public',
      'mime' => 'text',
    ];
    $licence_txt_fe = [
      'fid' => 3,
      'filename' => 'LICENSE.txt',
      'scheme' => 'public',
      'type' => 'document',
    ];

    $test_cases = [
      'No webform or user file usage' => [
        'DB records' => [
          'file_usage' => [
            [
              'fid' => 1,
              'module' => 'file',
              'type' => 'node',
              'id' => 1,
              'count' => 1,
            ],
            [
              'fid' => 2,
              'module' => 'file',
              'type' => 'node',
              'id' => 2,
              'count' => 1,
            ],
            [
              'fid' => 3,
              'module' => 'file',
              'type' => 'node',
              'id' => 3,
              'count' => 1,
            ],
          ],
        ],
        'Expected results' => [
          'Plain file' => [
            $blue_png,
            $green_jpg,
            $licence_txt,
          ],
          'File entity' => [
            $blue_png_fe,
            $green_jpg_fe,
            $licence_txt_fe,
          ],
        ],
      ],
      'Single user image usage with count 1, no webform submission file usage' => [
        'DB records' => [
          'users' => [
            [
              'uid' => 1,
              'name' => 'admin',
              'pass' => 'pass',
              'status' => 1,
              'picture' => 1,
            ],
          ],
          'file_usage' => [
            [
              'fid' => 1,
              'module' => 'file',
              'type' => 'user',
              'id' => 1,
              'count' => 1,
            ],
            [
              'fid' => 2,
              'module' => 'file',
              'type' => 'submission',
              'id' => 2314,
              'count' => 1,
            ],
            [
              'fid' => 2,
              'module' => 'webform',
              'type' => 'not_a_submission',
              'id' => 34,
              'count' => 1,
            ],
          ],
        ],
        'Expected results' => [
          'Plain file' => [
            $green_jpg,
            $licence_txt,
          ],
          'File entity' => [
            $green_jpg_fe,
            $licence_txt_fe,
          ],
        ],
      ],
      'Two webform submission file usage with count 1, no user pictures' => [
        'DB records' => [
          'file_usage' => [
            [
              'fid' => 1,
              'module' => 'webform',
              'type' => 'submission',
              'id' => 1,
              'count' => 1,
            ],
            [
              'fid' => 2,
              'module' => 'file',
              'type' => 'submission',
              'id' => 2314,
              'count' => 1,
            ],
            [
              'fid' => 3,
              'module' => 'webform',
              'type' => 'not_a_submission',
              'id' => 34,
              'count' => 1,
            ],
          ],
        ],
        'Expected results' => [
          'Plain file' => [
            $licence_txt,
          ],
          'File entity' => [
            $licence_txt_fe,
          ],
        ],
      ],
      'A user file is used somewhere else as well' => [
        'DB records' => [
          'users' => [
            [
              'uid' => 1,
              'name' => 'admin',
              'pass' => 'pass',
              'status' => 1,
              'picture' => 1,
            ],
          ],
          'file_usage' => [
            [
              'fid' => 1,
              'module' => 'file',
              'type' => 'node',
              'id' => 1,
              'count' => 1,
            ],
            [
              'fid' => 1,
              'module' => 'file',
              'type' => 'user',
              'id' => 1,
              'count' => 1,
            ],
          ],
        ],
        'Expected results' => [
          'Plain file' => [
            $blue_png,
            $green_jpg,
            $licence_txt,
          ],
          'File entity' => [
            $blue_png_fe,
            $green_jpg_fe,
            $licence_txt_fe,
          ],
        ],
      ],
      'A webform submission file is used somewhere' => [
        'DB records' => [
          'file_usage' => [
            [
              'fid' => 3,
              'module' => 'webform',
              'type' => 'submission',
              'id' => 1,
              'count' => 1,
            ],
            [
              'fid' => 3,
              'module' => 'file',
              'type' => 'node',
              'id' => 1,
              'count' => 1,
            ],
          ],
        ],
        'Expected results' => [
          'Plain file' => [
            $blue_png,
            $green_jpg,
            $licence_txt,
          ],
          'File entity' => [
            $blue_png_fe,
            $green_jpg_fe,
            $licence_txt_fe,
          ],
        ],
      ],
      'A webform submission file is used as user picture' => [
        'DB records' => [
          'users' => [
            [
              'uid' => 1,
              'name' => 'admin',
              'pass' => 'pass',
              'status' => 1,
              'picture' => 1,
            ],
          ],
          'file_usage' => [
            [
              'fid' => 1,
              'module' => 'webform',
              'type' => 'submission',
              'id' => 1,
              'count' => 1,
            ],
            [
              'fid' => 1,
              'module' => 'file',
              'type' => 'user',
              'id' => 1,
              'count' => 2,
            ],
          ],
        ],
        'Expected results' => [
          'Plain file' => [
            $green_jpg,
            $licence_txt,
          ],
          'File entity' => [
            $green_jpg_fe,
            $licence_txt_fe,
          ],
        ],
      ],
      'A webform submission file is used as user picture AND somewhere else' => [
        'DB records' => [
          'users' => [
            [
              'uid' => 1,
              'name' => 'admin',
              'pass' => 'pass',
              'status' => 1,
              'picture' => 1,
            ],
          ],
          'file_usage' => [
            [
              'fid' => 1,
              'module' => 'webform',
              'type' => 'submission',
              'id' => 1,
              'count' => 1,
            ],
            [
              'fid' => 1,
              'module' => 'file',
              'type' => 'user',
              'id' => 1,
              'count' => 1,
            ],
            [
              'fid' => 1,
              'module' => 'file',
              'type' => 'node',
              'id' => 1,
              'count' => 1,
            ],
          ],
        ],
        'Expected results' => [
          'Plain file' => [
            $blue_png,
            $green_jpg,
            $licence_txt,
          ],
          'File entity' => [
            $blue_png_fe,
            $green_jpg_fe,
            $licence_txt_fe,
          ],
        ],
      ],
    ];

    return $test_cases;
  }

  /**
   * Creates the required tables with the expected structure.
   */
  protected function createSourceDatabaseStructure() {
    // Managed file table.
    $this->sourceDatabase->schema()->createTable('file_managed', [
      'fields' => [
        'fid' => [
          'type' => 'serial',
          'not null' => TRUE,
          'size' => 'normal',
          'unsigned' => TRUE,
        ],
        'uid' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'default' => '0',
          'unsigned' => TRUE,
        ],
        'filename' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '255',
          'default' => '',
        ],
        'uri' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '255',
          'default' => '',
        ],
        'filemime' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '255',
          'default' => '',
        ],
        'filesize' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'big',
          'default' => '0',
          'unsigned' => TRUE,
        ],
        'status' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'tiny',
          'default' => '0',
        ],
        'timestamp' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'default' => '0',
          'unsigned' => TRUE,
        ],
      ],
      'primary key' => [
        'fid',
      ],
      'unique keys' => [
        'uri' => [
          'uri',
        ],
      ],
      'indexes' => [
        'uid' => [
          'uid',
        ],
        'status' => [
          'status',
        ],
        'timestamp' => [
          'timestamp',
        ],
      ],
      'mysql_character_set' => 'utf8',
    ]);
    // File usage table.
    $this->sourceDatabase->schema()->createTable('file_usage', [
      'fields' => [
        'fid' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'unsigned' => TRUE,
        ],
        'module' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '255',
          'default' => '',
        ],
        'type' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '64',
          'default' => '',
        ],
        'id' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'default' => '0',
          'unsigned' => TRUE,
        ],
        'count' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'default' => '0',
          'unsigned' => TRUE,
        ],
      ],
      'primary key' => [
        'fid',
        'type',
        'id',
        'module',
      ],
      'indexes' => [
        'type_id' => [
          'type',
          'id',
        ],
        'fid_count' => [
          'fid',
          'count',
        ],
        'fid_module' => [
          'fid',
          [
            'module',
            '191',
          ],
        ],
      ],
      'mysql_character_set' => 'utf8',
    ]);
    // Users table.
    $this->sourceDatabase->schema()->createTable('users', [
      'fields' => [
        'uid' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'default' => '0',
          'unsigned' => TRUE,
        ],
        'name' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '60',
          'default' => '',
        ],
        'pass' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '128',
          'default' => '',
        ],
        'mail' => [
          'type' => 'varchar',
          'not null' => FALSE,
          'length' => '254',
          'default' => '',
        ],
        'theme' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '255',
          'default' => '',
        ],
        'signature' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '255',
          'default' => '',
        ],
        'signature_format' => [
          'type' => 'varchar',
          'not null' => FALSE,
          'length' => '255',
        ],
        'created' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'default' => '0',
        ],
        'access' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'default' => '0',
        ],
        'login' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'default' => '0',
        ],
        'status' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'tiny',
          'default' => '0',
        ],
        'timezone' => [
          'type' => 'varchar',
          'not null' => FALSE,
          'length' => '32',
        ],
        'language' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '12',
          'default' => '',
        ],
        'picture' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'default' => '0',
        ],
        'init' => [
          'type' => 'varchar',
          'not null' => FALSE,
          'length' => '254',
          'default' => '',
        ],
        'data' => [
          'type' => 'blob',
          'not null' => FALSE,
          'size' => 'big',
        ],
      ],
      'primary key' => [
        'uid',
      ],
      'unique keys' => [
        'name' => [
          'name',
        ],
      ],
      'indexes' => [
        'access' => [
          'access',
        ],
        'created' => [
          'created',
        ],
        'mail' => [
          [
            'mail',
            '191',
          ],
        ],
        'picture' => [
          'picture',
        ],
      ],
      'mysql_character_set' => 'utf8',
    ]);

    // Add data.
    $this->sourceDatabase->insert('file_managed')
      ->fields([
        'fid',
        'uid',
        'filename',
        'uri',
        'filemime',
        'filesize',
        'status',
        'timestamp',
      ])
      ->values([
        'fid' => 1,
        'uid' => 1,
        'filename' => 'blue.png',
        'uri' => 'public://blue.png',
        'filemime' => 'image/png',
        'filesize' => 9061,
        'status' => 1,
        'timestamp' => 1587725909,
      ])
      ->values([
        'fid' => 2,
        'uid' => 2,
        'filename' => 'green.jpg',
        'uri' => 'public://field/image/green.jpg',
        'filemime' => 'image/jpeg',
        'filesize' => 11050,
        'status' => 1,
        'timestamp' => 1587730322,
      ])
      ->values([
        'fid' => 3,
        'uid' => 2,
        'filename' => 'LICENSE.txt',
        'uri' => 'public://LICENSE.txt',
        'filemime' => 'text/plain',
        'filesize' => 18002,
        'status' => 1,
        'timestamp' => 1587731111,
      ])
      ->execute();
  }

  /**
   * Updates the source DB's fiel_managed tabke with a type column.
   */
  protected function updateSourceDbWithFileTypeColumn() {
    $this->sourceDatabase->schema()->addField('file_managed', 'type', [
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '50',
      'default' => 'undefined',
      'initial' => 'image',
    ]);
    $this->sourceDatabase->update('file_managed')
      ->fields([
        'type' => 'document',
      ])
      ->condition('fid', 3)
      ->execute();
  }

  /**
   * Updates the source database with the given records.
   *
   * @param string[][][] $db_records
   *   The column values to insert, indexed by the column name, per row,
   *   and the table name.
   *
   * @throws \Exception
   */
  protected function addRecordsToSourceDatabase(array $db_records) {
    foreach ($db_records as $table => $records) {
      foreach ($records as $record) {
        $this->sourceDatabase->insert($table)
          ->fields($record)
          ->execute();
      }
    }
  }

}

/**
 * Test class using the trait.
 */
class TestMediaMigrationDatabaseTrait {

  use MediaMigrationDatabaseTrait {
    getFilePlainBaseQuery as public;
    getFileEntityBaseQuery as public;
  }

}

<?php

namespace Drupal\Tests\media_migration\Unit\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\media_migration\Plugin\migrate\process\MediaMigrationNullCoalesce;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the media_migration_null_coalesce process plugin.
 *
 * @todo Remove when Drupal core 8.7.x security support ends.
 *
 * @group media_migration
 *
 * @coversDefaultClass \Drupal\media_migration\Plugin\migrate\process\MediaMigrationNullCoalesce
 */
class MediaMigrationNullCoalesceTest extends MigrateProcessTestCase {

  /**
   * Tests that an exception is thrown for a non-array value.
   *
   * @covers ::transform
   */
  public function testExceptionOnInvalidValue() {
    $this->expectException(MigrateException::class);
    (new MediaMigrationNullCoalesce([], 'media_migration_null_coalesce', []))->transform('invalid', $this->migrateExecutable, $this->row, 'destinationproperty');
  }

  /**
   * Tests media_migration_null_coalesce.
   *
   * @param array $source
   *   The source value.
   * @param mixed $expected_result
   *   The expected result.
   *
   * @covers ::transform
   *
   * @dataProvider transformDataProvider
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public function testTransform(array $source, $expected_result) {
    $plugin = new MediaMigrationNullCoalesce([], 'media_migration_null_coalesce', []);
    $result = $plugin->transform($source, $this->migrateExecutable, $this->row, 'destinationproperty');
    $this->assertSame($expected_result, $result);
  }

  /**
   * Provides Data for ::testTransform.
   */
  public function transformDataProvider() {
    return [
      'all null' => [
        'source' => [NULL, NULL, NULL],
        'expected_result' => NULL,
      ],
      'false first' => [
        'source' => [FALSE, NULL, NULL],
        'expected_result' => FALSE,
      ],
      'no null' => [
        'source' => ['test', 'test2'],
        'expected_result' => 'test',
      ],
      'string first' => [
        'source' => ['test', NULL, 'test2'],
        'expected_result' => 'test',
      ],
      'empty string' => [
        'source' => [NULL, '', NULL],
        'expected_result' => '',
      ],
      'array' => [
        'source' => [NULL, NULL, [1, 2, 3]],
        'expected_result' => [1, 2, 3],
      ],
    ];
  }

  /**
   * Tests media_migration_null_coalesce with default value.
   *
   * @covers ::transform
   */
  public function testTransformWithDefault() {
    $plugin = new MediaMigrationNullCoalesce(['default_value' => 'default'], 'media_migration_null_coalesce', []);
    $result = $plugin->transform([NULL, NULL, 'Test', 'Test 2'], $this->migrateExecutable, $this->row, 'destinationproperty');
    $this->assertSame('Test', $result);

    $this->assertSame('default', $plugin->transform([NULL, NULL], $this->migrateExecutable, $this->row, 'destinationproperty'));
  }

}

<?php

namespace Drupal\Tests\media_migration\Kernel\Plugin\migrate\process;

use Drupal\media_migration\Plugin\migrate\process\MediaWysiwygFilter;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate\Row;
use Drupal\Tests\media_migration\Kernel\Migrate\MediaMigrationTestBase;

/**
 * Tests the MediaWysiwygFilter migration process plugin.
 *
 * @group media_migration
 */
class MediaWysiwygFilterTest extends MediaMigrationTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = NULL;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'file',
    'filter',
    'image',
    'media',
    'media_migration',
    'migrate',
    'migrate_drupal',
    'node',
    'smart_sql_idmap',
    'system',
    'user',
  ];

  /**
   * Test the MediaWysiwygFilter plugin's transform.
   *
   * @dataProvider providerTransformTest
   */
  public function testMediaWysiwygFilterTransform(string $input_value, string $expected_value) {
    $row = new Row(['nid' => '1'], ['nid' => 'nid']);
    $migration_plugin_manager = $this->container->get('plugin.manager.migration');
    assert($migration_plugin_manager instanceof MigrationPluginManagerInterface);
    $migration = $migration_plugin_manager->createInstance('d7_node:article');
    $executable = $this->prophesize(MigrateExecutableInterface::class)->reveal();
    $media_migration_uuid_oracle = $this->container->get('media_migration.media_uuid_oracle');

    $plugin = new MediaWysiwygFilter([], 'media_wysiwyg_filter', [], $migration, NULL, $migration_plugin_manager, $media_migration_uuid_oracle);

    $this->assertEquals(['value' => $expected_value], $plugin->transform(['value' => $input_value], $executable, $row, 'destination_property'));
  }

  /**
   * Data provider for ::testMediaWysiwygFilterTransform().
   */
  public function providerTransformTest() {
    return [
      'Plain text' => [
        'input' => 'Lorem ipsum dolor sit amet.',
        'expected' => 'Lorem ipsum dolor sit amet.',
      ],
      'Plain text with non-media token' => [
        'input' => 'Blah
[[nid:1]]
Blah',
        'expected' => 'Blah
[[nid:1]]
Blah',
      ],
      'Plain text with non-media JSON' => [
        'input' => 'Blah
[[{"nid":"1"}]]
Blah',
        'expected' => 'Blah
[[{"nid":"1"}]]
Blah',
      ],
      'HTML text with non-media JSON' => [
        'input' => '<p>Blah</p>
[[{"nid":"1"}]]
<p><strong>Blah</strong></p>',
        'expected' => '<p>Blah</p>
[[{"nid":"1"}]]
<p><strong>Blah</strong></p>',
      ],
      'Plain text with non-media and media JSON tokens' => [
        'input' => '[[{"nid":"1"}]]
Lorem ipsum dolor sit amet.
[[{"fid":"3","view_mode":"default","type":"media"}]]
Nam finibus elit nec ipsum feugiat convallis.
[[{"fid":"1","view_mode":"wysiwyg","type":"media"}]]
Aliquam tellus nisi.
[[nid:1]]',
        'expected' => '[[{"nid":"1"}]]
Lorem ipsum dolor sit amet.
<drupal-entity data-embed-button="media" data-entity-type="media" data-entity-id="3" data-entity-embed-display="view_mode:media.default"></drupal-entity>
Nam finibus elit nec ipsum feugiat convallis.
<drupal-entity data-embed-button="media" data-entity-type="media" data-entity-id="1" data-entity-embed-display="view_mode:media.wysiwyg"></drupal-entity>
Aliquam tellus nisi.
[[nid:1]]',
      ],
      'HTML text with non-media and media JSON tokens' => [
        'input' => '<p class="lead">[[{"foo":"bar"}]]</p>
<p>Lorem ipsum dolor sit amet.</p>
[[{"fid":"453","view_mode":"default","type":"media"}]]
<p>Nam finibus elit nec ipsum feugiat convallis.</p>
<p>[[{"fid":"154","view_mode":"wysiwyg","type":"media"}]]</p>
<ul>
  <li>Aliquam tellus nisi.</li>
</ul>
[[tid:15]]
<p></p>',
        'expected' => '<p class="lead">[[{"foo":"bar"}]]</p>
<p>Lorem ipsum dolor sit amet.</p>
<drupal-entity data-embed-button="media" data-entity-type="media" data-entity-id="453" data-entity-embed-display="view_mode:media.default"></drupal-entity>
<p>Nam finibus elit nec ipsum feugiat convallis.</p>
<p><drupal-entity data-embed-button="media" data-entity-type="media" data-entity-id="154" data-entity-embed-display="view_mode:media.wysiwyg"></drupal-entity></p>
<ul>
  <li>Aliquam tellus nisi.</li>
</ul>
[[tid:15]]
<p></p>',
      ],
      'Text with invalid (?) JSON' => [
        'input' => '<p>Foo?</p>
[[{ "fid":"123456","view_mode":"default","type":"media","attributes":{ "class":"css-class1
css-class2 css-class3" ,"alt":"Overridden alt attribute for embed" ,"title":"Overridden title attribute for embed" },
"fields":{ "format":"default" ,"alt":"Overridden alt attribute for embed","field_file_image_alt_text[und][0][value]":"Media alt attribute" ,"title":"Overridden title attribute for embed","field_file_image_title_text[und][0][value]":"Media title attribute"} }]]
<p>Bar baz!</p>',
        'expected' => '<p>Foo?</p>
<drupal-entity data-embed-button="media" data-entity-type="media" data-entity-id="123456" data-entity-embed-display="view_mode:media.default" alt="Overridden alt attribute for embed" title="Overridden title attribute for embed"></drupal-entity>
<p>Bar baz!</p>',
      ],
    ];
  }

}

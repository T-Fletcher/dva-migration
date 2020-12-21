<?php

namespace Drupal\Tests\media_migration\Kernel\Migrate;

use Drupal\Tests\media_migration\Traits\MediaMigrationAssertionsForMediaSourceTrait;

/**
 * Tests media migration.
 *
 * @group media_migration
 */
class MediaMigrationTest extends MediaMigrationTestBase {

  use MediaMigrationAssertionsForMediaSourceTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'comment',
    'datetime',
    'datetime_range',
    'editor',
    'embed',
    'entity_embed',
    'field',
    'file',
    'filter',
    'image',
    'link',
    'media',
    'media_migration',
    'media_migration_test_oembed',
    'menu_ui',
    'migrate',
    'migrate_drupal',
    'migrate_plus',
    'node',
    'options',
    'smart_sql_idmap',
    'system',
    'taxonomy',
    'telephone',
    'text',
    'user',
  ];

  /**
   * Tests the migration of media entities.
   *
   * @dataProvider providerTestMediaMigration
   */
  public function testMediaMigration(string $destination_token, string $reference_method, bool $classic_node_migration, array $expected_node1_embed_attributes, bool $preexisting_media_types) {
    if ($preexisting_media_types) {
      $this->createStandardMediaTypes();
    }
    $this->setEmbedTokenDestinationFilterPlugin($destination_token);
    $this->setEmbedMediaReferenceMethod($reference_method);
    $this->setClassicNodeMigration($classic_node_migration);

    // Execute the media migrations.
    $this->executeMediaMigrations($classic_node_migration);

    // Check configurations.
    $this->assertArticleImageFieldsAllowedTypes();
    $this->assertArticleMediaFieldsAllowedTypes();

    // Check the migrated media entities.
    $this->assertMedia1FieldValues();
    $this->assertMedia2FieldValues();
    $this->assertMedia3FieldValues();
    $this->assertMedia4FieldValues();
    $this->assertMedia5FieldValues();
    $this->assertMedia6FieldValues();
    $this->assertMedia7FieldValues();
    $this->assertMedia8FieldValues();
    $this->assertMedia9FieldValues();
    $this->assertMedia10FieldValues();
    $this->assertMedia11FieldValues();
    $this->assertMedia12FieldValues();

    $this->assertNode1FieldValues($expected_node1_embed_attributes);
  }

  /**
   * Data provider for ::testMediaTokenToMediaEmbedTransform().
   *
   * @return array
   *   The test cases.
   */
  public function providerTestMediaMigration() {
    $default_attributes = [
      'data-entity-type' => 'media',
      'alt' => 'Different alternative text about blue.png in the test article',
      'title' => 'Different title copy for blue.png in the test article',
      'data-align' => 'center',
    ];

    $test_cases = [
      // ID reference method. This should be neutral for media_embed token
      // transform destination.
      'Entity embed destination, ID reference method, classic node migration, preexisting media types' => [
        'Destination filter' => 'entity_embed',
        'Reference method' => 'id',
        'Classic node migration' => TRUE,
        'expected_node1_embed_html_attributes' => [
          0 => [
            'data-entity-id' => '1',
            'data-embed-button' => 'media',
            'data-entity-embed-display' => 'view_mode:media.wysiwyg',
          ] + $default_attributes,
        ],
        'Preexisting media types' => TRUE,
      ],
      'Media embed destination, ID reference method, classic node migration, preexisting media types' => [
        'Destination filter' => 'media_embed',
        'Reference method' => 'id',
        'Classic node migration' => TRUE,
        'expected_node1_embed_html_attributes' => [
          0 => [
            'data-entity-uuid' => TRUE,
            'data-view-mode' => 'wysiwyg',
          ] + $default_attributes,
        ],
        'Preexisting media types' => TRUE,
      ],
      'Entity embed destination, ID reference method, complete node migration, preexisting media types' => [
        'Destination filter' => 'entity_embed',
        'Reference method' => 'id',
        'Classic node migration' => FALSE,
        'expected_node1_embed_html_attributes' => [
          0 => [
            'data-entity-id' => '1',
            'data-embed-button' => 'media',
            'data-entity-embed-display' => 'view_mode:media.wysiwyg',
          ] + $default_attributes,
        ],
        'Preexisting media types' => TRUE,
      ],
      'Media embed destination, ID reference method, complete node migration, preexisting media types' => [
        'Destination filter' => 'media_embed',
        'Reference method' => 'id',
        'Classic node migration' => FALSE,
        'expected_node1_embed_html_attributes' => [
          0 => [
            'data-entity-uuid' => TRUE,
            'data-view-mode' => 'wysiwyg',
          ] + $default_attributes,
        ],
        'Preexisting media types' => TRUE,
      ],
      // UUID reference method.
      'Entity embed destination, UUID reference method, classic node migration, preexisting media types' => [
        'Destination filter' => 'entity_embed',
        'Reference method' => 'uuid',
        'Classic node migration' => TRUE,
        'expected_node1_embed_html_attributes' => [
          0 => [
            'data-entity-uuid' => TRUE,
            'data-embed-button' => 'media',
            'data-entity-embed-display' => 'view_mode:media.wysiwyg',
          ] + $default_attributes,
        ],
        'Preexisting media types' => TRUE,
      ],
      'Media embed destination, UUID reference method, classic node migration, preexisting media types' => [
        'Destination filter' => 'media_embed',
        'Reference method' => 'uuid',
        'Classic node migration' => TRUE,
        'expected_node1_embed_html_attributes' => [
          0 => [
            'data-entity-uuid' => TRUE,
            'data-view-mode' => 'wysiwyg',
          ] + $default_attributes,
        ],
        'Preexisting media types' => TRUE,
      ],
      'Entity embed destination, UUID reference method, complete node migration, preexisting media types' => [
        'Destination filter' => 'entity_embed',
        'Reference method' => 'uuid',
        'Classic node migration' => FALSE,
        'expected_node1_embed_html_attributes' => [
          0 => [
            'data-entity-uuid' => TRUE,
            'data-embed-button' => 'media',
            'data-entity-embed-display' => 'view_mode:media.wysiwyg',
          ] + $default_attributes,
        ],
        'Preexisting media types' => TRUE,
      ],
      'Media embed destination, UUID reference method, complete node migration, preexisting media types' => [
        'Destination filter' => 'media_embed',
        'Reference method' => 'uuid',
        'Classic node migration' => FALSE,
        'expected_node1_embed_html_attributes' => [
          0 => [
            'data-entity-uuid' => TRUE,
            'data-view-mode' => 'wysiwyg',
          ] + $default_attributes,
        ],
        'Preexisting media types' => TRUE,
      ],
    ];

    // Add 'no initial media types' test cases.
    $test_cases_without_media_types = [];
    foreach ($test_cases as $test_case_label => $test_case) {
      $without_media_label = preg_replace('/preexisting media types$/', 'no media types', $test_case_label);
      $test_case['Preexisting media types'] = FALSE;
      $test_cases_without_media_types[$without_media_label] = $test_case;
    }

    $test_cases += $test_cases_without_media_types;

    // Drupal 8.8.x only has 'classic' node migrations.
    // @see https://www.drupal.org/node/3105503
    if (version_compare(\Drupal::VERSION, '8.9', '<')) {
      $test_cases = array_filter($test_cases, function ($test_case) {
        return $test_case['Classic node migration'];
      });
    }

    return $test_cases;
  }

}

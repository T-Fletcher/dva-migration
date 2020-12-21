<?php

namespace Drupal\Tests\media_migration\Traits;

use Drupal\field\FieldConfigInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;

/**
 * Trait for non-media source to media migration tests.
 */
trait MediaMigrationAssertionsForNonMediaSourceTrait {

  use MediaMigrationAssertionsBaseTrait;

  /**
   * Asserts the migration result from file ID 1 to media 1.
   */
  protected function assertNonMediaToMedia1FieldValues() {
    $media = $this->container->get('entity_type.manager')->getStorage('media')->load(1);
    assert($media instanceof MediaInterface);

    $this->assertEquals([
      'mid' => [['value' => '1']],
      'bundle' => [['target_id' => 'image']],
      'name' => [['value' => 'blue.png']],
      'uid' => [['target_id' => '2']],
      'status' => [['value' => '1']],
      'created' => [['value' => '1594368799']],
      'changed' => [['value' => '1594368799']],
      'field_media_image' => [
        [
          'target_id' => '1',
          'alt' => 'Alt for blue.png',
          'title' => NULL,
          'width' => '1280',
          'height' => '720',
        ],
      ],
    ], $this->getImportantEntityProperties($media));

    // Check the media field.
    $media_field = $this->getReferencedEntities($media, 'field_media_image', 1);
    assert($media_field[0] instanceof FileInterface);
    // The referenced file should exist.
    $this->assertTrue(file_exists($media_field[0]->getFileUri()));
  }

  /**
   * Asserts the migration result from file ID 2 to media 2.
   */
  protected function assertNonMediaToMedia2FieldValues() {
    $media = $this->container->get('entity_type.manager')->getStorage('media')->load(2);
    assert($media instanceof MediaInterface);

    $this->assertEquals([
      'mid' => [['value' => '2']],
      'bundle' => [['target_id' => 'image']],
      'name' => [['value' => 'green.jpg']],
      'uid' => [['target_id' => '2']],
      'status' => [['value' => '1']],
      'created' => [['value' => '1594368799']],
      'changed' => [['value' => '1594368799']],
      'field_media_image' => [
        [
          'target_id' => '2',
          'alt' => 'Alt for green.jpg',
          'title' => NULL,
          'width' => '720',
          'height' => '960',
        ],
      ],
    ], $this->getImportantEntityProperties($media));

    // Check the media field.
    $media_field = $this->getReferencedEntities($media, 'field_media_image', 1);
    assert($media_field[0] instanceof FileInterface);
    // The referenced file should exist.
    $this->assertTrue(file_exists($media_field[0]->getFileUri()));
  }

  /**
   * Asserts the migration result from file ID 3 to media 3.
   */
  protected function assertNonMediaToMedia3FieldValues() {
    $media = $this->container->get('entity_type.manager')->getStorage('media')->load(3);
    assert($media instanceof MediaInterface);

    $this->assertEquals([
      'mid' => [['value' => '3']],
      'bundle' => [['target_id' => 'image']],
      'name' => [['value' => 'red.jpeg']],
      'uid' => [['target_id' => '2']],
      'status' => [['value' => '1']],
      'created' => [['value' => '1594368881']],
      'changed' => [['value' => '1594368881']],
      'field_media_image' => [
        [
          'target_id' => '3',
          'alt' => 'Alt for red.jpeg',
          'title' => NULL,
          'width' => '1280',
          'height' => '720',
        ],
      ],
    ], $this->getImportantEntityProperties($media));

    // Check the media field.
    $media_field = $this->getReferencedEntities($media, 'field_media_image', 1);
    assert($media_field[0] instanceof FileInterface);
    // The referenced file should exist.
    $this->assertTrue(file_exists($media_field[0]->getFileUri()));
  }

  /**
   * Asserts the migration result from file ID 6 to media 6.
   */
  protected function assertNonMediaToMedia6FieldValues() {
    $media = $this->container->get('entity_type.manager')->getStorage('media')->load(6);
    assert($media instanceof MediaInterface);

    $this->assertEquals([
      'mid' => [['value' => '6']],
      'bundle' => [['target_id' => 'document']],
      'name' => [['value' => 'LICENSE.txt']],
      'uid' => [['target_id' => '2']],
      'status' => [['value' => '1']],
      'created' => [['value' => '1594368799']],
      'changed' => [['value' => '1594368799']],
      'field_media_document' => [
        [
          'target_id' => '6',
          'display' => '1',
          'description' => NULL,
        ],
      ],
    ], $this->getImportantEntityProperties($media));

    // Check the media field.
    $media_field = $this->getReferencedEntities($media, 'field_media_document', 1);
    assert($media_field[0] instanceof FileInterface);
    // The referenced file should exist.
    $this->assertTrue(file_exists($media_field[0]->getFileUri()));
  }

  /**
   * Asserts the migration result from file ID 7 to media 7.
   */
  protected function assertNonMediaToMedia7FieldValues() {
    $media = $this->container->get('entity_type.manager')->getStorage('media')->load(7);
    assert($media instanceof MediaInterface);

    $this->assertEquals([
      'mid' => [['value' => '7']],
      'bundle' => [['target_id' => 'image']],
      'name' => [['value' => 'yellow.jpg']],
      'uid' => [['target_id' => '2']],
      'status' => [['value' => '1']],
      'created' => [['value' => '1594368799']],
      'changed' => [['value' => '1594368799']],
      'field_media_image' => [
        [
          'target_id' => '7',
          'alt' => 'Alt for yellow.jpg',
          'title' => NULL,
          'width' => '640',
          'height' => '400',
        ],
      ],
    ], $this->getImportantEntityProperties($media));

    // Check the media field.
    $media_field = $this->getReferencedEntities($media, 'field_media_image', 1);
    assert($media_field[0] instanceof FileInterface);
    // The referenced file should exist.
    $this->assertTrue(file_exists($media_field[0]->getFileUri()));
  }

  /**
   * Asserts the migration result from file ID 8 to media 8.
   */
  protected function assertNonMediaToMedia8FieldValues() {
    $media = $this->container->get('entity_type.manager')->getStorage('media')->load(8);
    assert($media instanceof MediaInterface);

    $this->assertEquals([
      'mid' => [['value' => '8']],
      'bundle' => [['target_id' => 'video']],
      'name' => [['value' => 'video.webm']],
      'uid' => [['target_id' => '1']],
      'status' => [['value' => '1']],
      'created' => [['value' => '1597409263']],
      'changed' => [['value' => '1597409263']],
      'field_media_video_file' => [
        [
          'target_id' => '8',
          'display' => '1',
          'description' => NULL,
        ],
      ],
    ], $this->getImportantEntityProperties($media));

    // Check the media field.
    $media_field = $this->getReferencedEntities($media, 'field_media_video_file', 1);
    assert($media_field[0] instanceof FileInterface);
    // The referenced file should exist.
    $this->assertTrue(file_exists($media_field[0]->getFileUri()));
  }

  /**
   * Asserts the migration result from file ID 9 to media 9.
   */
  protected function assertNonMediaToMedia9FieldValues() {
    $media = $this->container->get('entity_type.manager')->getStorage('media')->load(9);
    assert($media instanceof MediaInterface);

    $this->assertEquals([
      'mid' => [['value' => '9']],
      'bundle' => [['target_id' => 'video']],
      'name' => [['value' => 'video.mp4']],
      'uid' => [['target_id' => '1']],
      'status' => [['value' => '1']],
      'created' => [['value' => '1597409263']],
      'changed' => [['value' => '1597409263']],
      'field_media_video_file' => [
        [
          'target_id' => '9',
          'display' => '1',
          'description' => 'Tiny video about kittens',
        ],
      ],
    ], $this->getImportantEntityProperties($media));

    // Check the media field.
    $media_field = $this->getReferencedEntities($media, 'field_media_video_file', 1);
    assert($media_field[0] instanceof FileInterface);
    // The referenced file should exist.
    $this->assertTrue(file_exists($media_field[0]->getFileUri()));
  }

  /**
   * Asserts the migration result from file ID 10 to media 10.
   */
  protected function assertNonMediaToMedia10FieldValues() {
    $media = $this->container->get('entity_type.manager')->getStorage('media')->load(10);
    assert($media instanceof MediaInterface);

    $this->assertEquals([
      'mid' => [['value' => '10']],
      'bundle' => [['target_id' => 'image']],
      'name' => [['value' => 'yellow.webp']],
      'uid' => [['target_id' => '2']],
      'status' => [['value' => '1']],
      'created' => [['value' => '1594368881']],
      'changed' => [['value' => '1594368881']],
      'field_media_image' => [
        [
          'target_id' => '10',
          'alt' => NULL,
          'title' => NULL,
          'width' => NULL,
          'height' => NULL,
        ],
      ],
    ], $this->getImportantEntityProperties($media));

    // Check the media field.
    $media_field = $this->getReferencedEntities($media, 'field_media_image', 1);
    assert($media_field[0] instanceof FileInterface);
    // The referenced file should exist.
    $this->assertTrue(file_exists($media_field[0]->getFileUri()));
  }

  /**
   * Asserts the migration result from file ID 11 to media 11.
   */
  protected function assertNonMediaToMedia11FieldValues() {
    $media = $this->container->get('entity_type.manager')->getStorage('media')->load(11);
    assert($media instanceof MediaInterface);

    $this->assertEquals([
      'mid' => [['value' => '11']],
      'bundle' => [['target_id' => 'audio']],
      'name' => [['value' => 'audio.m4a']],
      'uid' => [['target_id' => '1']],
      'status' => [['value' => '1']],
      'created' => [['value' => '1597409263']],
      'changed' => [['value' => '1597409263']],
      'field_media_audio_file' => [
        [
          'target_id' => '11',
          'display' => '1',
          'description' => NULL,
        ],
      ],
    ], $this->getImportantEntityProperties($media));

    // Check the media field.
    $media_field = $this->getReferencedEntities($media, 'field_media_audio_file', 1);
    assert($media_field[0] instanceof FileInterface);
    // The referenced file should exist.
    $this->assertTrue(file_exists($media_field[0]->getFileUri()));
  }

  /**
   * Asserts the migration result from file ID 12 to media 12.
   */
  protected function assertNonMediaToMedia12FieldValues() {
    $media = $this->container->get('entity_type.manager')->getStorage('media')->load(12);
    assert($media instanceof MediaInterface);

    $this->assertEquals([
      'mid' => [['value' => '12']],
      'bundle' => [['target_id' => 'document']],
      'name' => [['value' => 'document.odt']],
      'uid' => [['target_id' => '2']],
      'status' => [['value' => '1']],
      'created' => [['value' => '1594368799']],
      'changed' => [['value' => '1594368799']],
      'field_media_document' => [
        [
          'target_id' => '12',
          'display' => '1',
          'description' => NULL,
        ],
      ],
    ], $this->getImportantEntityProperties($media));

    // Check the media field.
    $media_field = $this->getReferencedEntities($media, 'field_media_document', 1);
    assert($media_field[0] instanceof FileInterface);
    // The referenced file should exist.
    $this->assertTrue(file_exists($media_field[0]->getFileUri()));
  }

  /**
   * Assertions of node 1.
   */
  protected function assertNonMediaToMediaNode1FieldValues() {
    $node = $this->container->get('entity_type.manager')->getStorage('node')->load(1);
    assert($node instanceof NodeInterface);

    $this->assertEquals([
      'nid' => [['value' => 1]],
      'type' => [['target_id' => 'article']],
      'status' => [['value' => 1]],
      'uid' => [['target_id' => 2]],
      'title' => [['value' => 'Article with images and files']],
      'created' => [['value' => 1594368799]],
      'changed' => [['value' => 1594368881]],
      'promote' => [['value' => 1]],
      'sticky' => [['value' => 0]],
      'body' => [
        [
          'value' => '<p>Nulla tempor, nunc eu mollis finibus, risus nunc venenatis nulla, in ullamcorper nisl nulla et nisi. Cras vel urna risus. Cras in sem a nulla aliquet pretium.</p><p>Quisque tortor libero, vulputate sit amet augue dictum, posuere bibendum lectus. Nunc fermentum justo odio, ut fermentum purus fermentum a. Aenean congue fringilla arcu sit amet pellentesque.</p>',
          'summary' => '',
          'format' => 'filtered_html',
        ],
      ],
      'field_file' => [['target_id' => '6']],
      'field_file_multi' => [
        ['target_id' => '12'],
        ['target_id' => '10'],
      ],
      'field_image' => [['target_id' => '1']],
      'field_image_multi' => [
        ['target_id' => '2'],
        ['target_id' => '7'],
        ['target_id' => '3'],
      ],
    ], $this->getImportantEntityProperties($node));

    // Test that the image and file fields are referencing media entities.
    $media_fields = [
      'field_file' => 1,
      'field_file_multi' => 2,
      'field_image' => 1,
      'field_image_multi' => 3,
    ];
    foreach ($media_fields as $field_name => $expected_count) {
      $referred_entities = $this->getReferencedEntities($node, $field_name, $expected_count);
      assert($referred_entities[0] instanceof MediaInterface);
    }
  }

  /**
   * Assertions of node 2.
   */
  protected function assertNonMediaToMediaNode2FieldValues() {
    $node = $this->container->get('entity_type.manager')->getStorage('node')->load(2);
    assert($node instanceof NodeInterface);

    $this->assertEquals([
      'nid' => [['value' => 2]],
      'type' => [['target_id' => 'article']],
      'status' => [['value' => 1]],
      'uid' => [['target_id' => 1]],
      'title' => [['value' => 'Another article with audio and video files']],
      'created' => [['value' => 1597409263]],
      'changed' => [['value' => 1597409263]],
      'promote' => [['value' => 1]],
      'sticky' => [['value' => 0]],
      'body' => [
        [
          'value' => '<p>Aliquam efficitur fermentum nisi ut sagittis. Nullam pharetra nisi venenatis sodales tincidunt. Mauris sit amet metus arcu.</p>',
          'summary' => '',
          'format' => 'filtered_html',
        ],
      ],
      'field_file' => [['target_id' => '9']],
      'field_file_multi' => [
        ['target_id' => '8'],
        ['target_id' => '11'],
      ],
      'field_image' => [],
      'field_image_multi' => [],
    ], $this->getImportantEntityProperties($node));

    // Test that the image and file fields are referencing media entities.
    $media_fields = [
      'field_file' => 1,
      'field_file_multi' => 2,
      'field_image' => 0,
      'field_image_multi' => 0,
    ];
    foreach ($media_fields as $field_name => $expected_count) {
      $referred_entities = $this->getReferencedEntities($node, $field_name, $expected_count);
      if ($expected_count) {
        assert($referred_entities[0] instanceof MediaInterface);
      }
    }
  }

  /**
   * Checks the properties of the image media type's source field config.
   */
  protected function assertNonMediaToMediaImageMediaBundleSourceFieldProperties() {
    $field_config = $this->container->get('entity_type.manager')
      ->getStorage('field_config')
      ->load('media.image.field_media_image');
    assert($field_config instanceof FieldConfigInterface);

    $this->assertEquals([
      'id' => 'media.image.field_media_image',
      'status' => TRUE,
      'field_name' => 'field_media_image',
      'entity_type' => 'media',
      'bundle' => 'image',
      'label' => 'Image',
      'description' => '',
      'required' => TRUE,
      'translatable' => TRUE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [
        'alt_field' => TRUE,
        'alt_field_required' => TRUE,
        'title_field' => FALSE,
        'title_field_required' => FALSE,
        'max_resolution' => '',
        'min_resolution' => '',
        'default_image' => [
          'uuid' => NULL,
          'alt' => '',
          'title' => '',
          'width' => NULL,
          'height' => NULL,
        ],
        'file_directory' => '[date:custom:Y]-[date:custom:m]',
        'file_extensions' => 'png gif jpg jpeg webp',
        'max_filesize' => '',
        'handler' => 'default:file',
        'handler_settings' => [],
      ],
      'field_type' => 'image',
    ], $this->getImportantEntityProperties($field_config));
  }

  /**
   * Checks the properties of the document media type's source field config.
   */
  protected function assertNonMediaToMediaDocumentMediaBundleSourceFieldProperties() {
    $field_config = $this->container->get('entity_type.manager')
      ->getStorage('field_config')
      ->load('media.document.field_media_document');
    assert($field_config instanceof FieldConfigInterface);

    $this->assertEquals([
      'id' => 'media.document.field_media_document',
      'status' => TRUE,
      'field_name' => 'field_media_document',
      'entity_type' => 'media',
      'bundle' => 'document',
      'label' => 'Document',
      'description' => '',
      'required' => TRUE,
      'translatable' => TRUE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [
        'description_field' => TRUE,
        'file_directory' => '[date:custom:Y]-[date:custom:m]',
        'file_extensions' => 'txt doc docx pdf odt',
        'max_filesize' => '',
        'handler' => 'default:file',
        'handler_settings' => [],
      ],
      'field_type' => 'file',
    ], $this->getImportantEntityProperties($field_config));
  }

  /**
   * Checks the properties of the audio media type's source field config.
   */
  protected function assertNonMediaToMediaAudioMediaBundleSourceFieldProperties() {
    $field_config = $this->container->get('entity_type.manager')
      ->getStorage('field_config')
      ->load('media.audio.field_media_audio_file');
    assert($field_config instanceof FieldConfigInterface);

    $this->assertEquals([
      'id' => 'media.audio.field_media_audio_file',
      'status' => TRUE,
      'field_name' => 'field_media_audio_file',
      'entity_type' => 'media',
      'bundle' => 'audio',
      'label' => 'Audio file',
      'description' => '',
      'required' => TRUE,
      'translatable' => TRUE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [
        'description_field' => TRUE,
        'file_directory' => '[date:custom:Y]-[date:custom:m]',
        'file_extensions' => 'mp3 wav aac m4a',
        'max_filesize' => '',
        'handler' => 'default:file',
        'handler_settings' => [],
      ],
      'field_type' => 'file',
    ], $this->getImportantEntityProperties($field_config));
  }

  /**
   * Checks the properties of the audio media type's source field config.
   */
  protected function assertNonMediaToMediaVideoMediaBundleSourceFieldProperties() {
    $field_config = $this->container->get('entity_type.manager')
      ->getStorage('field_config')
      ->load('media.video.field_media_video_file');
    assert($field_config instanceof FieldConfigInterface);

    $this->assertEquals([
      'id' => 'media.video.field_media_video_file',
      'status' => TRUE,
      'field_name' => 'field_media_video_file',
      'entity_type' => 'media',
      'bundle' => 'video',
      'label' => 'Video file',
      'description' => '',
      'required' => TRUE,
      'translatable' => TRUE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [
        'description_field' => TRUE,
        'file_directory' => '[date:custom:Y]-[date:custom:m]',
        'file_extensions' => 'mp4 webm',
        'max_filesize' => '',
        'handler' => 'default:file',
        'handler_settings' => [],
      ],
      'field_type' => 'file',
    ], $this->getImportantEntityProperties($field_config));
  }

}

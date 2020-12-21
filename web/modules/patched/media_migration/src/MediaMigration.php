<?php

namespace Drupal\media_migration;

use Drupal\Core\Site\Settings;

/**
 * Media Migration configuration and helpers.
 */
final class MediaMigration {

  /**
   * Migration tag for every media related migrations.
   *
   * @var string
   */
  const MIGRATION_TAG_MAIN = 'Media Migration';

  /**
   * Migration tag for media configuration migrations.
   *
   * @var string
   */
  const MIGRATION_TAG_CONFIG = 'Media Configuration';

  /**
   * Migration tag for media entity migrations.
   *
   * @var string
   */
  const MIGRATION_TAG_CONTENT = 'Media Entity';

  /**
   * The name of the media UUID prophecy table.
   *
   * @var string
   */
  const MEDIA_UUID_PROPHECY_TABLE = 'media_migration_media_entity_uuid_prophecy';

  /**
   * The name of the media source ID column.
   *
   * @var string
   */
  const MEDIA_UUID_PROPHECY_SOURCEID_COL = 'source_id';

  /**
   * The name of the column that contains the destination UUID.
   *
   * @var string
   */
  const MEDIA_UUID_PROPHECY_UUID_COL = 'destination_uuid';

  /**
   * The name of the setting of how embedded media should be referred.
   *
   * @var string
   */
  const MEDIA_REFERENCE_METHOD_SETTINGS = 'media_migration_embed_media_reference_method';

  /**
   * The ID embedded media reference method.
   *
   * @var string
   */
  const EMBED_MEDIA_REFERENCE_METHOD_ID = 'id';

  /**
   * The UUID embedded media reference method.
   *
   * @var string
   */
  const EMBED_MEDIA_REFERENCE_METHOD_UUID = 'uuid';

  /**
   * Default embedded media reference method.
   *
   * @var string
   */
  const EMBED_MEDIA_REFERENCE_METHOD_DEFAULT = self::EMBED_MEDIA_REFERENCE_METHOD_ID;

  /**
   * Valid embedded media reference methods.
   *
   * @var string[]
   */
  const VALID_EMBED_MEDIA_REFERENCE_METHODS = [
    self::EMBED_MEDIA_REFERENCE_METHOD_ID,
    self::EMBED_MEDIA_REFERENCE_METHOD_UUID,
  ];

  /**
   * The name of embed code transformation destination filter plugin setting.
   *
   * @var string
   */
  const MEDIA_TOKEN_DESTINATION_FILTER_SETTINGS = 'media_migration_embed_token_transform_destination_filter_plugin';

  /**
   * Entity embed destination filter.
   *
   * @var string
   */
  const MEDIA_TOKEN_DESTINATION_FILTER_ENTITY_EMBED = 'entity_embed';

  /**
   * Media embed destination filter.
   *
   * @var string
   */
  const MEDIA_TOKEN_DESTINATION_FILTER_MEDIA_EMBED = 'media_embed';

  /**
   * Default embed token destination filter plugin ID.
   *
   * Actually, MEDIA_TOKEN_DESTINATION_FILTER_MEDIA_EMBED would be the correct
   * default, but doing that would cause a BC break in this module.
   *
   * @var string
   */
  const MEDIA_TOKEN_DESTINATION_FILTER_DEFAULT = self::MEDIA_TOKEN_DESTINATION_FILTER_ENTITY_EMBED;

  /**
   * The required modules of the valid destination filter plugins.
   *
   * @var array[]
   */
  const MEDIA_TOKEN_DESTINATION_FILTER_REQUIREMENTS = [
    self::MEDIA_TOKEN_DESTINATION_FILTER_ENTITY_EMBED => [
      'entity_embed',
    ],
    self::MEDIA_TOKEN_DESTINATION_FILTER_MEDIA_EMBED => [
      'media',
    ],
  ];

  /**
   * Sets the method of the embedded media reference.
   *
   * @return string
   *   The reference method. This might be 'id', or 'uuid'.
   */
  public static function getEmbedMediaReferenceMethod() {
    $value_from_settings = Settings::get(self::MEDIA_REFERENCE_METHOD_SETTINGS, self::EMBED_MEDIA_REFERENCE_METHOD_DEFAULT);

    if (version_compare(\Drupal::VERSION, '8.7', '>') && self::getEmbedTokenDestinationFilterPlugin() === self::MEDIA_TOKEN_DESTINATION_FILTER_MEDIA_EMBED) {
      return self::EMBED_MEDIA_REFERENCE_METHOD_UUID;
    }

    return in_array($value_from_settings, self::VALID_EMBED_MEDIA_REFERENCE_METHODS, TRUE) ?
      $value_from_settings :
      self::EMBED_MEDIA_REFERENCE_METHOD_DEFAULT;
  }

  /**
   * Returns the embed media token transform's destination filter_plugin.
   *
   * @return string
   *   The embed media token transform's destination filter_plugin from
   *   settings.php.
   */
  public static function getEmbedTokenDestinationFilterPlugin() {
    // The media embed filter exists only from Drupal 8.8+.
    if (version_compare(\Drupal::VERSION, '8.7', '>')) {
      return Settings::get(self::MEDIA_TOKEN_DESTINATION_FILTER_SETTINGS, self::MEDIA_TOKEN_DESTINATION_FILTER_DEFAULT);
    }

    return self::MEDIA_TOKEN_DESTINATION_FILTER_DEFAULT;
  }

  /**
   * Whether the transform's destination filter_plugin is valid or not.
   *
   * @param string|null $filter_plugin_id
   *   The filter plugin ID to check.
   *
   * @return bool
   *   TRUE if the plugin is valid, false if not.
   */
  public static function embedTokenDestinationFilterPluginIsValid($filter_plugin_id) {
    $valid_filter_plugin_ids = array_keys(self::MEDIA_TOKEN_DESTINATION_FILTER_REQUIREMENTS);
    return in_array($filter_plugin_id, $valid_filter_plugin_ids, TRUE);
  }

}

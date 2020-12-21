<?php

namespace Drupal\media_migration;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * MediaWysiwyg plugin manager.
 */
class MediaWysiwygPluginManager extends DefaultPluginManager {

  /**
   * Constructs MediaWysiwygPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/MediaWysiwyg',
      $namespaces,
      $module_handler,
      'Drupal\media_migration\MediaWysiwygInterface',
      'Drupal\media_migration\Annotation\MediaWysiwyg'
    );
    $this->alterInfo('media_wysiwyg_info');
    $this->setCacheBackend($cache_backend, 'media_wysiwyg_plugins');
  }

}

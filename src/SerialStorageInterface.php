<?php

namespace Drupal\serial;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines an interface for node serial storage classes.
 * @todo review extends ContentEntityStorageInterface
 * @todo document
 * @todo type casting, instance contains the entity type + bundle
 */
interface SerialStorageInterface {
  public function createStorage($field, $instance);
  public function dropStorage($field, $instance);
  public function initOldEntries($field, $instance);
  public function renameStorage($entityType, $bundleOld, $bundleNew);
  public function getFieldStorageName($field, $instance);
}

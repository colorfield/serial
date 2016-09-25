<?php

namespace Drupal\serial;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines an interface for node serial storage classes.
 * @todo review extends ContentEntityStorageInterface
 * @todo document
 * @todo type casting
 */
interface SerialStorageInterface {
  public function createStorage($field, $entity);
  public function dropStorage($field, $entity);
  public function initOldEntries($field, $entity);
  public function renameStorage($entityType, $bundleOld, $bundleNew);
  public function getFieldStorageName($field, $entity);
}

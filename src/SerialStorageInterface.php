<?php

namespace Drupal\serial;

// use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines an interface for node serial storage classes.
 * @todo review extends ContentEntityStorageInterface
 * @todo document
 * @todo type casting
 */
interface SerialStorageInterface {
  const SERIAL_FIELD_TYPE = 'serial';
  public function createStorage($fieldDefinition, $entity);
  public function dropStorage($fieldDefinition, $entity);
  public function initOldEntries($fieldDefinition, $entity);
  public function renameStorage($entityType, $bundleOld, $bundleNew);
  public function getFieldStorageName($fieldDefinition, $entity);
}

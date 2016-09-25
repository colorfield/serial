<?php

namespace Drupal\serial;

// use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Defines an interface for node serial storage classes.
 * @todo review extends ContentEntityStorageInterface
 * @todo document + inherit in implementation.
 */
interface SerialStorageInterface {
  const SERIAL_FIELD_TYPE = 'serial';
  public function createStorage(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity);
  public function dropStorage(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity);
  public function initOldEntries(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity);
  public function renameStorage($entityType, $bundleOld, $bundleNew);
  public function generateValue(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity, $delete);
  public function getFieldStorageName(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity);
}

<?php

namespace Drupal\serial;

// Use Drupal\Core\Entity\ContentEntityStorageInterface;.
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Defines an interface for node serial storage classes.
 */
interface SerialStorageInterface {

  const SERIAL_FIELD_TYPE = 'serial';

  /**
   * Gets the assistant storage for a specific field.
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   *
   * @return mixed
   */
  public function getStorage(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity);

  /**
   * Creates the storage name.
   *
   * @param $entityTypeId
   * @param $entityBundle
   * @param $fieldName
   *
   * @return string
   */
  public function createStorageName($entityTypeId, $entityBundle, $fieldName);

  /**
   * @param $storageName
   * @param bool $delete
   *
   * @return mixed
   */
  public function generateValueFromName($storageName, $delete = TRUE);

  /**
   * Generates a unique serial value (unique per entity bundle).
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   * @param bool $delete
   *   Indicates if temporary records should be deleted.
   */
  public function generateValue(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity, $delete = TRUE);

  /**
   * Gets the schema of the assistant storage for generating serial values.
   *
   * @return array
   *   Assistant storage schema.
   */
  public function getSchema();

  /**
   * Gets a lightweight map of fields across bundles filtered by field type.
   *
   * @return array
   *   An array keyed by entity type. Each value is an array which keys are
   *   field names and value is an array with two entries:
   *   - type: The field type.
   *   - bundles: An associative array of the bundles in which the field
   *     appears, where the keys and values are both the bundle's machine name.
   */
  public function getAllFields();

  /**
   * Creates an assistant serial storage for a new created field.
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   */
  public function createStorage(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity);

  /**
   * Creates an assistant serial storage for a new created field.
   *
   * @param $storageName
   *
   * @return mixed
   */
  public function createStorageFromName($storageName);

  /**
   * Drops an assistant serial storage for a deleted field.
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   */
  public function dropStorage(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity);

  /**
   * Drops an assistant serial storage for a deleted field.
   *
   * @param $storageName
   *
   * @return mixed
   */
  public function dropStorageFromName($storageName);

  /**
   * Initializes the value of a new serial field in existing entities.
   *
   * @param $entityTypeId
   * @param $entityBundle
   * @param $fieldName
   *
   * @return int
   */
  public function initOldEntries($entityTypeId, $entityBundle, $fieldName);

}

<?php

namespace Drupal\serial;

// Use Drupal\Core\Entity\ContentEntityStorageInterface;.
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Defines an interface for node serial storage classes.
 *
 * @todo review extends ContentEntityStorageInterface
 * @todo document + inherit in implementation.
 */
interface SerialStorageInterface {
  const SERIAL_FIELD_TYPE = 'serial';

  /**
   * Creates an assistant serial storage for a new created field.
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   */
  public function createStorage(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity);

  /**
   * Drops an assistant serial storage for a deleted field.
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   */
  public function dropStorage(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity);

  /**
   * Initializes the value of a new serial field in existing entities.
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   *
   * @return int
   */
  public function initOldEntries(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity);

  /**
   * Renames the storage, after a bundle rename.
   *
   * @param $entityType
   * @param $bundleOld
   * @param $bundleNew
   * @return mixed
   */
  public function renameStorage($entityType, $bundleOld, $bundleNew);

  /**
   * Generates a unique serial value (unique per entity bundle).
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   * @param bool $delete
   *   Indicates if temporary records should be deleted.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *
   * @throws \Exception
   */
  public function generateValue(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity, $delete);

  /**
   * Gets the schema of the assistant storage for generating serial values.
   *
   * @param null $tableDescription
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
   * Gets the name of the assistant storage for a specific field.
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   * @return mixed
   */
  public function getStorageName(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity);

}

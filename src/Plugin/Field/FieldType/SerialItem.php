<?php

namespace Drupal\serial\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\serial\SerialStorageInterface;

/**
 * Plugin implementation of the 'serial' field type.
 * @todo should not be translatable, by default
 *
 * @FieldType(
 *   id = "serial",
 *   label = @Translation("Serial"),
 *   description = @Translation("Auto increment serial field type."),
 *   default_widget = "serial_default_widget",
 *   default_formatter = "serial_default_formatter"
 * )
 */
class SerialItem extends FieldItemBase
{

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'sortable' => TRUE,
          'views' => TRUE,
          'index' => TRUE,
          )
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // @todo review DataDefinition methods : setReadOnly, setComputed, setRequired, setConstraints
    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(t('Serial'))
      ->setComputed(TRUE)
      ->setRequired(TRUE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    // For numbers, the field is empty if the value isn't numeric.
    // But should never be treated as empty.
    $empty = $value === NULL || !is_integer($value);
    return $empty;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    $value = $this->getSerial();
    $this->setValue($value);
  }

  /**
   * Gets the serial for this entity type, bundle, field instance.
   * @return int
   */
  private function getSerial() {
    // @todo review, it should make sense to define a starting autoincrement (e.g. history from an invoice system)
    $serial = null;
    $entity = $this->getEntity();
    // Does not apply if the node is not new.
    if($entity->isNew()) {
      // @todo dependency injection
      /** @var SerialStorageInterface */
      $serialStorage = \Drupal::getContainer()->get('serial.sql_storage');

      // @todo the create storage must be done on field instance creation
      $serialStorage->createStorage($this->getFieldDefinition(), $this->getEntity());
      $serial = $serialStorage->generateValue($this->getFieldDefinition(), $this->getEntity());

      // @todo remove non atomic version left during module port
      // Let's start a first naive implementation
      // by querying the amount of entities from this entity type + bundle.
      //$entity_type_id = $entity->getEntityTypeId(); // e.g. node
      //$entity_bundle = $entity->bundle(); // e.g. article
      //$query = \Drupal::entityQuery($entity_type_id);
      //$query->condition('type', $entity_bundle);
      //$result = $query->execute();
      //$serial = count($result) + 1;
    }
    return $serial;
  }

}

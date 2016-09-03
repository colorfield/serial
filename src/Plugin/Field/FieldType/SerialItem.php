<?php

/**
 * @file
 * Contains \Drupal\serial\Plugin\Field\FieldType\SerialItem.
 */

namespace Drupal\serial\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'serial' field type.
 *
 * @FieldType(
 *   id = "serial",
 *   label = @Translation("Serial"),
 *   description = @Translation("Auto increment serial field type."),
 *   default_widget = "serial_default",
 *   default_formatter = "serial_default"
 * )
 */
class SerialItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'int',
          'unsigned' => true,
          'not null' => true,
          'sortable' => true,
          'views' => true,
          'index' => true,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(t('Serial'))
      ->setRequired(true);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // Never should be treated as empty.
    $value = $this->get('value')->getValue();
    return $value;
  }

}

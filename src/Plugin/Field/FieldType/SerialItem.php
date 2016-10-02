<?php

namespace Drupal\serial\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * Plugin implementation of the 'serial' field type.
 *
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
class SerialItem extends FieldItemBase {

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
        ),
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
    $empty = $value === NULL || !is_numeric($value);
    return $empty;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    $value = $this->getSerial();
    if (isset($value)) {
      $this->setValue($value);
    }
  }

  /**
   * Gets the serial for this entity type, bundle, field instance.
   *
   * @return int
   *   serial id
   */
  private function getSerial() {
    // @todo review, it should make sense to define a starting autoincrement (e.g. history from an invoice system)
    $serial = NULL;
    $entity = $this->getEntity();
    $newSerial = FALSE;

    // Does not apply if the node is not new or translated.
    if ($entity->isNew()) {
      $newSerial = TRUE;
    }
    else {
      // Handle entity translation: fetch the same id or another one
      // depending of what is the design.
      // This should probably be solved by the end user decision
      // while setting the field translation.
      /** @var \Drupal\Core\Language\LanguageManagerInterface $languageManager */
      $languageManager = \Drupal::getContainer()->get('language_manager');
      // @todo isMultilingual is global, prefer local hasTranslation
      if ($languageManager->isMultilingual() && $entity instanceof TranslatableInterface) {
        $newSerial = $entity->isNewTranslation();
      }
    }

    if ($newSerial) {
      /** @var \Drupal\serial\SerialStorageInterface $serialStorage */
      $serialStorage = \Drupal::getContainer()->get('serial.sql_storage');
      $serial = $serialStorage->generateValue($this->getFieldDefinition(), $this->getEntity());
    }

    return $serial;
  }

}

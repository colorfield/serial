<?php

namespace Drupal\Tests\serial\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Sets up serial field test and helpers definition.
 *
 * @group serial
 */
abstract class SerialTestBase extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field',
    'field_ui',
    'node',
    'taxonomy',
    'comment',
    'entity_test',
    'serial',
  ];

  /**
   * Entity types and bundles to test on.
   *
   * It must be the entity type id from a ContentEntityType
   * that is accessible from the field UI.
   *
   * @var array
   */
  public static $entityTypes = [
    'entity_test' => ['bundle' => 'entity_test', 'name' => 'Entity test'],
    'node' => ['bundle' => 'article', 'name' => 'Article'],
    'taxonomy_term' => ['bundle' => 'tags', 'name' => 'Tags'],
    'comment' => ['bundle' => 'comment', 'name' => 'Default comments'],
  ];

  /**
   * A user with permission to create test entities.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * An array of display options to pass to EntityViewDisplay.
   *
   * @var array
   */
  protected $displayOptions;

  /**
   * A field storage to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * The current serial id to test on.
   *
   * @var int
   */
  protected $serialId;

  /**
   * The serial field used in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * Attaches a field to a content entity type.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param array $entity_type
   *   The entity type values.
   */
  protected function attachField($entity_type_id, array $entity_type) {
    $field_name = 'field_serial';
    $type = 'serial';
    $widget_type = 'serial_default_widget';
    $formatter_type = 'serial_default_formatter';

    // Add the serial field to the entity test.
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type_id,
      'type' => $type,
      // 'cardinality' => -1, // see #2833498
      // 'translatable' => FALSE, // see #2833491.
    ]);
    $this->fieldStorage->save();
    $this->field = FieldConfig::create([
      'field_storage' => $this->fieldStorage,
      'label' => 'Serial',
      // 'entity_type' => $entity_type_id, // should not be necessary.
      'bundle' => $entity_type['bundle'],
      'required' => TRUE,
    ]);
    $this->field->save();

    EntityFormDisplay::load($entity_type_id . '.' . $entity_type['bundle'] . '.default')
      ->setComponent($field_name, ['type' => $widget_type])
      ->save();

    $this->displayOptions = [
      'type' => $formatter_type,
      'label' => 'hidden',
    ];

    EntityViewDisplay::create([
      'targetEntityType' => $this->field->getTargetEntityTypeId(),
      'bundle' => $this->field->getTargetBundle(),
      'mode' => 'full',
      'status' => TRUE,
    ])->setComponent($field_name, $this->displayOptions)->save();
  }

  /**
   * Returns the entity form for an entity type and bundle.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param array $entity_type
   *   The entity type values.
   * @param string $operation
   *   Create, edit or delete operation.
   *
   * @return string
   *   The path to the entity form.
   */
  protected function getEntityTypeFormPath($entity_type_id, array $entity_type, $operation = 'add') {
    $result = '';
    switch ($entity_type_id) {
      case 'entity_test':
        $result = $entity_type_id . '/' . $operation;
        break;

      case 'node':
        $result = $entity_type_id . '/' . $operation . '/' . $entity_type['bundle'];
        break;

      case 'taxonomy_term':
        $result = 'admin/structure/taxonomy/manage/' . $entity_type['bundle'] . '/' . $operation;
        break;

      case 'comment':
        // @todo
        // $this->drupalGet($entityTypeId . '/add/' . $entityType['bundle']);
        break;
    }
    return $result;
  }

}

<?php

namespace Drupal\Tests\serial\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\comment\Entity\CommentType;
use Drupal\Core\Language\LanguageInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the creation of serial field and CRUD operations on entities.
 *
 * <h2>Assertions</h2>
 * <ul>
 *   <li>When a serial field is attached to an entity,
 *   it should not be visible, by default, on the entity edit form.
 *   </li>
 *   <li>On entity create, the serial id should be a continuous sequence.
 *   <li>On entity update, the serial id should be the same as during creation.
 *   <li>On entity delete, the serial id should not be deleted.
 * </ul>
 *
 * <h2>Scope</h2>
 * <ul>
 *   <li>User interface</li>
 *   <li>Entity API</li>
 * </ul>
 *
 * @group serial
 */
class SerialFieldTest extends BrowserTestBase {

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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->webUser = $this->drupalCreateUser([
      // Node.
      'access content',
      'administer content types',
      'administer nodes',
      'view own unpublished content',
      'administer node fields',
      // Taxonomy.
      'administer taxonomy',
      // Comment.
      'administer comments',
      'administer comment types',
      'access comments',
      // Entity test.
      'view test entity',
      'administer entity_test content',
      'administer entity_test form display',
    ]);
    $this->drupalLogin($this->webUser);

    $this->drupalCreateContentType([
      'type' => self::$entityTypes['node']['bundle'],
      'name' => self::$entityTypes['node']['name'],
    ]);

    $this->createVocabulary([
      'vid' => self::$entityTypes['taxonomy_term']['bundle'],
      'name' => self::$entityTypes['taxonomy_term']['name'],
    ]);

    $this->createCommentType([
      'id' => self::$entityTypes['comment']['bundle'],
      'label' => self::$entityTypes['comment']['name'],
    ]);

    foreach (SerialFieldTest::$entityTypes as $entityTypeId => $entityType) {
      $this->attachField($entityTypeId, $entityType);
    }
  }

  /**
   * Returns a new vocabulary specified by the entity types property.
   *
   * @param array $values
   *   Vocabulary vid and name properties.
   */
  private function createVocabulary(array $values) {
    $values += [
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'weight' => mt_rand(0, 10),
    ];
    $vocabulary = Vocabulary::create($values);
    $vocabulary->save();
    return $vocabulary;
  }

  /**
   * Returns a new comment type specified by the entity types property.
   *
   * @param array $values
   *   Comment type id and label properties.
   */
  private function createCommentType(array $values) {
    $values += [
      'target_entity_type_id' => 'comment',
    ];
    $commentType = CommentType::create($values);
    $commentType->save();
  }

  /**
   * Attaches a field to a content entity type.
   *
   * @param array $entity_type
   *   The entity type id and bundle.
   */
  private function attachField($entity_type_id, array $entity_type) {
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
   * Tests the existence of the serial field on each entity type.
   */
  public function testSerialField() {
    foreach (SerialFieldTest::$entityTypes as $entityTypeId => $entityType) {
      // Test the entity creation form.
      $this->drupalGet($entityTypeId . '/add');
      // Make sure the "serial_default_widget" widget is on the markup.
      $fields = $this->xpath('//div[contains(@class, "field--widget-serial-default-widget") and @id="edit-field-serial-wrapper"]');
      $this->assertEquals(1, count($fields));
      // Make sure that the widget is hidden on the entity creation form.
      $this->assertSession()->fieldNotExists('field_serial[0][value]');

      // Test basic definition of serial field on entity save.
      $edit = [];
      $this->drupalPostForm(NULL, $edit, t('Save'));
      // Make sure the entity was saved.
      preg_match('|' . $entityTypeId . '/manage/(\d+)|', $this->getSession()
        ->getCurrentUrl(), $match);
      $entityId = $match[1];
      drupal_set_message(sprintf('%s %d has been created.', $entityTypeId, $entityId));
      $this->assertSession()
        ->pageTextContains(sprintf('%s %d has been created.', $entityTypeId, $entityId));
      // Make sure the serial id is in the output.
      $this->serialId = 1;
      $this->drupalGet($entityTypeId . '/' . $entityId);
      $serial = $this->xpath('//div[contains(@class, "field__item") and text()="' . $this->serialId . '"]');
      $this->assertEquals(1, count($serial));
    }
  }

  /**
   * Tests N entities creation from the UI for each entity type.
   *
   * @param int $entitiesQuantity
   *   Number of entities that should be created.
   */
  public function testCreateEntityFromUi($entitiesQuantity = 15) {
    foreach (SerialFieldTest::$entityTypes as $entityTypeId => $entityType) {
      $i = 0;
      while ($i < $entitiesQuantity) {
        $this->drupalGet($entityTypeId . '/add');
        $edit = [];
        $this->drupalPostForm(NULL, $edit, t('Save'));
        // Make sure the entity was saved.
        preg_match('|' . $entityTypeId . '/manage/(\d+)|', $this->getSession()
          ->getCurrentUrl(), $match);
        $entityId = $match[1];
        drupal_set_message(sprintf('%s %d has been created.', $entityTypeId, $entityId));
        $this->assertSession()
          ->pageTextContains(sprintf('%s %d has been created.', $entityTypeId, $entityId));
        // Make sure the serial id is in the output.
        $this->serialId++;
        $this->drupalGet($entityTypeId . '/' . $entityId);
        $serial = $this->xpath('//div[contains(@class, "field__item") and text()="' . $this->serialId . '"]');
        $this->assertEquals(1, count($serial));
        $i++;
      }
    }
  }

  /**
   * Tests N entities creation, programmatically, for each entity type.
   *
   * @param int $entitiesQuantity
   *   Number of entities that should be created.
   */
  public function testCreateEntityFromApi($entitiesQuantity = 15) {
    $entityTypeManager = \Drupal::service('entity_type.manager');
    foreach (SerialFieldTest::$entityTypes as $entityTypeId => $entityType) {
      $i = 0;
      while ($i < $entitiesQuantity) {
        // @todo create entities
        // $settings = [];
        // $this->createNode($settings); // ...
        $i++;
      }
    }
  }

}

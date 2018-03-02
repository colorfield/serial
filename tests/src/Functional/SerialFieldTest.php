<?php

namespace Drupal\Tests\serial\Functional;

/**
 * Tests the creation of serial field on entity test.
 *
 * @group serial
 */
class SerialFieldTest extends SerialTestBase {

  /**
   * The entity type id used for this test.
   */
  const ENTITY_TYPE_ID = 'entity_test';

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
      // Entity test.
      'view test entity',
      'administer entity_test content',
      'administer entity_test form display',
    ]);
    $this->drupalLogin($this->webUser);

    $this->attachField(self::ENTITY_TYPE_ID, SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID]);
  }

  /**
   * Tests the existence of the serial field on each entity type.
   */
  public function testSerialField() {
    // Test the entity creation form.
    $formPath = $this->getEntityTypeFormPath(self::ENTITY_TYPE_ID, SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID], 'add');
    $this->drupalGet($formPath);
    // Make sure the "serial_default_widget" widget is on the markup.
    $fields = $this->xpath('//div[contains(@class, "field--widget-serial-default-widget") and @id="edit-field-serial-wrapper"]');
    $this->assertEquals(1, count($fields));
    // Make sure that the widget is hidden on the entity creation form.
    $this->assertSession()->fieldNotExists('field_serial[0][value]');

    // Test basic definition of serial field on entity save.
    $edit = [];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    // Make sure the entity was saved.
    preg_match('|' . self::ENTITY_TYPE_ID . '/manage/(\d+)|', $this->getSession()
      ->getCurrentUrl(), $match);
    $entityId = $match[1];
    $this->assertSession()
      ->pageTextContains(sprintf('%s %d has been created.', self::ENTITY_TYPE_ID, $entityId));
    // Make sure the serial id is in the output.
    $this->serialId = 1;
    $this->drupalGet(self::ENTITY_TYPE_ID . '/' . $entityId);
    $serial = $this->xpath('//div[contains(@class, "field__item") and text()="' . $this->serialId . '"]');
    $this->assertEquals(1, count($serial));
  }

}

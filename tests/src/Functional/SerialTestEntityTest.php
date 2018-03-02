<?php

namespace Drupal\Tests\serial\Functional;

/**
 * Tests the creation of serial field and CRUD operations on Entity Test.
 *
 * @group serial
 */
class SerialTestEntityTest extends SerialEntityTestBase {

  /**
   * The entity type id.
   */
  const ENTITY_TYPE_ID = 'entity_test';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->webUser = $this->drupalCreateUser([
      'view test entity',
      'administer entity_test content',
      'administer entity_test form display',
    ]);
    $this->drupalLogin($this->webUser);

    $this->attachField(self::ENTITY_TYPE_ID, SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID]);
  }

  /**
   * {@inheritdoc}
   */
  public function testCreateEntityFromUi($entitiesQuantity = 15) {
    $i = 0;
    while ($i < $entitiesQuantity) {
      $this->drupalGet($this->getEntityTypeFormPath(self::ENTITY_TYPE_ID, SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID], 'add'));
      $edit = [];
      $this->drupalPostForm(NULL, $edit, t('Save'));
      // Make sure the entity was saved.
      preg_match('|' . self::ENTITY_TYPE_ID . '/manage/(\d+)|', $this->getSession()
        ->getCurrentUrl(), $match);
      $entityId = $match[1];
      $this->assertSession()
        ->pageTextContains(sprintf('%s %d has been created.', self::ENTITY_TYPE_ID, $entityId));
      // Make sure the serial id is in the output.
      $this->serialId++;
      $this->drupalGet(self::ENTITY_TYPE_ID . '/' . $entityId);
      $serial = $this->xpath('//div[contains(@class, "field__item") and text()="' . $this->serialId . '"]');
      $this->assertEquals(1, count($serial));
      $i++;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function testCreateEntityFromApi($entitiesQuantity = 15) {
    // @todo implement
    $entityTypeManager = \Drupal::service('entity_type.manager');
    $i = 0;
    while ($i < $entitiesQuantity) {
      // @todo create entities
      // $settings = [];
      // $this->createNode($settings); // ...
      $i++;
    }
  }

}

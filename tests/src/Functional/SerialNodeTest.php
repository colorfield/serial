<?php

namespace Drupal\Tests\serial\Functional;

/**
 * Tests the creation of serial field and CRUD operations on Node entity.
 *
 * @group serial
 */
class SerialNodeTest extends SerialEntityTestBase {

  /**
   * The entity type id.
   */
  const ENTITY_TYPE_ID = 'node';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->webUser = $this->drupalCreateUser([
      'access content',
      'administer content types',
      'administer nodes',
      'view own unpublished content',
      'administer node fields',
    ]);
    $this->drupalLogin($this->webUser);

    $this->drupalCreateContentType([
      'type' => SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID]['bundle'],
      'name' => SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID]['name'],
    ]);

    $this->attachField(self::ENTITY_TYPE_ID, SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID]);
  }

  /**
   * {@inheritdoc}
   */
  public function testCreateEntityFromUi($entitiesQuantity = 15) {
    // @todo implement
  }

  /**
   * {@inheritdoc}
   */
  public function testCreateEntityFromApi($entitiesQuantity = 15) {
    // @todo implement
  }

}

<?php

namespace Drupal\Tests\serial\Functional;

use Drupal\comment\Entity\CommentType;

/**
 * Tests the creation of serial field and CRUD operations on Comment entity.
 *
 * @group serial
 */
class SerialCommentTest extends SerialEntityTestBase {

  /**
   * The entity type id.
   */
  const ENTITY_TYPE_ID = 'comment';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->webUser = $this->drupalCreateUser([
      'administer comments',
      'administer comment types',
      'access comments',
    ]);
    $this->drupalLogin($this->webUser);

    $this->createCommentType([
      'id' => SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID]['bundle'],
      'label' => SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID]['name'],
    ]);

    $this->attachField(self::ENTITY_TYPE_ID, SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID]);
  }

  /**
   * Returns a new comment type specified by the entity types property.
   *
   * @param array $values
   *   Comment type id and label properties.
   */
  private function createCommentType(array $values) {
    $values += [
      'target_entity_type_id' => self::ENTITY_TYPE_ID,
    ];
    $commentType = CommentType::create($values);
    $commentType->save();
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

<?php

namespace Drupal\Tests\serial\Functional;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Core\Language\LanguageInterface;

/**
 * Tests the creation of serial field and CRUD operations on Term entity.
 *
 * @group serial
 */
class SerialTermTest extends SerialEntityTestBase {

  /**
   * The entity type id.
   */
  const ENTITY_TYPE_ID = 'taxonomy_term';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->webUser = $this->drupalCreateUser([
      'administer taxonomy',
    ]);
    $this->drupalLogin($this->webUser);

    $this->createVocabulary([
      'vid' => SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID]['bundle'],
      'name' => SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID]['name'],
    ]);

    $this->attachField(self::ENTITY_TYPE_ID, SerialEntityTestBase::$entityTypes[self::ENTITY_TYPE_ID]);
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

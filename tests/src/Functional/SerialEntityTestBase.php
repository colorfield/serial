<?php

namespace Drupal\Tests\serial\Functional;

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
abstract class SerialEntityTestBase extends SerialTestBase {

  /**
   * Tests N entities creation from the UI for each entity type.
   *
   * @param int $entitiesQuantity
   *   Number of entities that should be created.
   */
  abstract public function testCreateEntityFromUi($entitiesQuantity = 15);

  /**
   * Tests N entities creation, programmatically, for each entity type.
   *
   * @param int $entitiesQuantity
   *   Number of entities that should be created.
   */
  abstract public function testCreateEntityFromApi($entitiesQuantity = 15);

}

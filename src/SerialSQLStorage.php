<?php

namespace Drupal\serial;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Serial storage service definition.
 *
 * Begin by the D7 implementation with SQL tables.
 *
 * @todo review extends SqlContentEntityStorage
 * @todo remove unused dependencies
 * @todo use DI for database, resolve SQL agnostic driver first
 */
class SerialSQLStorage implements ContainerInjectionInterface, SerialStorageInterface {

  /**
   * Drupal\Core\Entity\Query\QueryInterface definition.
   *
   * @var Drupal\Core\Entity\Query\QueryInterface
   */
  protected $entityQuery;

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(QueryFactory $entityQuery,
                              EntityTypeManager $entityTypeManager) {
    $this->entityQuery = $entityQuery;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageName(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity) {
    return $this->createStorageName(
      $entity->getEntityTypeId(),
      $entity->bundle(),
      $fieldDefinition->getName()
    );
  }

  /**
   * Creates the escaped table name.
   *
   * @param $entityTypeId
   * @param $entityBundle
   * @param $fieldName
   *
   * @return string
   */
  public function createStorageName($entityTypeId, $entityBundle, $fieldName) {
    // Remember about max length of MySQL tables - 64 symbols.
    // @todo Think about improvement for this.
    $tableName = 'serial_' . md5("{$entityTypeId}_{$entityBundle}_{$fieldName}");
    return Database::getConnection()->escapeTable($tableName);
  }

  /**
   * @todo description
   * @todo refactoring
   *
   * @param $storageName
   * @param $delete
   * @return \Drupal\Core\Database\StatementInterface|int|null
   * @throws \Exception
   */
  public function generateValueFromName($storageName, $delete = TRUE) {
    $connection = Database::getConnection();
    // @todo review https://api.drupal.org/api/drupal/core%21includes%21database.inc/function/db_transaction/8.2.x
    $transaction = $connection->startTransaction();

    try {
      // Insert a temporary record to get a new unique serial value.
      $uniqid = uniqid('', TRUE);
      $sid = $connection->insert($storageName)
        ->fields(array('uniqid' => $uniqid))
        ->execute();

      // If there's a reason why it's come back undefined, reset it.
      $sid = isset($sid) ? $sid : 0;

      // Delete the temporary record.
      if ($delete && $sid && ($sid % 10) == 0) {
        $connection->delete($storageName)
          ->condition('sid', $sid, '<')
          ->execute();
      }

      // Return the new unique serial value.
      return $sid;
    }
    // @todo use dedicated Exception
    // https://www.drupal.org/node/608166
    catch (Exception $e) {
      $transaction->rollback();
      watchdog_exception('serial', $e);
      throw $e;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generateValue(FieldDefinitionInterface $fieldDefinition,
                                FieldableEntityInterface $entity,
                                $delete = TRUE) {
    $storageName = $this->getStorageName($fieldDefinition, $entity);
    return $this->generateValueFromName($storageName, $delete);
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema($tableDescription = NULL) {
    $schema = array(
      'fields' => array(
        'sid' => array(
          // Serial Drupal DB type, not SerialStorageInterface::SERIAL_FIELD_TYPE
          // means auto increment
          // https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Database!database.api.php/group/schemaapi/8.2.x
          'type' => 'serial',
          'not null' => TRUE,
          'unsigned' => TRUE,
          'description' => 'The atomic serial field.',
        ),
        'uniqid' => array(
          'type' => 'varchar',
          'length' => 23,
          'default' => '',
          'not null' => TRUE,
          // @todo review UUID instead
          'description' => 'Unique temporary allocation Id.',
        ),
      ),
      'primary key' => array('sid'),
      'unique keys' => array(
        'uniqid' => array('uniqid'),
      ),
    );
    if (isset($description)) {
      $schema['description'] = $tableDescription;
    }
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function createStorage(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity) {
    $storageName = $this->getStorageName($fieldDefinition, $entity);
    $this->createStorageFromName($storageName);
  }

  /**
   * @todo description.
   * @todo class refactoring needed to get something consistent with method signatures / facade.
   * @param $storageName
   */
  public function createStorageFromName($storageName) {
    $dbSchema = Database::getConnection()->schema();
    if (!$dbSchema->tableExists($storageName)) {
      // $tableDescription = 'Serial storage for entity type ' . $entity->getEntityTypeId();
      // $tableDescription .= ', bundle ' . $entity->bundle();
      // $dbSchema->createTable($storageName, $this->getSchema($tableDescription));
      $dbSchema->createTable($storageName, $this->getSchema());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function dropStorage(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity) {
    $this->dropStorageFromName($this->getStorageName($fieldDefinition, $entity));
  }

  /**
   * @todo description.
   * @todo class refactoring needed to get something consistent with method signatures / facade.
   * @param $storageName
   */
  public function dropStorageFromName($storageName) {
    $dbSchema = Database::getConnection()->schema();
    $dbSchema->dropTable($storageName);
  }

  /**
   * {@inheritdoc}
   */
  public function initOldEntries($entityTypeId, $entityBundle, $fieldName) {
    $query = $this->entityQuery->get($entityTypeId);
    // @todo shall we assign serial id to unpublished as well?
    // $query->condition('status', 1);
    $query->condition('type', $entityBundle);
    $entityIds = $query->execute();

    $updated = 0;
    $storageName = $this->createStorageName(
      $entityTypeId,
      $entityBundle,
      $fieldName
    );

    foreach ($entityIds as $entityId) {
      $entity = $this->entityTypeManager
        ->getStorage($entityTypeId)
        ->loadUnchanged($entityId);
      $serial = $this->generateValueFromName($storageName);
      // @todo review multilingual
      $entity->{$fieldName}->und[0]->value = $serial;
      if ($entity->save() === SAVED_UPDATED) {
        ++$updated;
      }
    }

    return $updated;
  }

  /**
   * {@inheritdoc}
   */
  public function renameStorage($entityType, $bundleOld, $bundleNew) {
    // TODO: Implement renameStorage() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getAllFields() {
    return \Drupal::entityManager()->getFieldMapByFieldType(SerialStorageInterface::SERIAL_FIELD_TYPE);
  }

}

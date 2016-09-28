<?php

namespace Drupal\serial;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Serial storage service definition.
 * Begin by the D7 implementation with SQL tables.
 *
 * @todo review extends SqlContentEntityStorage
 * @todo remove unused dependencies
 * @todo use DI for database, resolve SQL agnostic driver first
 */
class SerialSQLStorage implements ContainerInjectionInterface, SerialStorageInterface {
  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @todo review driver
   * @var Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

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
  public function __construct(Connection $database,
                              QueryFactory $entityQuery,
                              EntityTypeManager $entityTypeManager) {
    $this->database = $database;
    $this->entityQuery = $entityQuery;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('entity.query'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Gets the name of the assistant table for a specific field.
   *
   * @param $field
   * @param $entity
   *
   * @return string
   */
  public function getStorageName(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity) {
    return $this->createTableStorageName($entity->getEntityTypeId(), $entity->bundle(), $fieldDefinition->getName());
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
  public function createTableStorageName($entityTypeId, $entityBundle, $fieldName) {
    // Remember about max length of MySQL tables - 64 symbols.
    // @todo Think about improvement for this.
    $tableName = 'serial_' . md5("{$entityTypeId}_{$entityBundle}_{$fieldName}");
    return Database::getConnection()->escapeTable($tableName);
  }

  /**
   * Generates a unique serial value (unique per entity bundle).
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   * @param bool $delete
   *   Indicates if temporary records should be deleted.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *
   * @throws \Exception
   */
  public function generateValue(FieldDefinitionInterface $fieldDefinition,
                                FieldableEntityInterface $entity,
                                $delete = TRUE) {
    $connection = Database::getConnection();
    // @todo review https://api.drupal.org/api/drupal/core%21includes%21database.inc/function/db_transaction/8.2.x
    $transaction = $connection->startTransaction();

    try {
      // Get the name of the relevant table.
      $tableName = $this->getStorageName($fieldDefinition, $entity);
      // Insert a temporary record to get a new unique serial value.
      $uniqid = uniqid('', TRUE);
      $sid = $connection->insert($tableName)
        ->fields(array('uniqid' => $uniqid))
        ->execute();

      // If there's a reason why it's come back undefined, reset it.
      $sid = isset($sid) ? $sid : 0;

      // Delete the temporary record.
      if ($delete && $sid && ($sid % 10) == 0) {
        $connection->delete($tableName)
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
   * Gets the schema of the assistant tables for generating serial values.
   *
   * @param null $tableDescription
   *
   * @return array
   *   Assistant table schema.
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
   * Creates an assistant serial table for a new created field.
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   */
  public function createStorage(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity) {
    $dbSchema = Database::getConnection()->schema();
    $tableName = $this->getStorageName($fieldDefinition, $entity);
    if (!$dbSchema->tableExists($tableName)) {
      $tableDescription = 'Serial storage for entity type ' . $entity->getEntityTypeId();
      $tableDescription .= ', bundle ' . $entity->bundle();
      $dbSchema->createTable($tableName, $this->getSchema($tableDescription));

      // @todo review if we should called this here.
      $this->initOldEntries($fieldDefinition, $entity);
    }
  }

  /**
   * Drops an assistant serial table for a deleted field.
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   */
  public function dropStorage(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity) {
    $dbSchema = Database::getConnection()->schema();
    $dbSchema->dropTable($this->getStorageName($fieldDefinition, $entity));
  }

  /**
   * Initializes the value of a new serial field in existing entities.
   *
   * @param FieldDefinitionInterface $fieldDefinition
   * @param FieldableEntityInterface $entity
   *
   * @return int
   */
  public function initOldEntries(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity) {
    // TODO: Implement initOldEntries() method.
    //    $entity_type_id = $entity->getEntityTypeId(); // e.g. node
    //    $entity_bundle = $entity->bundle(); // e.g. article
    //    $query = \Drupal::entityQuery($entity_type_id)
    //          ->condition('field', $fieldDefinition->getName());
    //
    //    // @todo check this if the "comment" entity type still does not support bundle conditions.
    //    // @see https://api.drupal.org/api/drupal/includes!entity.inc/function/EntityFieldQuery%3A%3AentityCondition/7
    //    if ('comment' !== $entity_type_id) {
    //      $query->condition('bundle', $entity_bundle);
    //    }
    //
    //    $result = $query->execute();
    //    foreach ($result as $entity) {
    //    }.
    // @todo section to port
    /*
    if (!empty($result[$entity_type])) {
    foreach ($result[$entity_type] as $entity) {
    list($id, , $bundle) = entity_extract_ids($entity_type, $entity);

    $entity = entity_load_unchanged($entity_type, $id);
    $entity->{$field_name} = array(
    LANGUAGE_NONE => array(
    array(
    'value' => _serial_generate_value($entity_type, $bundle, $field_name, FALSE),
    ),
    ),
    );

    field_attach_insert($entity_type, $entity);
    }

    return count($result[$entity_type]);
    }
     */

    return 0;
  }

  /**
   *
   */
  public function renameStorage($entityType, $bundleOld, $bundleNew) {
    // TODO: Implement renameStorage() method.
  }

  /**
   * Gets a lightweight map of fields across bundles filtered by field type.
   *
   * @return array
   *   An array keyed by entity type. Each value is an array which keys are
   *   field names and value is an array with two entries:
   *   - type: The field type.
   *   - bundles: An associative array of the bundles in which the field
   *     appears, where the keys and values are both the bundle's machine name.
   */
  public function getAllFields() {
    return \Drupal::entityManager()->getFieldMapByFieldType(SerialStorageInterface::SERIAL_FIELD_TYPE);
  }

  // Public function getFieldStorageName(FieldDefinitionInterface $fieldDefinition, FieldableEntityInterface $entity) {
  // TODO: Implement getFieldStorageName() method.
  // not used anymore / removed from interface, to delete
  // }.
}

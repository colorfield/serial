<?php

namespace Drupal\serial;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Serial storage service definition.
 * Begin by the D7 implementation with SQL tables.
 * @todo review extends SqlContentEntityStorage
 * @todo remove unused dependencies
 */
class SerialSQLStorage implements ContainerInjectionInterface, SerialStorageInterface
{
  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
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
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('database'),
      $container->get('entity.query'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Gets the name of the assistant table for a specific field.
   * @param $field
   * @param $entity
   * @return string
   */
  private function getStorageName($fieldDefinition, $entity) {
    // Remember about max length of MySQL tables - 64 symbols.
    // @todo Think about improvement for this.
    $table = 'serial_' . md5("{$entity->getEntityTypeId()}_{$entity->bundle()}_{$fieldDefinition->getName()}");
    return Database::getConnection()->escapeTable($table);
  }

  public function createStorage($fieldDefinition, $entity) {
    // @todo use DI, resolve SQL agnostic driver first
    $dbSchema = Database::getConnection()->schema();
    $tableDescription = 'Serial storage for entity type ' . $entity->getEntityTypeId();
    $tableDescription .= ', bundle ' . $entity->bundle();
    $tableSchema = array(
      'description' =>  $tableDescription,
      'fields' => array(
        'sid' => array(
          // serial Drupal DB type, not SerialStorageInterface::SERIAL_FIELD_TYPE
          // @see https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Database!database.api.php/group/schemaapi/8.2.x
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
    $dbSchema->createTable($this->getStorageName($fieldDefinition, $entity), $tableSchema);
  }

  public function dropStorage($fieldDefinition, $entity) {
    // TODO: Implement dropStorage() method.
  }

  public function initOldEntries($fieldDefinition, $entity) {
    // TODO: Implement initOldEntries() method.
  }

  public function renameStorage($entityType, $bundleOld, $bundleNew) {
    // TODO: Implement renameStorage() method.
  }

  public function getFieldStorageName($fieldDefinition, $entity) {
    // TODO: Implement getFieldStorageName() method.
  }
}
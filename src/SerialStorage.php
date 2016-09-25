<?php

namespace Drupal\serial;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Serial storage service definition.
 * Begin by the D7 implementation with SQL tables.
 * @todo review extends SqlContentEntityStorage
 */
class Serial implements ContainerInjectionInterface, SerialStorageInterface
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
   * Fetches content type data.
   * @param $contentType
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function getContentType($contentType)
  {
    return $this->entityTypeManager->getStorage('node_type')->load($contentType);
  }

  public function createStorage($field, $instance) {
    // TODO: Implement createStorage() method.
  }

  public function dropStorage($field, $instance) {
    // TODO: Implement dropStorage() method.
  }

  public function initOldEntries($field, $instance) {
    // TODO: Implement initOldEntries() method.
  }

  public function renameStorage($entityType, $bundleOld, $bundleNew) {
    // TODO: Implement renameStorage() method.
  }

  public function getFieldStorageName($field, $instance) {
    // TODO: Implement getFieldStorageName() method.
  }
}
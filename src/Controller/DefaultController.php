<?php

namespace Drupal\serial\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Class DefaultController.
 *
 * @package Drupal\serial\Controller
 */
class DefaultController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityManager definition.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;
  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManager $entity_manager, EntityTypeManager $entity_type_manager, QueryFactory $entity_query) {
    $this->entityManager = $entity_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.manager'),
      $container->get('entity.query')
    );
  }

  public function getAllFields() {
    // TODO: Implement getAllFields() method.
    $result = [];
    $map = $this->entityManager->getFieldMapByFieldType('serial');
    kint($map);
    foreach($map as $entityTypeId => $entry) {
      dsm("-- " . $entityTypeId);
      foreach($entry as $fieldKey => $fieldInstance) {
        dsm($fieldKey);
        foreach($fieldInstance['bundles'] as $bundle) {
          dsm($bundle);
        }
      }
    }
    // array of $field objects with $fieldDefinition and $entity props
    return $result;
  }

  /**
   * Test.
   *
   * @return string
   *   Return Hello string.
   */
  public function test() {
    $this->getAllFields();
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: test')
    ];
  }

}

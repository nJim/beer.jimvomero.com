<?php

namespace Drupal\brewery_homepage\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HomepageController.
 *
 * Returns the contents of a custom homepage. The page is rendered using the
 * 'brewery_homepage' template in the site's theme. Since this is a simple site,
 * all of the features of this template are simply hardcoded.
 */
class HomepageController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Homepage content.
   *
   * @return array
   *   Return the rendered homepage content.
   */
  public function content() {

    // Load data for published breweries and count brewery types.
    $breweryEntities = self::loadBreweryData();
    $breweryTypeCount = self::countBreweryTypes($breweryEntities);

    return [
      '#theme' => 'brewery_homepage',
      '#attached' => [
        'drupalSettings' => [
          'breweryTypeCount' => $breweryTypeCount,
        ],
      ],
    ];
  }

  /**
   * Load published brewery data.
   *
   * @return array
   *   An array of brewery entities.
   */
  protected function loadBreweryData(): array {
    return $this->entityTypeManager
      ->getListBuilder('brewery')
      ->getStorage()
      ->loadByProperties(['status' => 1]);
  }

  /**
   * Load brewery type data and count the values.
   *
   * The return array will likely take the form:
   * [
   *   ['brewery'] => 33,
   *   ['brewpub'] => 67,
   *   ['taproom'] => 11,
   * ]
   *
   * @param array $breweryEntities
   *   An array of brewery entities.
   *
   * @return array
   *   Brewery type count.
   */
  protected function countBreweryTypes(array $breweryEntities) {
    $types = [];
    foreach ($breweryEntities as $brewery) {
      $type = $brewery->get('field_type')->first()->getValue();
      array_push($types, strtolower($type['value']) ?? NULL);
    }
    return array_count_values($types);
  }

  /**
   * Constructs a new ListController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $container->get('entity_type.manager');
    return new static(
      $entityTypeManager
    );
  }

}

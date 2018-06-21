<?php

namespace Drupal\brewery_map\Controller;

use Drupal\brewery\Entity\BreweryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class MapController provides a 'Brewery Map' page.
 *
 * The brewery map appears on the main menu. Brewery data is passed to the
 * brewery_map template where it is added to the page with javascript.
 * The Google Maps API v3 creates the interactive map.
 */
class MapController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Page content.
   *
   * @return array
   *   Page render array.
   */
  public function contents() {

    $breweryEntities = self::loadBreweryData();
    $mapData = array_map('self::parseBreweryData', $breweryEntities);

    return [
      '#theme' => 'brewery_map',
      '#attached' => [
        'library' => [
          'brewery_map/brewery_map',
        ],
        'drupalSettings' => [
          'mapData' => $mapData,
        ],
      ],
      '#cache' => [
        'contexts' => [
          'route',
        ],
      ],
    ];
  }

  /**
   * Load published breweries to place on the map.
   *
   * @return array
   *   An array of brewery entities.
   */
  protected function loadBreweryData() {
    return $this->entityTypeManager
      ->getListBuilder('brewery')
      ->getStorage()
      ->loadByProperties(['status' => 1]);
  }

  /**
   * Parse and organize brewery entity for elements that build the map.
   *
   * @param \Drupal\brewery\Entity\BreweryInterface $brewery
   *   A brewery entity.
   *
   * @return array
   *   Brewery data useful for building the map.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function parseBreweryData(BreweryInterface $brewery) {
    $fieldGeo = $brewery->get('field_geolocation')->first()->getValue();
    $fieldAdd = $brewery->get('field_location')->first()->getValue();
    $fieldType = array_filter(array_map(function ($type) {
      return $type['value'] ?? NULL;
    }, $brewery->get('field_type')->getValue()));
    return [
      'name' => $brewery->label(),
      'types' => $fieldType,
      'coordinates' => [
        'longitude' => $fieldGeo['lon'],
        'latitude' => $fieldGeo['lat'],
      ],
      'address' => [
        'city' => $fieldAdd['locality'] ?? '',
        'state' => $fieldAdd['administrative_area'] ?? '',
      ],
    ];
  }

  /**
   * Constructs a new MapController object.
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

<?php

namespace Drupal\brewery_map\Controller;

use Drupal\brewery\Entity\BreweryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\TypedData;
use Drupal\Core\TypedData\TypedDataInterface;
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
   * Provides the page title.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The page title.
   */
  public function getHeadline(): TranslatableMarkup {
    return $this->t('Brew Map');
  }

  /**
   * Page content.
   *
   * @return array
   *   Page render array.
   */
  public function contents(): array {

    // Load data for published breweries and prepare variables for theming.
    $breweryEntities = self::loadBreweryData();
    $mapData = array_filter(array_map('self::parseBreweryData', $breweryEntities));
    $countBreweries = count($mapData);
    $countState = count(array_unique(array_map(function ($brewery) {
      return $brewery['address']['state'] ?? NULL;
    }, $mapData)));

    return [
      'content_header_row' => [
        '#theme' => 'content_header',
        '#headline' => self::getHeadline(),
        '#description' => "Always on the lookout for a new local favorite, my 
          travels have taken me to {$countBreweries} breweries, brewpubs, and
          taprooms across {$countState} states and provinces.",
      ],
      'brewery_map_row' => [
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
      ],
    ];
  }

  /**
   * Load published breweries to place on the map.
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
   * Parse and organize brewery entity for elements that build the map.
   *
   * @param \Drupal\brewery\Entity\BreweryInterface $brewery
   *   A brewery entity.
   *
   * @return array
   *   Brewery data useful for building the map.
   */
  protected function parseBreweryData(BreweryInterface $brewery): array {
    // Can not map breweries if long/lat data is not set, so return empty.
    if (!$fieldGeo = $brewery->getGeolocation()) {
      return [];
    }
    $fieldAdd = $brewery->getAddress();
    $fieldType = $brewery->getTypes();

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

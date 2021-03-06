<?php

namespace Drupal\brewery_list\Controller;

use Drupal\brewery\Entity\BreweryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class ListController provides a 'Brewery List' page.
 *
 * The brewery list appears on the main menu. Brewery data is passed to the
 * brewery_list template.
 */
class ListController extends ControllerBase {

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
    return $this->t('Brew List');
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
    $breweryData = array_map('self::parseBreweryData', $breweryEntities);
    usort($breweryData, 'self::sortByDateVisitCallback');

    return [
      'content_header_row' => [
        '#theme' => 'content_header',
        '#headline' => self::getHeadline(),
        '#description' => "	Visiting breweries is an experience: sampling local
          flavors, geeking-out with beer nerds, and if you’re lucky, the aroma
          of malt and grains steeping. I’m always searching for my next
          favorite brew.",
      ],
      'brewery_list_row' => [
        '#theme' => 'brewery_list',
        '#breweries' => $breweryData,
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
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function parseBreweryData(BreweryInterface $brewery): array {
    $fieldAdd = $brewery->getAddress();
    $fieldDate = $brewery->getDateVisit();
    $fieldType = $brewery->getTypes();
    $image_url = $brewery->getImageUrl('field_image', 'tile_400x300');
    $staticmap_url = $brewery->getImageUrl('field_staticmap', 'tile_400x300');

    return [
      'name' => $brewery->label(),
      'types' => $fieldType,
      'date' => $fieldDate,
      'image' => [
        'url' => $image_url ?? '',
      ],
      'staticmap' => [
        'url' => $staticmap_url ?? '',
      ],
      'address' => [
        'city' => $fieldAdd['locality'] ?? '',
        'state' => $fieldAdd['administrative_area'] ?? '',
      ],
    ];
  }

  /**
   * Sort brewery data array by 'date' key.
   */
  protected function sortByDateVisitCallback($a, $b) {
    return ($a['date'] <= $b['date']) ? -1 : 1;
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

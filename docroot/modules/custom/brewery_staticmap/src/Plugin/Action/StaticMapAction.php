<?php

namespace Drupal\brewery_staticmap\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Fetches a static map image using a Google API and attached it to a brewery.
 *
 * @Action(
 *   id = "staticmap_action",
 *   label = @Translation("Fetch Static Map"),
 * )
 */
class StaticMapAction extends ActionBase {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function execute($entity = NULL) {

    /** @var \Drupal\brewery_staticmap\BreweryStaticMap $staticMap */
    $staticMap = \Drupal::service('brewery_staticmap');
    $mediaElement = $staticMap->generateStaticMapForBrewery($entity);

    if ($mediaElement) {
      $entity->set('field_staticmap', $mediaElement->id());
      $entity->save();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\custom_entity_tools\Entity\EntityBaseInterface $entity */
    $result = $object->access('update', $account, TRUE);
    return $return_as_object ? $result : $result->isAllowed();
  }

}

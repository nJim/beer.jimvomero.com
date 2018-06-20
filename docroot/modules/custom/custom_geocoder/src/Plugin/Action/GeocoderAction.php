<?php

namespace Drupal\custom_geocoder\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use CommerceGuys\Addressing\Address;

/**
 * Runs Google API Geocoder for current address values.
 *
 * @Action(
 *   id = "geocoder_action",
 *   label = @Translation("Geocode address field"),
 * )
 */
class GeocoderAction extends ActionBase {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function execute($entity = NULL) {

    $geo = \Drupal::service('custom_geocoder.geocoder');

    /** @var \Drupal\custom_entity_tools\Entity\EntityBaseInterface $entity */
    $values = $entity->get('field_location')->first()->getValue();

    $address = new Address(
      $countryCode        = $values["country_code"],
      $administrativeArea = $values["administrative_area"],
      $locality           = $values["locality"],
      $dependentLocality  = '',
      $postalCode         = $values["postal_code"],
      $sortingCode        = '',
      $addressLine1       = $values["address_line1"],
      $addressLine2       = $values["address_line2"]
    );

    if ($waypoint = $geo->getWaypointFromAddress($address)) {
      $entity->set('field_geolocation', $waypoint);
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

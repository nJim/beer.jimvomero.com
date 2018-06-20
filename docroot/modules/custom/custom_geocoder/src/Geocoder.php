<?php

namespace Drupal\custom_geocoder;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\geofield\WktGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CommerceGuys\Addressing\AddressInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class Geocoder.
 *
 * Fetches geocoded coordinates from address field values via Google API.
 */
class Geocoder implements ContainerInjectionInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The waypoint generator service.
   *
   * @var \Drupal\geofield\WktGeneratorInterface
   */
  protected $geoFieldGenerator;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Generate a WKT point from an AddressInterface address.
   *
   * @param \CommerceGuys\Addressing\AddressInterface $address
   *   An address to geocode.
   *
   * @return null|string
   *   The WKT point of geocoded address.
   */
  public function getWaypointFromAddress(AddressInterface $address): ?string {
    $addressString = self::buildAddressString($address);
    $coordinates = self::geocode($addressString);
    if (isset($coordinates['latitude'], $coordinates['longitude'])) {
      return $this->geoFieldGenerator->WktBuildPoint([
        $coordinates['latitude'],
        $coordinates['longitude'],
      ]);
    }
    return NULL;
  }

  /**
   * Generate a fully qualified address to meet google's geocoder standards.
   *
   * @param \CommerceGuys\Addressing\AddressInterface $address
   *   An address to geocode.
   *
   * @return string
   *   Geocoded address as a string.
   */
  public function buildAddressString(AddressInterface $address): string {
    return implode(" ", array_filter([
      $address->getAddressLine1(),
      $address->getAddressLine2(),
      $address->getLocality(),
      $address->getAdministrativeArea(),
      $address->getPostalCode(),
      $address->getCountryCode(),
    ]));
  }

  /**
   * Helper function to request and parse geocode info from Google Geocode API.
   *
   * @param string $addressString
   *   A fully qualified address to meet google's geocoder standards.
   *
   * @return array|null
   *   Array with keys 'latitude' and 'longitude'. NULL if any geocoding errors.
   */
  public function geocode($addressString): ?array {

    $client = \Drupal::httpClient();
    $address = urlencode($addressString);
    if ($response = $client->get("http://maps.google.com/maps/api/geocode/json?address={$address}")) {
      $data = json_decode($response->getBody(), TRUE);
    }

    // Response status will be 'OK', if able to geocode given address.
    if (isset($data['status']) && $data['status'] == 'OK') {
      return array(
        'latitude' => $data['results'][0]['geometry']['location']['lat'] ?? NULL,
        'longitude' => $data['results'][0]['geometry']['location']['lng'] ?? NULL,
      );
    }
    $this->messenger->addMessage(
      'Google Geocoder returned status %status for the address %address.',
      [
        '%status' => $data['status'] ?? 'unknown',
        '%address' => $addressString,
      ]
    );
    return NULL;
  }

  /**
   * Constructs a new BreweryImportItem.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\geofield\WktGeneratorInterface $geoFieldGenerator
   *   The waypoint generator service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, WktGeneratorInterface $geoFieldGenerator, MessengerInterface $messenger) {
    $this->geoFieldGenerator = $geoFieldGenerator;
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $container->get('entity_type.manager');
    /* @var \Drupal\geofield\WktGeneratorInterface $geoFieldGenerator */
    $geoFieldGenerator = $container->get('geofield.wkt_generator');
    /** @var \Drupal\Core\Messenger\MessengerInterface $messenger */
    $messenger = $container->get('messenger');
    return new static(
      $entityTypeManager,
      $geoFieldGenerator,
      $messenger
    );
  }

}

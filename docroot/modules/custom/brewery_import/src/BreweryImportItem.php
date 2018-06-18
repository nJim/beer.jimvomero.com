<?php

namespace Drupal\brewery_import;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\brewery\Entity\Brewery;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\geofield\WktGeneratorInterface;
use Drupal\media\Entity\Media;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BreweryImportItem.
 *
 * Organize and process data from the brewery import scripts. Used to create
 * new Brewery content items and related entities.
 */
class BreweryImportItem implements ContainerInjectionInterface {

  /**
   * The plain data values of the contained fields.
   *
   * This property is an overload array of any values that apply to the creation
   * of the Brewery entity. This pattern is similar to ContentEntityBase.
   *
   * @var array
   */
  protected $values = [];

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * The File System service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The waypoint generator service.
   *
   * @var \Drupal\geofield\WktGeneratorInterface
   */
  protected $geoFieldGenerator;

  /**
   * The Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Add the date of the visit to the brewery.
   *
   * @param string $name
   *   The name of the brewery.
   *
   * @return \Drupal\brewery_import\BreweryImportItem
   *   The current brewery import item.
   */
  public function setName(string $name): BreweryImportItem {
    $this->values['name'][] = $name;
    return $this;
  }

  /**
   * Add the date of the visit to the brewery.
   *
   * @param string $date
   *   The date of the brewery visit in form 'Y-m-d'.
   *
   * @return \Drupal\brewery_import\BreweryImportItem
   *   The current brewery import item.
   */
  public function setDateVisit(string $date): BreweryImportItem {
    $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
    $this->values['date'][] = $dateTime->format('Y-m-d\TH:i:s');
    return $this;
  }

  /**
   * Add the types-values to the brewery.
   *
   * @param int $latitude
   *   The latitude component of the coordinates of the venue.
   * @param int $longitude
   *   The longitude component of the coordinates of the venue.
   *
   * @return \Drupal\brewery_import\BreweryImportItem
   *   The current brewery import item.
   */
  public function setGeolocation(int $latitude, int $longitude): BreweryImportItem {
    $this->values['geolocation'][] = $this->geoFieldGenerator->WktBuildPoint([$latitude, $longitude]);
    return $this;
  }

  /**
   * Add the location details to the brewery.
   *
   * @param array $locationData
   *   An array of location data following the form:
   *   $locationData = [
   *     'country_code'        => 'USA',
   *     'administrative_area' => 'PA',
   *     'locality'            => 'State College',
   *     'postal_code'         => '16801',
   *     'address_line1'       => '1200 N. James Street',
   *     'address_line2'       => 'Second Floor',
   *   ].
   *
   * @return \Drupal\brewery_import\BreweryImportItem
   *   The current brewery import item.
   */
  public function setLocation(array $locationData): BreweryImportItem {
    $this->values['location'][] = [
      'country_code'        => $locationData["country_code"],
      'administrative_area' => $locationData["administrative_area"],
      'locality'            => $locationData["locality"],
      'postal_code'         => $locationData["postal_code"],
      'address_line1'       => $locationData["address_line1"],
      'address_line2'       => $locationData["address_line2"],
    ];
    return $this;
  }

  /**
   * Add the types-values to the brewery.
   *
   * @param string $type
   *   The machine-readable key for the brewery-type.
   *
   * @return \Drupal\brewery_import\BreweryImportItem
   *   The current brewery import item.
   */
  public function addType(string $type): BreweryImportItem {
    if (!in_array($type, $this->values['types'])) {
      $this->values['types'][] = $type;
    }
    return $this;
  }

  /**
   * Create a media entity of type image from a remote image url.
   *
   * @param string $url
   *   The fully qualified path to the remote file.
   *
   * @return \Drupal\brewery_import\BreweryImportItem
   *   The current brewery import item.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addMedia(string $url): BreweryImportItem {
    if ($file = $this->createFileFromUrl($url)) {
      $imageMedia = Media::create([
        'bundle' => 'image',
        'uid' => $this->account->id(),
        'langcode' => $this->languageManager->getDefaultLanguage()->getId(),
        'status' => 1,
        'field_media_image' => [
          'target_id' => $file->id(),
          'alt' => $this->values['name'] || 'Brewery Image',
        ],
      ]);
      $imageMedia->save();
      $this->values['imageIds'][] = $imageMedia->id();
    }
    return $this;
  }

  /**
   * Retrieve a remote file and create a managed File entity.
   *
   * @param string $url
   *   The fully qualified path to the remote file.
   *
   * @return \Drupal\file\FileInterface|null
   *   The created File entity if created or Null.
   */
  public function createFileFromUrl(string $url): ?FileInterface {
    // Retrieve the file from the remote location and save it to the temporary
    // files directory. Retain the name from the remote website.
    $filename = $this->fileSystem->basename($url);
    $tempDestination = 'temporary://' . $filename;
    $tempFile = system_retrieve_file($url, $tempDestination, $managed = FALSE, $replace = FILE_EXISTS_REPLACE);

    // Copy the temporary file to the default file location. Move this as an
    // unmanaged file as its usage will be tracked as a File entity item.
    $filesDestination = file_default_scheme() . '://breweries/' . $filename;
    $fileUri = file_unmanaged_move($tempFile, $filesDestination, $replace = FILE_EXISTS_RENAME);

    // Create a new drupal managed file and save it to the node.
    $fileEntity = File::Create(['uri' => $fileUri]);
    try {
      $fileEntity->save();
      return $fileEntity;
    }
    catch (EntityStorageException $e) {
      return NULL;
    }
  }

  /**
   * Create a new Brewery entity from import data.
   *
   * @return int
   *   Return the numeric id of the created Brewery entity.
   */
  public function importBrewery(): int {
    $breweryEntity = Brewery::create([
      'name'              => $this->values['name'],
      'field_type'        => $this->values['types'],
      'field_geolocation' => $this->values['geolocation'],
      'field_date_visit'  => $this->values['date'],
      'field_image'       => $this->values['imageIds'],
      'field_location'    => $this->values['location'],
    ]);
    try {
      $breweryEntity->save();
    }
    catch (EntityStorageException $e) {
      throwException($e);
    }
    return $breweryEntity->id();
  }

  /**
   * Reset the breweryValues property back to defaults values.
   *
   * When this class is used within a loop, the service container allows a
   * single instance of this class to exist over the course of the execution.
   * This method allow the reset of data between processing breweries.
   */
  public function resetValues() {
    $this->values = [
      'name',
      'date',
      'types',
      'geolocation',
      'location',
      'imageIds',
    ];
  }

  /**
   * Constructs a new BreweryImportItem.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user account service.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   * @param \Drupal\geofield\WktGeneratorInterface $geoFieldGenerator
   *   The waypoint generator service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(AccountProxyInterface $account, FileSystemInterface $fileSystem, WktGeneratorInterface $geoFieldGenerator, LanguageManagerInterface $languageManager) {
    $this->account = $account;
    $this->geoFieldGenerator = $geoFieldGenerator;
    $this->fileSystem = $fileSystem;
    $this->languageManager = $languageManager;
    $this->resetValues();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Session\AccountProxyInterface $account */
    $account = $container->get('current_user');
    /* @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = $container->get('file_system');
    /* @var \Drupal\geofield\WktGeneratorInterface $geoFieldGenerator */
    $geoFieldGenerator = $container->get('geofield.wkt_generator');
    /* @var \Drupal\Core\Language\LanguageManagerInterface $languageManager */
    $languageManager = $container->get('language_manager');
    return new static(
      $account,
      $fileSystem,
      $geoFieldGenerator,
      $languageManager
    );
  }

}

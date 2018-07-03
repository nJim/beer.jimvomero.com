<?php

namespace Drupal\brewery_staticmap;

use Drupal\brewery\Entity\Brewery;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\media\Entity\Media;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BreweryStaticMap.
 *
 * Fetch static map assets via Google API. Attach image to a brewery entity
 * through a media item reference.
 */
class BreweryStaticMap implements ContainerInjectionInterface {

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
   * The Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Brewery Entity.
   *
   * @var \Drupal\brewery\Entity\BreweryInterface
   */
  protected $brewery = NULL;

  /**
   * Media Element.
   *
   * @var \Drupal\media\Entity\Media
   */
  protected $mediaElement = NULL;

  /**
   * Generate Google Static Map and return the created Media Entity.
   *
   * @param \Drupal\brewery\Entity\Brewery $brewery
   *   A brewery entity to process.
   *
   * @return bool|\Drupal\media\Entity\Media
   *   The media entity created that stores the static map.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function generateStaticMapForBrewery(Brewery $brewery) {

    $this->brewery = $brewery;
    $lat = $this->brewery->get('field_geolocation')->first()->getValue()['lat'];
    $lon = $this->brewery->get('field_geolocation')->first()->getValue()['lon'];

    try {
      $url = "https://maps.googleapis.com/maps/api/staticmap?center={$lat},{$lon}&zoom=12&size=400x200";
      return $this->addMediaFromUrl($url);
    }
    catch (EntityStorageException $e) {
      return FALSE;
    }
  }

  /**
   * Create a media entity of type image from a remote image url.
   *
   * @param string $url
   *   The fully qualified path to the remote file.
   *
   * @return \Drupal\media\Entity\Media
   *   The created media element.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addMediaFromUrl(string $url): Media {
    if ($file = $this->createFileFromUrl($url)) {
      $imageMedia = Media::create([
        'bundle' => 'image',
        'uid' => $this->account->id(),
        'langcode' => $this->languageManager->getDefaultLanguage()->getId(),
        'status' => 1,
        'field_media_image' => [
          'target_id' => $file->id(),
        ],
      ]);
      $imageMedia->save();
      $this->mediaElement = $imageMedia;
    }
    return $this->mediaElement;
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
    $filesDestination = file_default_scheme() . '://staticmap/' . $this->brewery->label() . '.png';
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
   * Constructs a new BreweryStaticMap.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user account service.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(AccountProxyInterface $account, FileSystemInterface $fileSystem, LanguageManagerInterface $languageManager) {
    $this->account = $account;
    $this->fileSystem = $fileSystem;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Session\AccountProxyInterface $account */
    $account = $container->get('current_user');
    /* @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = $container->get('file_system');
    /* @var \Drupal\Core\Language\LanguageManagerInterface $languageManager */
    $languageManager = $container->get('language_manager');
    return new static(
      $account,
      $fileSystem,
      $languageManager
    );
  }

}

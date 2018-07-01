<?php

namespace Drupal\brewery_staticmap;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\brewery\Entity\Brewery;
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
   * The File System service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a new BreweryStaticMap.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   */
  public function __construct(FileSystemInterface $fileSystem) {
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = $container->get('file_system');
    return new static(
      $fileSystem
    );
  }

}

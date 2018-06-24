<?php

namespace Drupal\brewery\Entity;

use Drupal\custom_entity_tools\Entity\EntityBaseInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * Provides an interface for defining Brewery entities.
 *
 * @ingroup brewery
 */
interface BreweryInterface extends EntityBaseInterface {

  /**
   * Get values for Geolocation field of the brewery entity.
   *
   * @return array|null
   *   The field->getValue return array for the field.
   */
  public function getGeolocation(): ?array;

  /**
   * Get values for Date First Visit field of the brewery entity.
   *
   * @return array|null
   *   The field->getValue return array for the field.
   */
  public function getDateVisit(): ?array;

  /**
   * Get values for Address field of the brewery entity.
   *
   * @return array|null
   *   The field->getValue return array for the field.
   */
  public function getAddress(): ?array;

  /**
   * Get values for Types field of the brewery entity.
   *
   * @return array|null
   *   An array of brewery type field values;
   */
  public function getTypes(): ?array;

  /**
   * Get values for Image field of the brewery entity.
   *
   * @return array|null
   *   The field->getValue return array for the field.
   */
  public function getImageField(): ?MediaInterface;

  /**
   * Get values for Image field of a given Media entity.
   *
   * @param \Drupal\media\MediaInterface $mediaEntity
   *
   * @return array|null
   *   The field->getValue return array for the field.
   */
  public function getFileFieldFromMediaEntity(MediaInterface $mediaEntity): ?FileInterface;

  /**
   * {@inheritdoc}
   */
  public function getImageUrl(string $imageStyle = 'default'): ?string;

}

<?php

namespace Drupal\brewery;

use Drupal\custom_entity_tools\EntityBasePermissions;

/**
 * Provides dynamic permissions for entities of different types.
 */
class BreweryPermissions extends EntityBasePermissions {

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId(): string {
    return 'brewery';
  }

}

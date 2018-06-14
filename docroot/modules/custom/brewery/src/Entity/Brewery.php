<?php

namespace Drupal\brewery\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\custom_entity_tools\Entity\EntityBase;

/**
 * Defines the Brewery entity.
 *
 * @ContentEntityType(
 *   id = "brewery",
 *   label = @Translation("Brewery"),
 *   label_collection = @Translation("Breweries"),
 *   label_singular = @Translation("brewery"),
 *   label_plural = @Translation("breweries"),
 *   label_count = @PluralTranslation(
 *     singular = "@count brewery",
 *     plural = "@count breweries"
 *   ),
 *   handlers = {
 *     "access" = "Drupal\custom_entity_tools\EntityBaseAccessControlHandler",
 *     "form" = {
 *       "default"  = "Drupal\custom_entity_tools\Form\EntityBaseForm",
 *       "add"      = "Drupal\custom_entity_tools\Form\EntityBaseForm",
 *       "edit"     = "Drupal\custom_entity_tools\Form\EntityBaseForm",
 *       "delete"   = "Drupal\custom_entity_tools\Form\EntityBaseDeleteForm",
 *       "settings" = "Drupal\brewery\Form\BrewerySettingsForm"
 *     },
 *     "list_builder" = "Drupal\brewery\BreweryListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\custom_entity_tools\EntityBaseHtmlRouteProvider",
 *     },
 *     "storage" = "Drupal\custom_entity_tools\EntityBaseStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   base_table = "brewery",
 *   data_table = "brewery_field_data",
 *   revision_table = "brewery_revision",
 *   revision_data_table = "brewery_field_revision",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "user_id",
 *   },
 *   translatable = TRUE,
 *   show_revision_ui = TRUE,
 *   common_reference_target = TRUE,
 *   admin_permission = "administer brewery entities",
 *   field_ui_base_route = "entity.brewery.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   links = {
 *     "collection"      = "/admin/content/brewery",
 *     "canonical"       = "/admin/content/brewery/{brewery}",
 *     "add-form"        = "/admin/content/brewery/add",
 *     "edit-form"       = "/admin/content/brewery/{brewery}/edit",
 *     "delete-form"     = "/admin/content/brewery/{brewery}/delete",
 *     "version-history" = "/admin/content/brewery/{brewery}/revisions",
 *     "revision"        = "/admin/content/brewery/{brewery}/revisions/{revision}/view",
 *     "revision-revert" = "/admin/content/brewery/{brewery}/revisions/{revision}/revert",
 *     "revision-delete" = "/admin/content/brewery/{brewery}/revisions/{revision}/delete",
 *   },
 * )
 *
 * @ingroup brewery
 */
class Brewery extends EntityBase implements BreweryInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    return $fields;
  }

}

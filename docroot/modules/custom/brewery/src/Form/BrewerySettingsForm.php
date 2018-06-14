<?php

namespace Drupal\brewery\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_entity_tools\Form\EntityBaseSettingsForm;

/**
 * Class BrewerySettingsForm.
 *
 * @ingroup brewery
 */
class BrewerySettingsForm extends EntityBaseSettingsForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'brewery_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $form['intro']['#markup'] = 'Settings form for managing custom entities.';
    return $form;
  }

}

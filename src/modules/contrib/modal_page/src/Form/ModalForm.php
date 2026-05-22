<?php

namespace Drupal\modal_page\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class: ModalForm.
 */
class ModalForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    $default_language = $entity->getUntranslated()->language()->getId();

    if (!empty($entity->langcode->value)) {
      $default_language = $entity->langcode->value;
    }

    $form['langcode'] = [
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $default_language,
      '#empty_option' => $this->t('- Any -'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    drupal_set_message($this->t('Completed'));
    $form_state->setRedirect('modal_page.default');
    $entity = $this->getEntity();
    $entity->save();
  }

}

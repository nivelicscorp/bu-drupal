<?php

declare(strict_types=1);
/**
 * @file
 * Contains \Drupal\mailchimp_transactional_activity\Form\ActivityDeleteForm.
 */

namespace Drupal\mailchimp_transactional_activity\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Activity entity delete form.
 *
 * @ingroup mailchimp_transactional_activity
 */
class ActivityDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('mailchimp_transactional_activity.admin');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    \Drupal::service('router.builder')->setRebuildNeeded();

    $this->messenger()->addStatus($this->t('Mailchimp Transactional Activity %label has been deleted.', ['%label' => $this->entity->label()]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}

<?php
declare(strict_types=1);

/**
 * @file
 * Contains \Drupal\mandrill_activity\Form\MandrillActivityForm.
 */

namespace Drupal\mandrill_activity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the MandrillActivity entity edit form.
 *
 * @ingroup mandrill_activity
 */
class MandrillActivityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $user_input = $form_state->getUserInput();

    /* @var $activity \Drupal\mandrill_activity\Entity\MandrillActivity */
    $activity = $this->entity;

    $entity_not_null = !empty($activity->id);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $activity->label,
      '#description' => t('The human-readable name of this Mandrill Activity entity.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $activity->id,
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'source' => ['label'],
        'exists' => [$this, 'exists'],
      ],
      '#description' => t('A unique machine-readable name for this Mandrill Activity entity. It must only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$activity->isNew(),
    ];

    $form['drupal_entity'] = [
      '#title' => t('Drupal entity'),
      '#type' => 'fieldset',
      '#attributes' => [
        'id' => ['Mandrill-activity-drupal-entity'],
      ],
      '#prefix' => '<div id="entity-wrapper">',
      '#suffix' => '</div>',
    ];

    // Prep the entity type list before creating the form item:
    $entity_info = \Drupal::entityTypeManager()->getDefinitions();

    $entity_types = ['' => t('-- Select --')];

    /* @var \Drupal\Core\Entity\EntityType $entity_type */
    foreach ($entity_info as $key => $entity_type) {
      // Ignore Mandrill entity types.
      if (strpos($entity_type->id(), 'mandrill') !== FALSE) {
        continue;
      }

      // Ignore configuration entities.
      if (get_class($entity_type) == 'Drupal\Core\Config\Entity\ConfigEntityType') {
        continue;
      }

      $entity_types[$entity_type->id()] = $entity_type->getLabel();
    }

    asort($entity_types);
    $form['drupal_entity']['entity_type'] = [
      '#title' => t('Entity type'),
      '#type' => 'select',
      '#options' => $entity_types,
      '#default_value' => $activity->entity_type,
      '#required' => TRUE,
      '#description' => t('Select an entity type to enable Mandrill history on.'),
      '#ajax' => [
        'callback' => '::entityCallback',
        'wrapper' => 'entity-wrapper',
        'method' => 'replace',
        'effect' => 'fade',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Retrieving bundles for this entity type.'),
        ],
      ],
    ];

    $form_entity_type = $user_input['entity_type'] ?? NULL;
    if (empty($form_entity_type) && $entity_not_null) {
      $form_entity_type = $activity->entity_type;
    }

    if (!empty($form_entity_type)) {
      // Prep the bundle list before creating the form item.
      $bundle_info = \Drupal::service('entity_type.bundle.info')->getBundleInfo($form_entity_type);

      $bundles = ['' => t('-- Select --')];

      foreach ($bundle_info as $key => $bundle) {
        $bundles[$key] = ucfirst($key);
      }

      asort($bundles);
      $form['drupal_entity']['bundle'] = [
        '#title' => t('Entity bundle'),
        '#type' => 'select',
        '#required' => TRUE,
        '#description' => t('Select a Drupal entity bundle with an email address to report on.'),
        '#options' => $bundles,
        '#default_value' => $activity->bundle,
        '#ajax' => [
          'callback' => '::entityCallback',
          'wrapper' => 'entity-wrapper',
          'method' => 'replace',
          'effect' => 'fade',
          'progress' => [
            'type' => 'throbber',
            'message' => t('Retrieving email fields for this entity type.'),
          ],
        ],
      ];

      $form_bundle = $user_input['bundle'] ?? NULL;
      if (empty($form_bundle) && $entity_not_null) {
        $form_bundle = $activity->bundle;
      }

      if (!empty($form_bundle)) {
        $fields = $this->fieldmapOptions($form_entity_type, $form_bundle);
        $form['drupal_entity']['email_property'] = [
          '#title' => t('Email Property'),
          '#type' => 'select',
          '#required' => TRUE,
          '#description' => t('Select the field which contains the email address'),
          '#options' => $fields,
          '#default_value' => $activity->email_property,
        ];
      }
    }

    $form['enabled'] = [
      '#title' => t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => ($entity_not_null) ? $activity->enabled : TRUE,
    ];

    return $form;
  }

  /**
   * AJAX callback handler for MandrillActivityForm.
   */
  public function entityCallback(&$form, FormStateInterface $form_state) {
    return $form['drupal_entity'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->isSubmitted()) {
      $entity = $this->entityTypeManager->getStorage('mandrill_activity')->getQuery()
        ->condition('entity_type', $form_state->getValue('entity_type'))
        ->condition('bundle', $form_state->getValue('bundle'))
        ->execute();

      if (!empty($entity)) {
        $form_state->setErrorByName('bundle', $this->t('A Mandrill Activity Entity already exists for this Bundle.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\mandrill_activity\Entity\MandrillActivity $activity */
    $activity = $this->getEntity();
    $activity->save();

    \Drupal::service('router.builder')->setRebuildNeeded();

    $form_state->setRedirect('mandrill_activity.admin');
  }

  /**
   * Determines if a Mandrill Activity entity exists.
   *
   * @param int $id
   *   The unique ID of the Mandrill Activity entity.
   *
   * @return bool
   *   TRUE if the entity exists.
   */
  public function exists($id) {
    $entity = $this->entityTypeManager->getStorage('mandrill_activity')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

  /**
   * Return all possible Drupal properties for a given entity type.
   *
   * @param string $entity_type
   *   Name of entity whose properties to list.
   * @param string|null $entity_bundle
   *   Entity bundle to get properties for.
   *
   * @return array
   *   List of entities that can be used as an #options list.
   */
  public function fieldmapOptions(string $entity_type, string $entity_bundle = NULL): array {
    $options = ['' => t('-- Select --')];

    $fields =  \Drupal::service('entity_field.manager')->getFieldMap();

    if (isset($fields[$entity_type])) {
      foreach ($fields[$entity_type] as $key => $field) {
        // Limit to email fields.
        if ($field['type'] == 'email') {
          // Check this field appears in the selected entity bundle.
          if (isset($field['bundles'][$entity_bundle])) {
            $options[$key] = $key;
          }
        }
      }
    }

    return $options;
  }

}

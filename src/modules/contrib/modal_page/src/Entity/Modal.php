<?php

namespace Drupal\modal_page\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\modal_page\ModalPageInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the Modal entity.
 *
 * @ingroup modal_page
 *
 * @ContentEntityType(
 *   id = "modal_page_modal",
 *   label = @Translation("Modal entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\modal_page\Entity\Controller\ModalListBuilder",
 *     "form" = {
 *       "add" = "Drupal\modal_page\Form\ModalForm",
 *       "edit" = "Drupal\modal_page\Form\ModalForm",
 *       "delete" = "Drupal\modal_page\Form\ModalDeleteForm",
 *     },
 *     "access" = "Drupal\modal_page\ModalAccessControlHandler",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "modal",
 *   admin_permission = "administer modal_page entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/modal_page_modal/{modal_page_modal}",
 *     "edit-form" = "/modal_page_modal/{modal_page_modal}/edit",
 *     "delete-form" = "/modal_page_modal/{modal_page_modal}/delete",
 *     "collection" = "/modal_page_modal"
 *   },
 *   field_ui_base_route = "modal_page.settings",
 * )
 */
class Modal extends ContentEntityBase implements ModalPageInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Modal entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Modal entity.'))
      ->setReadOnly(TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the Modal.'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['body'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Body'))
      ->setDescription(t('The body of the Modal Entity.'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -5,
        'settings' => [
          'rows' => 11,
        ],
      ])

      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Modal By'))
      ->setRequired(TRUE)
      ->setSettings([
        'allowed_values' => [
          'page' => 'Page',
          'parameter' => 'Parameter',
        ],
      ])
      ->setDefaultValue('page')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $pages_description = t('Pages for the Modal appear');
    $pages_description .= '<ul>';
    $pages_description .= '<li>' . t('Enter one path per line') . '</li>';
    $pages_description .= '<li>' . t('An example path is /home for show in Home Page') . '</li>';
    $pages_description .= '<li>&lt;front&gt;' . t('is the front page') . '</li>';
    $pages_description .= '</ul>';

    $fields['pages'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Pages'))
      ->setDescription($pages_description)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -5,
        'settings' => [
          'rows' => 4,
        ],
      ])

      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $parameters_description = t('Parameters for the Modal appear');
    $parameters_description .= '<ul>';
    $parameters_description .= '<li>' . t('Enter one parameters per line') . '</li>';
    $parameters_description .= '<li>' . t('An example path is welcome for show in this parameter') . '</li>';
    $parameters_description .= '<li>' . t('In URL should be /page?modal=welcome') . '</li>';
    $parameters_description .= '</ul>';

    $fields['parameters'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Parameters'))
      ->setDescription($parameters_description)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -5,
        'settings' => [
          'rows' => 4,
        ],
      ])

      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ok_label_button'] = BaseFieldDefinition::create('string')
      ->setLabel(t('OK Label Button'))
      ->setDescription(t('If blank the value will be <b>OK</b>'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of Modal entity.'));
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}

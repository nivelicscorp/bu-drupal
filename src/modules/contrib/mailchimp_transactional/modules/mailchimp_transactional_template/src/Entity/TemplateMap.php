<?php

declare(strict_types=1);
/**
 * @file
 * Defines Template Map Config Entity.
 */

namespace Drupal\mailchimp_transactional_template\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the TemplateMap entity.
 *
 * @ingroup mailchimp_transactional_template
 *
 * @ConfigEntityType(
 *   id = "mailchimp_transactional_template",
 *   label = @Translation("Mailchimp Transactional Template Map"),
 *   fieldable = FALSE,
 *   handlers = {
 *     "list_builder" = "Drupal\mailchimp_transactional_template\Controller\TemplateMapListBuilder",
 *     "form" = {
 *       "add" = "Drupal\mailchimp_transactional_template\Form\TemplateMapForm",
 *       "edit" = "Drupal\mailchimp_transactional_template\Form\TemplateMapForm",
 *       "delete" = "Drupal\mailchimp_transactional_template\Form\TemplateMapDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer mailchimp_transactional",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   config_export = {
 *    "id",
 *    "label",
 *    "template_name",
 *    "content_area",
 *    "mailsystem_key",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/services/mailchimp_transactional/templates/{mailchimp_transactional_template}",
 *     "delete-form" = "/admin/config/services/mailchimp_transactional/templates/{mailchimp_transactional_template}/delete"
 *   }
 * )
 */
class TemplateMap extends ConfigEntityBase implements TemplateMapInterface {

  /**
   * Unique Mailchimp Transactional Template Map entity machine name.
   *
   * @var string|null
   */
  public $id = NULL;

  /**
   * The human-readable name of the Mailchimp Transactional Template Map.
   *
   * @var string|null
   */
  public $label = NULL;

  /**
   * The unique identifier of the Mailchimp Transactional template in use.
   *
   * @var string|null
   */
  public $template_name = NULL;

  /**
   * The name of the section where primary email content should go.
   *
   * @var string|null
   */
  public $content_area = NULL;

  /**
   * The MailSystem key that is using this map.
   *
   * @var string|null
   */
  public $mailsystem_key = NULL;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

}

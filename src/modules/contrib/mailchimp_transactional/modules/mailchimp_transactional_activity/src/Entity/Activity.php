<?php

declare(strict_types=1);
/**
 * @file
 * Contains \Drupal\mailchimp_transactional_activity\Entity\Activity.
 */

namespace Drupal\mailchimp_transactional_activity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Activity entity.
 *
 * @ingroup mailchimp_transactional_activity
 *
 * @ConfigEntityType(
 *   id = "mailchimp_transactional_activity",
 *   label = @Translation("Mailchimp Transactional Activity"),
 *   fieldable = FALSE,
 *   handlers = {
 *     "list_builder" = "Drupal\mailchimp_transactional_activity\Controller\ActivityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\mailchimp_transactional_activity\Form\ActivityForm",
 *       "edit" = "Drupal\mailchimp_transactional_activity\Form\ActivityForm",
 *       "delete" = "Drupal\mailchimp_transactional_activity\Form\ActivityDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer mailchimp transactional activity",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   config_export = {
 *    "id",
 *    "label",
 *    "entity_type",
 *    "bundle",
 *    "entity_path",
 *    "email_property",
 *    "enabled"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/services/mailchimp_transactional/activity/{mailchimp_transactional_activity}",
 *     "delete-form" = "/admin/config/services/mailchimp_transactional/activity/{mailchimp_transactional_activity}/delete"
 *   }
 * )
 */
class Activity extends ConfigEntityBase implements ActivityInterface {

  /**
   * Unique Mailchimp Transactional Activity entity ID.
   *
   * @var int
   */
  public $id;

  /**
   * The human-readable name of this mailchimp_transactional_activity_entity.
   *
   * @var string
   */
  public $label;

  /**
   * The Drupal entity type (e.g. "node", "user").
   *
   * @var string
   */
  public $entity_type;

  /**
   * The Drupal bundle (e.g. "page", "user")
   *
   * @var string
   */
  public $bundle;

  /**
   * The path to view individual entities of the selected type.
   *
   * @var string
   */
  public $entity_path;

  /**
   * The property that contains the email address to track.
   *
   * @var string
   */
  public $email_property;

  /**
   * Whether or not this Mailchimp Transactional activity stream is enabled.
   *
   * @var bool
   */
  public $enabled;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

}

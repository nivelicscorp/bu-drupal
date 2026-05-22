<?php
declare(strict_types=1);

/**
 * @file
 * Contains \Drupal\mandrill_activity\Plugin\Derivative\MandrillActivityLocalTasks.
 */

namespace Drupal\mandrill_activity\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\mandrill_activity\Entity\MandrillActivity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines dynamic local tasks for Mandrill Activity.
 */
class MandrillActivityLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $activity_ids = \Drupal::entityQuery('mandrill_activity')->execute();

    $entity_definitions = $this->entityTypeManager->getDefinitions();

    $activity_entities = MandrillActivity::loadMultiple($activity_ids);

    /* @var $activity \Drupal\mandrill_activity\Entity\MandrillActivity */
    foreach ($activity_entities as $activity) {
      $entity = $entity_definitions[$activity->entity_type];

      if (!$activity->enabled || empty($entity)) {
        continue;
      }

      // Determine if the entity has a canonical path to add this task to.
      $link_templates = $entity->getLinkTemplates();
      $has_canonical_path = (isset($link_templates['canonical']));

      $task = $activity->entity_type . '.mandrill_activity';

      $this->derivatives[$task] = $base_plugin_definition;
      $this->derivatives[$task]['title'] = 'Mandrill Activity';
      $this->derivatives[$task]['route_name'] = 'entity.' . $activity->entity_type . '.mandrill_activity';
      $this->derivatives[$task]['base_route'] = 'entity.' . $activity->entity_type . (($has_canonical_path) ? '.canonical' : '.edit_form');
    }

    return $this->derivatives;
  }

}

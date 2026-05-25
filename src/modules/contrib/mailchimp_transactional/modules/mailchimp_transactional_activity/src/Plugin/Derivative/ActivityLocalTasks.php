<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional_activity\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines dynamic local tasks for Mailchimp Transactional Activity.
 */
class ActivityLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    $instance = new static();
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $storage = $this->entityTypeManager
      ->getStorage('mailchimp_transactional_activity');
    $activities = $storage->loadMultiple();
    $entity_definitions = $this->entityTypeManager->getDefinitions();

    /** @var \Drupal\mailchimp_transactional_activity\Entity\Activity $activity */
    foreach ($activities as $activity) {
      $entity = $entity_definitions[$activity->entity_type];

      if (!$activity->enabled || empty($entity)) {
        continue;
      }

      // Determine if the entity has a canonical path to add this task to.
      $link_templates = $entity->getLinkTemplates();
      $has_canonical_path = (isset($link_templates['canonical']));

      $task = $activity->entity_type . '.activity';

      $this->derivatives[$task] = $base_plugin_definition;
      $this->derivatives[$task]['title'] = 'Mailchimp Transactional Activity';
      $this->derivatives[$task]['route_name'] = 'entity.' . $activity->entity_type . '.activity';
      $this->derivatives[$task]['base_route'] = 'entity.' . $activity->entity_type . (($has_canonical_path) ? '.canonical' : '.edit_form');
    }

    return $this->derivatives;
  }

}

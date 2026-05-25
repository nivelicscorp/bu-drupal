<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional_activity\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mailchimp_transactional_activity\Entity\Activity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Defines dynamic routes for Mailchimp Transactional Activity entities.
 *
 * This allows Mailchimp Transactional activity to be displayed on any entity.
 */
class ActivityRoutes implements ContainerInjectionInterface {

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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routes = [];

    $activity_ids = $this->entityTypeManager->getStorage('mailchimp_transactional_activity')->getQuery()->accessCheck(TRUE)->execute();

    $activity_entities = Activity::loadMultiple($activity_ids);

    /** @var \Drupal\mailchimp_transactional_activity\Entity\Activity $activity */
    foreach ($activity_entities as $activity) {
      if (!$activity->enabled) {
        continue;
      }

      $routes['entity.' . $activity->entity_type . '.activity'] = new Route(
        // Route path.
        $activity->entity_type . '/{' . $activity->entity_type . '}/activity',
        // Route defaults.
        [
          '_controller' => '\Drupal\mailchimp_transactional_activity\Controller\ActivityController::overview',
          '_title' => 'Mailchimp Transactional Activity',
        ],
        // Route requirements.
        [
          '_permission'  => 'view mailchimp transactional activity',
        ]
      );
    }

    return $routes;
  }

}

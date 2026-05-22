<?php

declare(strict_types=1);
/**
 * @file
 * Contains \Drupal\mailchimp_transactional_activity\Routing\ActivityRoutes.
 */

namespace Drupal\mailchimp_transactional_activity\Routing;

use Drupal\mailchimp_transactional_activity\Entity\Activity;
use Symfony\Component\Routing\Route;

/**
 * Defines dynamic routes for Mailchimp Transactional Activity entities.
 *
 * This allows Mailchimp Transactional activity to be displayed on any entity.
 */
class ActivityRoutes {

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routes = [];

    $activity_ids = \Drupal::entityQuery('mailchimp_transactional_activity')
      ->execute();

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

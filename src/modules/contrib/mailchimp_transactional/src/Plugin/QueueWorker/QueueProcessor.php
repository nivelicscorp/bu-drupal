<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sends queued mail messages.
 *
 * @QueueWorker(
 *   id = "mailchimp_transactional_queue",
 *   title = @Translation("Sends queued mail messages"),
 *   cron = {"time" = 60}
 * )
 */
class QueueProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    /** @var \Drupal\mailchimp_transactional\Service $mailchimp_transactional */
    $mailchimp_transactional = \Drupal::service('mailchimp_transactional.service');

    $mailchimp_transactional->send($data['message']);
  }

}

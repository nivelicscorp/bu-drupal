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
   * The file system service.
   *
   * @var \Drupal\mailchimp_transactional_template\TemplateService
   */
  protected $mailchimpTransactional;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->mailchimpTransactional = $container->get('mailchimp_transactional.service');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->mailchimpTransactional->send($data['message']);
  }

}

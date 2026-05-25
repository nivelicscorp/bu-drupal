<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional\Plugin\Mail;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Mailchimp Transactional test mail plugin.
 *
 * @Mail(
 *   id = "mailchimp_transactional_test_mail",
 *   label = @Translation("Mailchimp Transactional test mailer"),
 *   description = @Translation("Sends test messages through Mailchimp Transactional.")
 * )
 */
class TestMail extends TransactionMail {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->mailchimpTransactional = $container->get('mailchimp_transactional.test.service');

    return $instance;
  }

}

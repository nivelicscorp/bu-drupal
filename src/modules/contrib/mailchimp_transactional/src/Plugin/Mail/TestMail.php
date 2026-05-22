<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional\Plugin\Mail;

/**
 * Mailchimp Transactional test mail plugin.
 *
 * @Mail(
 *   id = "mailchimp_transactional_test_mail",
 *   label = @Translation("Mailchimp Transactional test mailer"),
 *   description = @Translation("Sends test messages through Mailchimp Transactional.")
 * )
 */
class TestMail extends Mail {

  /**
   * Constructor.
   */
  public function __construct() {
    parent::__construct();

    $this->mailchimpTransactional = \Drupal::service('mailchimp_transactional.test.service');
  }

}

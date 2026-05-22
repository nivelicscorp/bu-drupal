<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\Access\AccessInterface;

/**
 * Checks access for displaying configuration page.
 */
class MailerAccessCheck implements AccessInterface {

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Class constructor.
   */
  public function __construct(ConfigFactoryInterface $config) {
    $this->config = $config;
  }

  /**
   * Access check for Mailchimp Transactional module configuration.
   *
   * Ensures a Mailchimp Transactional API key has been provided.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access() {
    $config = $this->config->get('mailsystem.settings');
    $sender = $config->get('defaults.sender');

    return AccessResult::allowedIf(in_array($sender, [
      'mailchimp_transactional_mail',
      'mailchimp_transactional_test_mail',
    ]));
  }

}

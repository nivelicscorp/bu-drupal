<?php

declare(strict_types=1);


namespace Drupal\mailchimp_transactional\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\Access\AccessInterface;

/**
 * Checks access for displaying configuration page.
 */
class ConfigurationAccessCheck implements AccessInterface {

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
    $config = $this->config->get('mailchimp_transactional.settings');
    $api_key = $config->get('mailchimp_transactional_api_key');

    return AccessResult::allowedIf(!empty($api_key));
  }

}

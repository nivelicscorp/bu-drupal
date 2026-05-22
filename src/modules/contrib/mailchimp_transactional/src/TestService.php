<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;

/**
 * Test Mailchimp Transactional service.
 */
class TestService extends Service {

  /**
   * The state store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs the service.
   *
   * @param \Drupal\mailchimp_transactional\APIInterface $mailchimp_transactional_api
   *   The Mailchimp Transactional api service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory Service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state store.
   */
  public function __construct(APIInterface $mailchimp_transactional_api, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory, ModuleHandlerInterface $module_handler, StateInterface $state) {
    parent::__construct($mailchimp_transactional_api, $config_factory, $logger_factory, $module_handler);
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  protected function handleSendResponse(array $response, array $message) {
    if (isset($response['status'])) {
      // There was a problem sending the message.
      return FALSE;
    }

    foreach ($response as $result) {
      // Allow other modules to react based on a send result.
      $this->moduleHandler->invokeAll('mailchimp_transactional_mailsend_result', [$result], [$message]);
      switch ($result['status']) {
        case 'error':
        case 'invalid':
        case 'rejected':
          return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Replaces Mailchimp Transactional API call, currently still sends email.
   *
   * {@inheritdoc}
   *
   * @todo refactor testing needs into a test/module
   */
  public function send($message) {
    if ($this->config->get('system.mail')->get('interface.default') === 'test_mail_collector') {
      // We're running inside a functional test.
      $captured_emails = $this->state->get('system.test_mail_collector') ?: [];
      $captured_emails[] = $message;
      $this->state->set('system.test_mail_collector', $captured_emails);
      return TRUE;
    }
    return parent::send($message);
  }

}

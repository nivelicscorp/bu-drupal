<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Mailchimp Transactional Service.
 */
class Service implements ServiceInterface {

  /**
   * The Mailchimp Transactional API service.
   *
   * @var \Drupal\mailchimp_transactional\APIInterface
   */
  protected $mailchimpTransactionalAPI;

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The Logger Factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $log;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   */
  public function __construct(APIInterface $mailchimp_transactional_api, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory, ModuleHandlerInterface $module_handler) {
    $this->mailchimpTransactionalAPI = $mailchimp_transactional_api;
    $this->config = $config_factory;
    $this->log = $logger_factory->get('mailchimp_transactional');
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getMailSystems() {
    $systems = [];
    // Check if the system wide sender or formatter is Mailchimp Transactional.
    $mail_system_config = $this->config->get('mailsystem.settings');
    $systems[] = [
      'key' => 'default',
      'sender' => $mail_system_config->get('defaults')['sender'],
      'formatter' => $mail_system_config->get('defaults')['formatter'],
    ];
    // Check all custom configured modules if any uses Mailchimp Transactional.
    $modules = $mail_system_config->get('modules') ?: [];
    foreach ($modules as $module => $configuration) {
      foreach ($configuration as $key => $settings) {
        $systems[] = [
          'key' => "$module-$key",
          'sender' => $settings['sender'],
          'formatter' => $settings['formatter'],
        ];
      }
    }
    return $systems;
  }

  /**
   * {@inheritdoc}
   */
  public function getReceivers($receivers) {
    // Check the input variable type to provide backward compatibility for
    // when only a string of 'to' recipients are passed.
    if (is_string($receivers)) {
      $receivers = [
        'to' => $receivers,
      ];
    }
    $recipients = [];
    foreach ($receivers as $type => $receiver) {
      $receiver_array = explode(',', $receiver);
      foreach ($receiver_array as $email) {
        if (preg_match(MailchimpTransactionalInterface::MAILCHIMP_TRANSACTIONAL_EMAIL_REGEX, $email, $matches)) {
          $recipients[] = [
            'email' => $matches[2],
            'name' => $matches[1],
            'type' => $type,
          ];
        }
        else {
          $recipients[] = [
            'email' => $email,
            'type' => $type,
          ];
        }
      }
    }
    return $recipients;
  }

  /**
   * {@inheritdoc}
   */
  public function send($message) {
    try {
      $response = $this->mailchimpTransactionalAPI->send(['message' => $message]);

      return $this->handleSendResponse($response, $message);
    }
    catch (\Exception $e) {
      $this->log->error('Error sending email from %from to %to. @code: @message', [
        '%from' => $message['from_email'],
        '%to' => $message['to'],
        '@code' => $e->getCode(),
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Response handler for sent messages.
   *
   * @param array $response
   *   Response from the Mailchimp Transactional API.
   * @param array $message
   *   The sent message.
   *
   * @return bool
   *   TRUE if the message was sent or queued without error.
   */
  protected function handleSendResponse(array $response, array $message) {
    if (!isset($response['status'])) {
      foreach ($response as $result) {
        // Allow other modules to react based on a send result.
        $this->moduleHandler->invokeAll('mailchimp_transactional_mailsend_result',
          [$result, $message]
        );
        switch ($result->status) {
          case 'error':
          case 'invalid':
          case 'rejected':
            $to = $result->email ?? 'recipient';
            $status = $result->status ?? 'message';
            $error_message = $result->message ?? 'no message';
            if (!isset($result->message) && isset($result->reject_reason)) {
              $error_message = $result->reject_reason;
            }

            $this->log->error('Failed sending email from %from to %to. @status: @message', [
              '%from' => $message['from_email'],
              '%to' => $to,
              '@status' => $status,
              '@message' => $error_message,
            ]);
            return FALSE;

          case 'queued':
            $this->log->info('Email from %from to %to queued by Mailchimp Transactional App.', [
              '%from' => $message['from_email'],
              '%to' => $result->email,
            ]);
            break;
        }
      }
    }
    else {
      $this->log->warning('Mail send failed with status %status: code %code, %name, %message', [
        '%status' => $response['status'],
        '%code' => $response['code'],
        '%name' => $response['name'],
        '%message' => $response['message'],
      ]);
      return FALSE;
    }
    return TRUE;
  }

}

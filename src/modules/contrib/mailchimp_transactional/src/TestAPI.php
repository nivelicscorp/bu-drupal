<?php

declare(strict_types=1);
namespace Drupal\mailchimp_transactional;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Egulias\EmailValidator\EmailValidator;
use GuzzleHttp\Client;

/**
 * Overrides functions in the Mailchimp Transactional API service for testing.
 */
class TestAPI extends API {
  /**
   * The email validator.
   *
   * @var \Egulias\EmailValidator\EmailValidator
   */
  protected $emailValidator;

  /**
   * Class constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory, Client $http_client, MessengerInterface $messenger, EmailValidator $email_validator) {
    parent::__construct($config_factory, $logger_factory, $http_client, $messenger);
    $this->emailValidator = $email_validator;
  }

  /**
   * {@inheritdoc}
   */
  public function getMessages($email): array {
    $matched_messages = [];

    $query_key = 'email';
    $query_value = $email;

    $messages = $this->getTestMessagesData();

    foreach ($messages as $message) {
      if (isset($message[$query_key]) && ($message[$query_key] == $query_value)) {
        $matched_messages[] = $message;
      }
    }

    return $matched_messages;
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplates(): array {
    return $this->getTestTemplatesData();
  }

  /**
   * {@inheritdoc}
   */
  public function getSubAccounts(): array {
    $subaccounts = [];

    // Test Customer One.
    $subaccount = [
      'id' => 'test-customer-1',
      'name' => 'Test Customer One',
      'custom_quota' => 42,
      'status' => 'active',
      'reputation' => 42,
      'created_at' => '2013-01-01 15:30:27',
      'first_sent_at' => '2013-01-01 15:30:29',
      'sent_weekly' => 42,
      'sent_monthly' => 42,
      'sent_total' => 42,
    ];

    $subaccounts[] = $subaccount;

    // Test Customer Two.
    $subaccount = [
      'id' => 'test-customer-2',
      'name' => 'Test Customer Two',
      'custom_quota' => 42,
      'status' => 'active',
      'reputation' => 42,
      'created_at' => '2013-01-01 15:30:27',
      'first_sent_at' => '2013-01-01 15:30:29',
      'sent_weekly' => 42,
      'sent_monthly' => 42,
      'sent_total' => 42,
    ];

    $subaccounts[] = $subaccount;

    return $subaccounts;
  }

  /**
   * {@inheritdoc}
   */
  public function getUser(): ?object {
    return $this->getUserTestData();
  }

  /**
   * {@inheritdoc}
   */
  public function getTagsAllTimeSeries(): array {
    $time_series = [];

    $tags = $this->getTagsTestData();

    foreach ($tags as $tag) {
      $stats = $tag['stats']['last_30_days'];

      if (!isset($time_series_data)) {
        $time_series_data = $stats;
        $time_series_data['time'] = date('Y-m-d H:i:s', time());
      }
      else {
        $time_series_data['sent'] += $stats['sent'];
        $time_series_data['hard_bounces'] += $stats['hard_bounces'];
        $time_series_data['soft_bounces'] += $stats['soft_bounces'];
        $time_series_data['rejects'] += $stats['rejects'];
        $time_series_data['complaints'] += $stats['complaints'];
        $time_series_data['unsubs'] += $stats['unsubs'];
        $time_series_data['opens'] += $stats['opens'];
        $time_series_data['unique_opens'] += $stats['unique_opens'];
        $time_series_data['clicks'] += $stats['clicks'];
        $time_series_data['unique_clicks'] += $stats['unique_clicks'];
      }

      $time_series[] = $time_series_data;
    }

    return $time_series;
  }

  /**
   * {@inheritdoc}
   */
  public function sendTemplate(array $message, $template_id, array $template_content): array {
    if (!isset($message['to']) || empty($message['to'])) {
      return $this->getErrorResponse(12, 'ValidationError', 'No recipients defined.');
    }

    $response = [];

    $templates = $this->getTemplates();

    $matched_template = NULL;
    foreach ($templates as $template) {
      if ($template['name'] == $template_id) {
        $matched_template = $template;
      }
    }

    if ($matched_template == NULL) {
      return $this->getErrorResponse(12, 'Unknown_Template', 'No template with name: ' . $template_id);
    }

    $recipients = mailchimp_transactional_get_to($message['to']);

    foreach ($recipients as $recipient) {
      $recipient_response = [
        'email' => $recipient['email'],
        'status' => 'sent',
        'reject_reason' => '',
        '_id' => uniqid(),
      ];

      $response[] = $recipient_response;
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function send(array $message): array {
    if (empty($message['to'])) {
      return $this->getErrorResponse(12, 'ValidationError', 'No recipients defined.');
    }

    $response = [];

    foreach ($message['to'] as $recipient) {
      $recipient_response = [
        'email' => $recipient['email'],
        'status' => '',
        'reject_reason' => '',
        '_id' => uniqid(),
      ];

      if ($this->emailValidator->isValid($recipient['email'])) {
        $recipient_response['status'] = 'sent';
      }
      else {
        $recipient_response['status'] = 'invalid';
      }

      $response[] = $recipient_response;
    }

    return $response;
  }

  /**
   * Gets a Mailchimp Transactional-style formatted error response.
   *
   * @param int $code
   *   The Mailchimp Transactional error code.
   * @param string $name
   *   The name of the Mailchimp Transactional error type.
   * @param string $message
   *   The error message.
   *
   * @return array
   *   Formatted error response.
   */
  protected function getErrorResponse(int $code, string $name, string $message): array {
    return [
      'status' => 'error',
      'code' => $code,
      'name' => $name,
      'message' => $message,
    ];
  }

  /**
   * Gets an array of messages used in tests.
   */
  protected function getTestMessagesData(): array {
    $messages = [];

    $message = [
      'ts' => 1365190000,
      '_id' => 'abc123abc123abc123abc123',
      'sender' => 'sender@example.com',
      'template' => 'test-template',
      'subject' => 'Test Subject',
      'email' => 'recipient@example.com',
      'tags' => [
        'test-tag',
      ],
      'opens' => 42,
      'opens_detail' => [
        'ts' => 1365190001,
        'ip' => '55.55.55.55',
        'location' => 'Georgia, US',
        'ua' => 'Linux/Ubuntu/Chrome/Chrome 28.0.1500.53',
      ],
      'clicks' => 42,
      'clicks_detail' => [
        'ts' => 1365190001,
        'url' => 'https://www.example.com',
        'ip' => '55.55.55.55',
        'location' => 'Georgia, US',
        'ua' => 'Linux/Ubuntu/Chrome/Chrome 28.0.1500.53',
      ],
      'state' => 'sent',
      'metadata' => [
        'user_id' => 123,
        'website' => 'www.example.com',
      ],
    ];

    $messages[] = $message;

    return $messages;
  }

  /**
   * Gets an array of templates used in tests.
   */
  protected function getTestTemplatesData(): array {
    $templates = [];

    $template = [
      'slug' => 'test-template',
      'name' => 'Test Template',
      'labels' => [
        'test-label',
      ],
      'code' => '<div>editable content</div>',
      'subject' => 'Test Subject',
      'from_email' => 'admin@example.com',
      'from_name' => 'Admin',
      'text' => 'Test text',
      'publish_name' => 'Test Template',
      'publish_code' => '<div>different than draft content</div>',
      'publish_subject' => 'Test Publish Subject',
      'publish_from_email' => 'admin@example.com',
      'publish_from_name' => 'Test Publish Name',
      'publish_text' => 'Test publish text',
      'published_at' => '2013-01-01 15:30:40',
      'created_at' => '2013-01-01 15:30:27',
      'updated_at' => '2013-01-01 15:30:49',
    ];

    $templates[] = $template;

    return $templates;
  }

  /**
   * Gets user data used in tests.
   */
  protected function getUserTestData(): object {
    $stats_data = [
      'sent' => 42,
      'hard_bounces' => 42,
      'soft_bounces' => 42,
      'rejects' => 42,
      'complaints' => 42,
      'unsubs' => 42,
      'opens' => 42,
      'unique_opens' => 42,
      'clicks' => 42,
      'unique_clicks' => 42,
    ];

    $stats = [
      'today' => $stats_data,
      'last_7_days' => $stats_data,
      'last_30_days' => $stats_data,
      'last_60_days' => $stats_data,
      'last_90_days' => $stats_data,
      'all_time' => $stats_data,
    ];

    return (object) [
      'username' => 'testuser',
      'created_at' => '2013-01-01 15:30:27',
      'public_id' => 'aaabbbccc112233',
      'reputation' => 42,
      'hourly_quota' => 42,
      'backlog' => 42,
      'stats' => $stats,
    ];
  }

  /**
   * Gets an array of tags used in tests.
   */
  protected function getTagsTestData(): array {
    $tags = [];

    $stats_data = [
      'sent' => 42,
      'hard_bounces' => 42,
      'soft_bounces' => 42,
      'rejects' => 42,
      'complaints' => 42,
      'unsubs' => 42,
      'opens' => 42,
      'unique_opens' => 42,
      'clicks' => 42,
      'unique_clicks' => 42,
    ];

    $stats = [
      'today' => $stats_data,
      'last_7_days' => $stats_data,
      'last_30_days' => $stats_data,
      'last_60_days' => $stats_data,
      'last_90_days' => $stats_data,
    ];

    // Test Tag One.
    $tag = [
      'tag' => 'test-tag-one',
      'reputation' => 42,
      'sent' => 42,
      'hard_bounces' => 42,
      'soft_bounces' => 42,
      'rejects' => 42,
      'complaints' => 42,
      'unsubs' => 42,
      'opens' => 42,
      'clicks' => 42,
      'unique_opens' => 42,
      'unique_clicks' => 42,
      'stats' => $stats,
    ];

    $tags[] = $tag;

    // Test Tag Two.
    $tag = [
      'tag' => 'test-tag-two',
      'reputation' => 42,
      'sent' => 42,
      'hard_bounces' => 42,
      'soft_bounces' => 42,
      'rejects' => 42,
      'complaints' => 42,
      'unsubs' => 42,
      'opens' => 42,
      'clicks' => 42,
      'unique_opens' => 42,
      'unique_clicks' => 42,
      'stats' => $stats,
    ];

    $tags[] = $tag;

    return $tags;
  }

}

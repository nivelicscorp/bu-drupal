<?php

declare(strict_types=1);

namespace Drupal\Tests\mailchimp_transactional\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\mailchimp_transactional\MailchimpTransactionalInterface;

/**
 * Tests core Mailchimp Transactional functionality.
 */
abstract class TestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['mailchimp_transactional'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Permissions required to access admin pages.
   *
   * @var array
   */
  protected $permissions = [
    'administer mailchimp transactional',
  ];

  /**
   * Pre-test setup function.
   *
   * Enables dependencies.
   * Sets the mailchimp_transactional_api_key variable to the test key.
   */
  protected function setUp(): void {
    parent::setUp();

    $config = $this->config('mailchimp_transactional.settings');
    $config->set('mailchimp_transactional_from_email', 'foo@bar.com');
    $config->set('mailchimp_transactional_from_name', 'foo');
    $config->set('mailchimp_transactional_api_key', MailchimpTransactionalInterface::MAILCHIMP_TRANSACTIONAL_TEST_API_KEY);
    $config->save();
  }

  /**
   * Gets message data used in tests.
   *
   * @return array
   *   Mock message data formatted to match what the API should return.
   */
  protected function getMessageTestData(): array {
    return [
      'id' => 'unique_id',
      'module' => NULL,
      'body' => '<p>Mail content</p>',
      'subject' => 'Mail Subject',
      'from_email' => 'sender@example.com',
      'from_name' => 'Test Sender',
    ];
  }

}

<?php

declare(strict_types=1);

/**
 * @file
 * Test class and methods for the Mailchimp Transactional Activity module.
 */

namespace Drupal\Tests\mailchimp_transactional_activity\Functional;

use Drupal\Tests\mailchimp_transactional\Functional\TestBase;

/**
 * Test Mailchimp Transactional Activity functionality.
 *
 * @group mailchimp_transactional
 */
class ActivityTest extends TestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'mailchimp_transactional',
    'mailchimp_transactional_activity',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that the Test API returns messages for `recipient@example.com`.
   *
   * Also makes sure that the returned message data also contains that email.
   */
  public function testGetActivity() {
    $email = 'recipient@example.com';

    /** @var \Drupal\mailchimp_transactional\TestAPI $mailchimp_transactional_api */
    $mailchimp_transactional_api = \Drupal::service('mailchimp_transactional.test');

    $activity = $mailchimp_transactional_api->getMessages($email);

    $this->assertNotEmpty($activity, 'Tested retrieving activity.');

    if (!empty($activity) && is_array($activity)) {
      foreach ($activity as $message) {
        $this->assertEquals($message['email'], $email, 'Tested valid message: ' . $message['subject']);
      }
    }
  }

}

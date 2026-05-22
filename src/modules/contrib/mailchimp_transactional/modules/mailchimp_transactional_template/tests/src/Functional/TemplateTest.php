<?php

declare(strict_types=1);

/**
 * @file
 * Test class and methods for the Mailchimp Transactional Template module.
 */

namespace Drupal\Tests\mailchimp_transactional_template\Functional;

use Drupal\Tests\mailchimp_transactional\Functional\TestBase;

/**
 * Test Mailchimp Transactional Template functionality.
 *
 * @group mailchimp_transactional
 */
class TemplateTest extends TestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'mailchimp_transactional',
    'mailchimp_transactional_template',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests getting a list of templates for a given label.
   */
  public function testGetTemplates() {
    /** @var \Drupal\mailchimp_transactional\TestAPI $mailchimp_transactional_api */
    $mailchimp_transactional_api = \Drupal::service('mailchimp_transactional.test');

    $templates = $mailchimp_transactional_api->getTemplates();
    $this->assertNotEmpty($templates, 'Tested retrieving templates.');

    if (!empty($templates) && is_array($templates)) {
      foreach ($templates as $template) {
        $this->assertNotEmpty($template['name'], 'Tested valid template: ' . $template['name']);
      }
    }
  }

  /**
   * Test sending a templated message to multiple recipients.
   */
  public function testSendTemplatedMessage() {
    $to = 'Recipient One <recipient.one@example.com>,'
      . 'Recipient Two <recipient.two@example.com>,'
      . 'Recipient Three <recipient.three@example.com>';

    $message = $this->getMessageTestData();
    $message['to'] = $to;

    $template_name = 'Test Template';
    $template_content = [
      'name' => 'Recipient',
    ];

    /** @var \Drupal\mailchimp_transactional\TestAPI $mailchimp_transactional_api */
    $mailchimp_transactional_api = \Drupal::service('mailchimp_transactional.test');

    $response = $mailchimp_transactional_api->sendTemplate($message, $template_name, $template_content);

    $this->assertNotNull($response, 'Tested response from sending templated message.');

    if (isset($response['status'])) {
      $this->assertNotEquals('error', $response['status'], 'Tested response status: ' . $response['status'] . ', ' . $response['message']);
    }
  }

  /**
   * Test sending a templated message using an invalid template.
   */
  public function testSendTemplatedMessageInvalidTemplate() {
    $to = 'Recipient One <recipient.one@example.com>';

    $message = $this->getMessageTestData();
    $message['to'] = $to;

    $template_name = 'Invalid Template';
    $template_content = [
      'name' => 'Recipient',
    ];

    /** @var \Drupal\mailchimp_transactional\TestAPI $mailchimp_transactional_api */
    $mailchimp_transactional_api = \Drupal::service('mailchimp_transactional.test');

    $response = $mailchimp_transactional_api->sendTemplate($message, $template_name, $template_content);

    $this->assertNotNull($response, 'Tested response from sending invalid templated message.');

    if (isset($response['status'])) {
      $this->assertEquals('error', $response['status'], 'Tested response status: ' . $response['status'] . ', ' . $response['message']);
    }
  }

}

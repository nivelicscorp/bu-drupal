<?php

/**
 * @file
 * Contains \Drupal\mandrill\Tests\MandrillTestCase.
 */

namespace Drupal\Tests\mandrill\Functional;

use Drupal\mandrill\Plugin\Mail\MandrillTestMail;
use Drupal\Tests\BrowserTestBase;

/**
 * Test core Mandrill functionality.
 *
 * @group mandrill
 */
class MandrillTestCase extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['mandrill'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Pre-test setup function.
   *
   * Enables dependencies.
   * Sets the mandrill_api_key variable to the test key.
   */
  protected function setUp() {
    parent::setUp();
    $config = \Drupal::service('config.factory')->getEditable('mandrill.settings');
    $config->set('mandrill_from_email', 'foo@bar.com');
    $config->set('mandrill_from_name', 'foo');
    $config->set('mandrill_api_key', 'MANDRILL_TEST_API_KEY');
    $config->save();
  }

  /**
   * Tests sending a message to multiple recipients.
   */
  public function testSendMessage() {
    $mail_system = $this->getMandrillMail();
    $message = $this->getMessageTestData();
    $message['to'] = 'Recipient One <recipient.one@example.com>,' . 'Recipient Two <recipient.two@example.com>,' . 'Recipient Three <recipient.three@example.com>';
    $response = $mail_system->mail($message);
    $this->assertTrue($response, 'Tested sending message to multiple recipients.');
  }

  /**
   * Tests sending a message to an invalid recipient.
   */
//  public function testSendMessageInvalidRecipient() {
//    $mailSystem = $this->getMandrillMail();
//    $message = $this->getMessageTestData();
//    $message['to'] = 'Recipient One <recipient.one>';
//    $response = $mailSystem->mail($message);
//    $this->assertFalse($response, 'Tested sending message to an invalid recipient.');
//  }

  /**
   * Tests sending a message to no recipients.
   */
//  public function testSendMessageNoRecipients() {
//    $mailSystem = $this->getMandrillMail();
//    $message = $this->getMessageTestData();
//    $message['to'] = '';
//    $response = $mailSystem->mail($message);
//    $this->assertFalse($response, 'Tested sending message to no recipients.');
//  }

  /**
   * Tests getting a list of subaccounts.
   */
  public function testGetSubAccounts() {
    $mandrill_api = \Drupal::service('mandrill.test.api');
    $sub_accounts = $mandrill_api->getSubAccounts();
    $this->assertTrue(!empty($sub_accounts), 'Tested retrieving sub-accounts.');
    if (!empty($sub_accounts) && is_array($sub_accounts)) {
      foreach ($sub_accounts as $sub_account) {
        $this->assertTrue(!empty($sub_account['name']), 'Tested valid sub-account: ' . $sub_account['name']);
      }
    }
  }

  /**
   * Get the Mandrill Mail test plugin.
   *
   * @return \Drupal\mandrill\Plugin\Mail\MandrillTestMail
   */
  private function getMandrillMail() {
    return new MandrillTestMail();
  }

  /**
   * Gets message data used in tests.
   *
   * @return array
   */
  private function getMessageTestData() {
    return [
      'id' => 1,
      'module' => NULL,
      'body' => '<p>Mail content</p>',
      'subject' => 'Mail Subject',
      'from_email' => 'sender@example.com',
      'from_name' => 'Test Sender',
    ];
  }

}

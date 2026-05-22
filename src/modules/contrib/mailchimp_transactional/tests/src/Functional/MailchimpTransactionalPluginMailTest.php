<?php

declare(strict_types=1);

use Drupal\mailchimp_transactional\Plugin\Mail\TestMail;
use Drupal\Tests\mailchimp_transactional\Functional\TestBase;

/**
 * Test that the mail plugin behaves reliably.
 *
 * @group mailchimp_transactional
 */
class MailchimpTransactionalPluginMailTest extends TestBase {

  /**
   * Tests successful traversal through the Mail Plugin code.
   *
   * Proves message array must contain id, module, body, subject and to before
   * it can persist beyond Mail->mail().
   *
   * @todo doesn't need to be a functional test, refactor.
   */
  public function testMailPluginMailFunction() {
    $mail_system = $this->getMailchimpTransactionalMail();
    $message = $this->getMessageTestData();
    $message['to'] =
      'Recipient One <recipient.one@example.com>,' .
      'Recipient Two <recipient.two@example.com>,' .
      'Recipient Three <recipient.three@example.com>';
    $response = $mail_system->mail($message);
    $this->assertTrue($response, 'Tested sending message to multiple recipients.');
  }

  /**
   * Get the Mailchimp Transactional Mail test plugin.
   *
   * @return \Drupal\mailchimp_transactional\Plugin\Mail\TestMail
   *   Mail but with mock data instead of real API data, via TestService.
   */
  private function getMailchimpTransactionalMail() {
    return new TestMail();
  }

}

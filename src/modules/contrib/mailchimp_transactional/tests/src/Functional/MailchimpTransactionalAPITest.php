<?php

declare(strict_types=1);

use Drupal\Tests\mailchimp_transactional\Functional\TestBase;

/**
 * Test that the API class behaves reliably.
 *
 * @group mailchimp_transactional
 */
class MailchimpTransactionalApiTest extends TestBase {

  /**
   * For testing sub account retrieval.
   *
   * @todo doesn't need to be a functional test, refactor.
   */
  public function testGetSubAccounts() {
    /** @var \Drupal\mailchimp_transactional\TestApi $mailchimp_transactional_api */
    $mailchimp_transactional_api = $this->container->get('mailchimp_transactional.test');
    $sub_accounts = $mailchimp_transactional_api->getSubAccounts();
    $this->assertNotEmpty($sub_accounts, 'Tested retrieving sub-accounts.');
    if (!empty($sub_accounts) && is_array($sub_accounts)) {
      foreach ($sub_accounts as $sub_account) {
        $this->assertNotEmpty($sub_account['name'], 'Tested valid sub-account: ' . $sub_account['name']);
      }
    }
  }

}

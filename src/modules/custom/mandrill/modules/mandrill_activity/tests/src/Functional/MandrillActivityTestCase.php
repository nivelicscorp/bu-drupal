<?php

/**
 * @file
 * Test class and methods for the Mandrill Activity module.
 */

namespace Drupal\Tests\mandrill_activity\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test Mandrill Activity functionality.
 *
 * @group mandrill
 */
class MandrillActivityTestCase extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['mandrill', 'mandrill_activity'];

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
    $config->set('mandrill_api_key', 'MANDRILL_TEST_API_KEY');
  }

  /**
   * Tests getting an array of message activity for a given email address.
   */
  public function testGetActivity() {
    $email = 'recipient@example.com';

    /* @var $mandrill_api \Drupal\mandrill\MandrillTestAPI */
    $mandrill_api = \Drupal::service('mandrill.test.api');

    $activity = $mandrill_api->getMessages($email);

    static::assertNotEmpty($activity, 'Tested retrieving activity.');

    if (!empty($activity) && is_array($activity)) {
      foreach ($activity as $message) {
        $this->assertEqual($message['email'], $email, 'Tested valid message: ' . $message['subject']);
      }
    }
  }

}

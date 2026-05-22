<?php
declare(strict_types=1);

/**
 * @file
 * Test class and methods for the Mandrill Reports module.
 */

namespace Drupal\Tests\mandrill_reports\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test Mandrill Reports functionality.
 *
 * @group mandrill
 */
class MandrillReportsTestCase extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['mandrill', 'mandrill_reports'];

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
    $config->set('mandrill_api_key', MANDRILL_TEST_API_KEY);
  }

  /**
   * Tests getting Mandrill reports data.
   */
  public function testGetReportsData() {
    /* @var \Drupal\mandrill_reports\MandrillReportsService $reports */
    $mandrill_api = \Drupal::service('mandrill.test.api');

    $reports_data = [
      'user' => $mandrill_api->getUser(),
      'tags' => $mandrill_api->getTags(),
      'all_time_series' => $mandrill_api->getTagsAllTimeSeries(),
    ];

    $this->assertTrue(!empty($reports_data['user']), 'Tested user report data exists.');
    $this->assertTrue(!empty($reports_data['tags']), 'Tested tags report data exists.');
    $this->assertTrue(!empty($reports_data['all_time_series']), 'Tested all time series report data exists.');
  }

}

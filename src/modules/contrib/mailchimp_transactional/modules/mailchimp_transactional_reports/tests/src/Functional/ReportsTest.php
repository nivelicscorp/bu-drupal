<?php

declare(strict_types=1);
/**
 * @file
 * Test class and methods for the Mailchimp Transactional Reports module.
 */

namespace Drupal\Tests\mailchimp_transactional_reports\Functional;

use Drupal\Tests\mailchimp_transactional\Functional\TestBase;

/**
 * Test Mailchimp Transactional Reports functionality.
 *
 * @group mailchimp_transactional
 */
class ReportsTest extends TestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'mailchimp_transactional',
    'mailchimp_transactional_reports',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests getting Mailchimp Transactional reports data.
   */
  public function testGetReportsData() {
    /** @var \Drupal\mailchimp_transactional_reports\ReportsService $reports */
    $reports = \Drupal::service('mailchimp_transactional_reports.test.service');

    $reports_data = [
      'user' => $reports->getUser(),
      'all_time_series' => $reports->getTagsAllTimeSeries(),
    ];

    $this->assertNotEmpty($reports_data['user'], 'Tested user report data exists.');
    $this->assertNotEmpty($reports_data['all_time_series'], 'Tested all time series report data exists.');
  }

}

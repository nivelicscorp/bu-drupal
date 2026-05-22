<?php

declare(strict_types=1);
/**
 * @file
 * Contains \Drupal\mailchimp_transactional_reports\Controller\ReportsController.
 */

namespace Drupal\mailchimp_transactional_reports\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\mailchimp_transactional_reports\ReportsServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MailchimpTransactionalReports controller.
 */
class ReportsController extends ControllerBase {

  /**
   * Mailchimp Transactional reports service.
   *
   * @var \Drupal\mailchimp_transactional_reports\ReportsServiceInterface
   */
  protected $reports;

  /**
   * Class constructor.
   */
  public function __construct(ReportsServiceInterface $reports) {
    $this->reports = $reports;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mailchimp_transactional_reports.service')
    );
  }

  /**
   * View Mailchimp Transactional dashboard report.
   *
   * @return array
   *   Renderable array of page content.
   */
  public function dashboard(): array {
    $content = [];
    $data = ['all_time_series' => $this->reports->getTagsAllTimeSeries()];
    $settings = [];

    // All time series chart data.
    foreach ($data['all_time_series'] as $series) {
      $settings['mailchimp_transactional_reports']['volume'][] = [
        'date' => $series->time,
        'sent' => $series->sent,
        'bounced' => $series->hard_bounces + $series->soft_bounces,
        'rejected' => $series->rejects,
      ];

      $settings['mailchimp_transactional_reports']['engagement'][] = [
        'date' => $series->time,
        'open_rate' => $series->sent == 0 ? 0 : $series->unique_opens / $series->sent,
        'click_rate' => $series->sent == 0 ? 0 : $series->unique_clicks / $series->sent,
      ];
    }

    $content['info'] = [
      '#markup' => $this->t(
        'The following reports are based on Mailchimp Transactional data from the last 30 days. For more comprehensive data, please visit your %dashboard. %cache to ensure the data is current.',
        [
          '%dashboard' => Link::fromTextAndUrl($this->t('Mailchimp Transactional Dashboard'), Url::fromUri('https://mandrillapp.com/'))->toString(),
          '%cache' => Link::fromTextAndUrl($this->t('Clear your cache'), Url::fromRoute('system.performance_settings'))->toString(),
        ]
      ),
    ];

    $content['volume'] = [
      '#prefix' => '<h2>' . $this->t('Sending Volume') . '</h2>',
      '#markup' => '<div id="mailchimp_transactional-volume-chart"></div>',
    ];

    $content['engagement'] = [
      '#prefix' => '<h2>' . $this->t('Average Open and Click Rate') . '</h2>',
      '#markup' => '<div id="mailchimp_transactional-engage-chart"></div>',
    ];

    $content['#attached']['library'][] = 'mailchimp_transactional_reports/google-jsapi';
    $content['#attached']['library'][] = 'mailchimp_transactional_reports/reports-stats';

    $content['#attached']['drupalSettings'] = $settings;

    return $content;
  }

  /**
   * View Mailchimp Transactional account summary report.
   *
   * @return array
   *   Renderable array of page content.
   */
  public function summary(): array {
    $content = [];

    /** @var \Drupal\mailchimp_transactional_reports\ReportsService $reports */

    $user = $this->reports->getUser();
    if ($user === NULL) {
      $this->messenger()->addError($this->t('Unable to retrieve information about the active API user.'));
      return $content;
    }

    $content['info_table_desc'] = [
      '#markup' => $this->t('Active API user information.'),
    ];

    // User info table.
    $content['info_table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Attribute'),
        $this->t('Value'),
      ],
      '#empty' => 'No account information.',
    ];

    $info = [
      ['attr' => $this->t('Username'), 'value' => $user->username],
      ['attr' => $this->t('Reputation'), 'value' => $user->reputation],
      ['attr' => $this->t('Hourly quota'), 'value' => $user->hourly_quota],
      ['attr' => $this->t('Backlog'), 'value' => $user->backlog],
    ];

    $row = 0;
    foreach ($info as $item) {
      $content['info_table'][$row]['attribute'] = [
        '#markup' => $item['attr'],
      ];

      $content['info_table'][$row]['value'] = [
        '#markup' => $item['value'],
      ];

      $row++;
    }

    $content['stats_table_desc'] = [
      '#markup' => $this->t("This table contains an aggregate summary of the account's sending stats."),
    ];

    // User stats table.
    $content['stats_table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Range'),
        $this->t('Sent'),
        $this->t('hard_bounces'),
        $this->t('soft_bounces'),
        $this->t('Rejects'),
        $this->t('Complaints'),
        $this->t('Unsubs'),
        $this->t('Opens'),
        $this->t('unique_opens'),
        $this->t('Clicks'),
        $this->t('unique_clicks'),
      ],
      '#empty' => 'No account activity yet.',
    ];

    if (!empty($user->stats)) {
      $row = 0;
      foreach ($user->stats as $key => $stat) {
        $content['stats_table'][$row]['range'] = [
          '#markup' => str_replace('_', ' ', $key),
        ];

        $content['stats_table'][$row]['sent'] = [
          '#markup' => $stat->sent,
        ];

        $content['stats_table'][$row]['hard_bounces'] = [
          '#markup' => $stat->hard_bounces,
        ];

        $content['stats_table'][$row]['soft_bounces'] = [
          '#markup' => $stat->soft_bounces,
        ];

        $content['stats_table'][$row]['rejects'] = [
          '#markup' => $stat->rejects,
        ];

        $content['stats_table'][$row]['complaints'] = [
          '#markup' => $stat->complaints,
        ];

        $content['stats_table'][$row]['unsubs'] = [
          '#markup' => $stat->unsubs,
        ];

        $content['stats_table'][$row]['opens'] = [
          '#markup' => $stat->opens,
        ];

        $content['stats_table'][$row]['unique_opens'] = [
          '#markup' => $stat->unique_opens,
        ];

        $content['stats_table'][$row]['clicks'] = [
          '#markup' => $stat->clicks,
        ];

        $content['stats_table'][$row]['unique_clicks'] = [
          '#markup' => $stat->unique_clicks,
        ];

        $row++;
      }
    }

    return $content;
  }

}

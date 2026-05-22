<?php

declare(strict_types=1);

/**
 * @file
 * Contains \Drupal\mailchimp_transactional_activity\Controller\ActivityController.
 */

namespace Drupal\mailchimp_transactional_activity\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\mailchimp_transactional\APIInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Activity controller.
 */
class ActivityController extends ControllerBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The Mailchimp Transactional API.
   *
   * @var \Drupal\mailchimp_transactional\APIInterface
   */
  protected $mailchimpTransactionalApi;

  /**
   * Class constructor.
   */
  public function __construct(DateFormatterInterface $date_formatter, APIInterface $mailchimp_transactional_api) {
    $this->dateFormatter = $date_formatter;
    $this->mailchimpTransactionalApi = $mailchimp_transactional_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('mailchimp_transactional')
    );
  }

  /**
   * View Mailchimp Transactional activity for a given user.
   *
   * @param \Drupal\user\Entity\User $user
   *   The User to view activity for.
   *
   * @return array
   *   Renderable array of page content.
   */
  public function overview(User $user) {
    $content = [];

    /** @var \Drupal\mailchimp_transactional\API $this->mailchimpTransactionalApi */
    $email = $user->getEmail();
    $messages = $this->mailchimpTransactionalApi->getMessages($email);

    $content['activity'] = [
      '#markup' => $this->t('The most recent emails sent to %email via Mailchimp Transactional in the last 7 days.', ['%email' => $email]),
    ];

    $content['activity_table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Subject'),
        $this->t('Timestamp'),
        $this->t('State'),
        $this->t('Opens'),
        $this->t('Clicks'),
        $this->t('Tags'),
      ],
      '#empty' => 'No activity yet.',
    ];

    foreach ($messages as $index => $message) {
      $content['activity_table'][$index]['subject'] = [
        '#markup' => $message->subject,
      ];

      $content['activity_table'][$index]['timestamp'] = [
        '#markup' => $this->dateFormatter->format($message->ts, 'short'),
      ];

      $content['activity_table'][$index]['state'] = [
        '#markup' => $message->state,
      ];

      $content['activity_table'][$index]['opens'] = [
        '#markup' => $message->opens,
      ];

      $content['activity_table'][$index]['clicks'] = [
        '#markup' => $message->clicks,
      ];

      $content['activity_table'][$index]['tags'] = [
        '#markup' => implode(', ', $message->tags),
      ];
    }

    return $content;
  }

}

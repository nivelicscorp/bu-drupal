<?php
declare(strict_types=1);

/**
 * @file
 * Contains \Drupal\mandrill_activity\Controller\MandrillActivityController.
 */

namespace Drupal\mandrill_activity\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;

/**
 * MandrillActivity controller.
 */
class MandrillActivityController extends ControllerBase {

  /**
   * View Mandrill activity for a given user.
   *
   * @param \Drupal\user\Entity\User $user
   *   The User to view activity for.
   *
   * @return array
   *   Renderable array of page content.
   */
  public function overview(User $user) {
    $content = [];

    /* @var \Drupal\mandrill\MailchimpTransactionalAPI $api */
    $api = \Drupal::service('mailchimp_transactional.api');
    $email = $user->getEmail();
    $messages = $api->getMessages($email);

    $content['activity'] = [
      '#markup' => t('The most recent emails sent to %email via Mailchimp Transactional in the last 7 days.', ['%email' => $email]),
    ];

    $content['activity_table'] = [
      '#type' => 'table',
      '#header' => [t('Subject'), t('Timestamp'), t('State'), t('Opens'), t('Clicks'), t('Tags')],
      '#empty' => 'No activity yet.',
    ];

    foreach ($messages as $index => $message) {
      $content['activity_table'][$index]['subject'] = [
        '#markup' => $message->subject,
      ];

      $content['activity_table'][$index]['timestamp'] = [
        '#markup' => \Drupal::service('date.formatter')->format($message->ts, 'short'),
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

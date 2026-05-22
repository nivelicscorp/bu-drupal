<?php

declare(strict_types=1);
/**
 * @file
 * Contains \Drupal\mailchimp_transactional_template\TemplateService.
 */

namespace Drupal\mailchimp_transactional_template;

use Drupal\mailchimp_transactional\Service;

/**
 * Mailchimp Transactional Template service.
 *
 * Overrides Mailchimp Transactional Service to allow sending of templated
 * messages.
 */
class TemplateService extends Service {

  /**
   * {@inheritdoc}
   */
  public function send($message) {
    $template_map = NULL;

    if (isset($message['id']) && isset($message['module'])) {
      // Check for a template map to use with this message.
      $template_map = mailchimp_transactional_template_load_by_mailsystem($message['id'], $message['module']);
    }

    try {
      if (!empty($template_map)) {
        // Send the message with template information.
        $template_content = [
          [
            'name' => $template_map->content_area,
            'content' => $message['html'],
          ],
        ];

        if (isset($message['mailchimp_transactional_template_content'])) {
          $template_content = array_merge($message['mailchimp_transactional_template_content'], $template_content);
        }

        $response = $this->mailchimpTransactionalAPI->sendTemplate($message, $template_map->template_name, $template_content);
      }
      else {
        // No template map, so send a standard message.
        $response = $this->mailchimpTransactionalAPI->send(['message' => $message]);
      }
    }
    catch (\Exception $e) {
      $this->log->error('Error sending email from %from to %to. @code: @message', [
        '%from' => $message['from_email'],
        '%to' => $message['to'],
        '@code' => $e->getCode(),
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }

    if (!empty($response)) {
      return $this->handleSendResponse($response, $message);
    }
    else {
      return FALSE;
    }
  }

}

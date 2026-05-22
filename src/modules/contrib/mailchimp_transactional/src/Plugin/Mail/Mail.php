<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional\Plugin\Mail;

use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\Component\Utility\Html;
use Drupal\mailchimp_transactional\MailchimpTransactionalInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * Allow Drupal mailsystem to use Mailchimp Transactional when sending emails.
 *
 * @Mail(
 *   id = "mailchimp_transactional_mail",
 *   label = @Translation("Mailchimp Transactional mailer"),
 *   description = @Translation("Sends the message through Mailchimp Transactional.")
 * )
 */
class Mail implements MailInterface {

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The Mailchimp Transactional service.
   *
   * @var \Drupal\mailchimp_transactional\ServiceInterface
   */
  protected $mailchimpTransactional;

  /**
   * The Logger Factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $log;

  /**
   * The MIME Type Guesser service.
   *
   * @var \Drupal\Core\File\MimeType\MimeTypeGuesser
   */
  protected $mimeTypeGuesser;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->config = \Drupal::service('config.factory')->get('mailchimp_transactional.settings');
    $this->mailchimpTransactional = \Drupal::service('mailchimp_transactional.service');
    $this->log = \Drupal::service('logger.factory')->get('mailchimp_transactional');
    $this->mimeTypeGuesser = \Drupal::service('file.mime_type.guesser');
  }

  /**
   * Concatenate and wrap the email body for either plain-text or HTML emails.
   *
   * @param array $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return array
   *   The formatted $message.
   */
  public function format(array $message) {
    // Join the body array into one string.
    if (is_array($message['body'])) {
      $message['body'] = Html::transformRootRelativeUrlsToAbsolute(implode("\n\n", $message['body']), \Drupal::request()->getSchemeAndHttpHost());
    }
    return $message;
  }

  /**
   * Send the email message.
   *
   * @param array $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return bool
   *   TRUE if the mail was successfully accepted, otherwise FALSE.
   *
   * @see drupal_mail()
   */
  public function mail(array $message): bool {
    // Optionally log mail keys not using Mailchimp Transactional already.
    // Helpful in configuring Mailchimp Transactional.
    if ($this->config->get('mailchimp_transactional_log_defaulted_sends')) {
      $registered = FALSE;
      foreach ($this->mailchimpTransactional->getMailSystems() as $key => $system) {
        if ($message['id'] == $key) {
          $registered = TRUE;
        }
      }
      if (!$registered) {
        $this->log->notice("Module: %module Key: %key invoked Mailchimp Transactional to send email because Mailchimp Transactional is configured as the default mail system. Specify alternate configuration for this module & key in %mailsystem if this is not desirable.",
          [
            '%module' => $message['module'],
            '%key' => $message['id'],
            '%mailsystem' => Link::fromTextAndUrl(new TranslatableMarkup('Mail System'), Url::fromRoute('mailsystem.settings'))->toString(),
          ]
        );
      }
    }
    // Apply input format to body.
    $format = $this->config->get('mailchimp_transactional_filter_format');
    if (!empty($format)) {
      $message['body'] = (string) check_markup($message['body'], $format);
    }
    // Prepare headers, defaulting the reply-to to the from address since
    // Mailchimp Transactional needs the from address to be configured.
    // Note that only Reply-To and X-* headers are allowed.
    $headers = $message['headers'] ?? [];
    if (isset($message['params']['mailchimp_transactional']['header'])) {
      $headers = $message['params']['mailchimp_transactional']['header'] + $headers;
    }
    // Different modules are using different capitalization in the header
    // array keys i.e. Reply-to and Reply-To.
    $header_key_map = [];
    foreach (array_keys($headers) as $header_key) {
      $header_key_map[strtolower($header_key)] = $header_key;
    }
    // If the header reply-to doesn't exist use the from address.
    if (!empty($message['from_email']) && (!isset($header_key_map['reply-to']) || empty($headers[$header_key_map['reply-to']]))) {
      $reply_to_key = $header_key_map['reply-to'] ?? 'Reply-to';
      $headers[$reply_to_key] = $message['from_email'];
    }
    // Prepare attachments.
    $attachments = [];
    if (isset($message['attachments']) && !empty($message['attachments'])) {
      foreach ($message['attachments'] as $attachment) {
        if (is_file($attachment)) {
          $attachments[] = $this->getAttachmentStruct($attachment);
        }
      }
    }
    // Determine if content should be available for this message.
    $blacklisted_keys = explode(',', (string) $this->config->get('mailchimp_transactional_mail_key_blacklist'));
    $view_content = TRUE;
    foreach ($blacklisted_keys as $key) {
      if ($message['id'] == mb_strtolower(trim($key))) {
        $view_content = FALSE;
        break;
      }
    }
    // The Mime Mail module (mimemail) expects attachments as an array of file
    // arrays in $message['params']['attachments']. As many modules assume you
    // will be using Mime Mail to handle attachments, we need to parse this
    // array as well.
    if (isset($message['params']['attachments']) && !empty($message['params']['attachments'])) {
      foreach ($message['params']['attachments'] as $attachment) {
        if (isset($attachment['uri'])) {
          $attachment_path = \Drupal::service('file_system')->realpath($attachment['uri']);
          if (is_file($attachment_path)) {
            $struct = $this->getAttachmentStruct($attachment_path);
            // Allow for customised filenames.
            if (!empty($attachment['filename'])) {
              $struct['name'] = $attachment['filename'];
            }
            $attachments[] = $struct;
          }
        }
        // Support attachments that are directly included without a file in the
        // filesystem.
        elseif (isset($attachment['filecontent'])) {
          $attachments[] = [
            'type' => $attachment['filemime'],
            'name' => $attachment['filename'],
            'content' => chunk_split(base64_encode($attachment['filecontent'])),
          ];
        }
      }
      // Remove the file objects from $message['params']['attachments'].
      // (This prevents double-attaching in the drupal_alter hook below.)
      unset($message['params']['attachments']);
    }
    // Setup the list of recipients from the mail message and header data.
    $receivers = ['to' => $message['to']];
    if (isset($message['headers']['cc'])) {
      $receivers['cc'] = $message['headers']['cc'];
    }
    if (isset($message['headers']['bcc'])) {
      $receivers['bcc'] = $message['headers']['bcc'];
    }
    // Include the Start case versions of cc and bcc keys since PHP's array keys
    // are case-sensitive.
    if (isset($message['headers']['Cc'])) {
      $receivers['cc'] = $message['headers']['Cc'];
    }
    if (isset($message['headers']['Bcc'])) {
      $receivers['bcc'] = $message['headers']['Bcc'];
    }
    // Extract an array of recipients.
    $to = $this->mailchimpTransactional->getReceivers($receivers);
    // Account for the plaintext parameter provided by the mimemail module.
    $plain_text = empty($message['params']['plaintext']) ? MailFormatHelper::htmlToText($message['body']) : $message['params']['plaintext'];
    // Get metadata.
    $metadata = $message['metadata'] ?? [];
    $from = [
      'email' => !empty($this->config->get('mailchimp_transactional_from_email')) ? $this->config->get('mailchimp_transactional_from_email') : $from['email'] = \Drupal::config('system.site')->get('mail'),
      'name' => !empty($this->config->get('mailchimp_transactional_from_name')) ? $this->config->get('mailchimp_transactional_from_name') : $from['name'] = \Drupal::config('system.site')->get('name'),
    ];
    $overrides = $message['params']['mailchimp_transactional']['overrides'] ?? [];
    $mailchimp_transactional_message = $overrides + [
      'id' => $message['id'],
      'module' => $message['module'],
      'html' => $message['body'],
      'text' => $plain_text,
      'subject' => $message['subject'],
      'from_email' => $message['params']['from_mail'] ?? $from['email'],
      'from_name' => $message['params']['from_name'] ?? $from['name'],
      'to' => $to,
      'headers' => $headers,
      'track_opens' => $this->config->get('mailchimp_transactional_track_opens'),
      'track_clicks' => $this->config->get('mailchimp_transactional_track_clicks'),
      // We're handling this with htmlToText.
      'auto_text' => FALSE,
      'url_strip_qs' => $this->config->get('mailchimp_transactional_url_strip_qs'),
      'bcc_address' => $message['bcc_email'] ?? NULL,
      'tags' => [$message['id']],
      'google_analytics_domains' => ($this->config->get('mailchimp_transactional_analytics_domains')) ? explode(',', (string) $this->config->get('mailchimp_transactional_analytics_domains')) : [],
      'google_analytics_campaign' => $this->config->get('mailchimp_transactional_analytics_campaign'),
      'attachments' => $attachments,
      'view_content_link' => $view_content,
      'metadata' => $metadata,
    ];
    $subaccount = $this->config->get('mailchimp_transactional_subaccount');
    if ($subaccount && $subaccount != '_none') {
      $mailchimp_transactional_message['subaccount'] = $subaccount;
    }
    // Allow other modules to alter the Mailchimp Transactional message.
    $mailchimp_transactional_params = [
      'message' => $mailchimp_transactional_message,
    ];
    \Drupal::moduleHandler()->alter('mailchimp_transactional_mail', $mailchimp_transactional_params, $message);

    // Queue for processing during cron or send immediately.
    if ($this->config->get('mailchimp_transactional_process_async')) {
      $queue = \Drupal::queue(MailchimpTransactionalInterface::MAILCHIMP_TRANSACTIONAL_QUEUE, TRUE);
      $queue->createItem($mailchimp_transactional_params);
      if ($this->config->get('mailchimp_transactional_batch_log_queued')) {
        $this->log->notice('Message from %from to %to queued for delivery.', [
          '%from' => $from['email'],
          '%to' => $to[0]['email'],
        ]);
      }
      return TRUE;
    }
    else {
      return $this->mailchimpTransactional->send($mailchimp_transactional_params['message']);
    }
  }

  /**
   * Return an array structure for a message attachment.
   *
   * @param string $path
   *   Attachment path.
   *
   * @return array
   *   Attachment structure.
   *
   * @throws \Exception
   */
  public function getAttachmentStruct($path): array {
    $struct = [];
    if (!@is_file($path)) {
      throw new \Exception($path . ' is not a valid file.');
    }
    $filename = basename($path);
    $file_buffer = file_get_contents($path);
    $file_buffer = chunk_split(base64_encode($file_buffer), 76, "\n");
    if ($this->mimeTypeGuesser instanceof MimeTypeGuesserInterface) {
      $mime_type = $this->mimeTypeGuesser->guessMimeType($path);
    }
    else {
      $mime_type = $this->mimeTypeGuesser->guess($path);
    }
    if (!$this->isValidContentType($mime_type)) {
      throw new \Exception($mime_type . ' is not a valid content type.');
    }
    $struct['type'] = $mime_type;
    $struct['name'] = $filename;
    $struct['content'] = $file_buffer;
    return $struct;
  }

  /**
   * Helper to determine if an attachment is valid.
   *
   * @param string $file_type
   *   The file mime type.
   *
   * @return bool
   *   True or false.
   */
  protected function isValidContentType($file_type) {
    $valid_types = [
      'image/',
      'text/',
      'application/pdf',
      'application/x-zip',
    ];
    \Drupal::moduleHandler()->alter('mailchimp_transactional_valid_attachment_types', $valid_types);
    foreach ($valid_types as $vct) {
      if (strpos($file_type, $vct) !== FALSE) {
        return TRUE;
      }
    }
    return FALSE;
  }

}

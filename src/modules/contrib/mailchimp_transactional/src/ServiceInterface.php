<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional;

/**
 * Interface for the Mailchimp Transactional service.
 */
interface ServiceInterface {

  /**
   * Get the mail systems defined in the mail system module.
   *
   * @return array
   *   Array of mail systems and keys
   *   - key Either the module-key or default for site wide system.
   *   - sender The class to use for sending mail.
   *   - formatter The class to use for formatting mail.
   */
  public function getMailSystems();

  /**
   * Helper to generate an array of recipients.
   *
   * This function accepts an array of values keyed in the following way:
   * $receiver = [
   *   'to' => 'user@domain.com, any number of names <user@domain.com>',
   *   'cc' => 'user@domain.com, any number of names <user@domain.com>',
   *   'bcc' => 'user@domain.com, any number of names <user@domain.com>',
   * ];
   * The only required key is 'to'. The other values will automatically be
   * discovered if present. The strings of email addresses could provide a
   * single email address or many, depending on the needs of the application.
   *
   * This structure is in keeping with the Mailchimp Transactional API
   * documentation located here:
   * https://mailchimp.com/developer/transactional/api/messages/
   *
   * @param mixed $receivers
   *   An array of comma delimited lists of email addresses.
   *
   * @return array
   *   array of email addresses
   */
  public function getReceivers($receivers);

  /**
   * Abstracts sending of messages, allowing queueing option.
   *
   * @param array $message
   *   A message array formatted for Mailchimp Transactional's sending API.
   *
   * @return bool
   *   TRUE if no exception thrown.
   */
  public function send(array $message);

}

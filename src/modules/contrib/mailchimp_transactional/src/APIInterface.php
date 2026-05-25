<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional;

/**
 * Interface for the Mailchimp Transactional API.
 */
interface ApiInterface {

  /**
   * Checks if the Mailchimp Transactional PHP library is available.
   *
   * @return bool
   *   TRUE if the library is available.
   */
  public function isLibraryInstalled(): bool;

  /**
   * Gets messages received by an email address, from the API.
   *
   * @param string $email
   *   The email address of the message recipient.
   *
   * @return array
   *   Array of objects representing email messages
   *   sent to the provided email address.
   */
  public function getMessages($email): array;

  /**
   * Return a list of all the templates available to this user from the API.
   *
   * @return array
   *   Array of template objects with complete data, empty if none.
   */
  public function getTemplates(): array;

  /**
   * Gets a list of all sub accounts from the API.
   *
   * @return array
   *   Array of objects, each representing a subaccount.
   */
  public function getSubAccounts(): array;

  /**
   * Gets information about the API-connected user.
   *
   * @return object|null
   *   Describes the API-connected user and their stats.
   *   Returns NULL if the API request failed.
   */
  public function getUser(): ?object;

  /**
   * Gets recent history for all tags.
   *
   * @return array
   *   Array of objects, each contains stats for a single active hour.
   */
  public function getTagsAllTimeSeries(): array;

  /**
   * Sends a templated Mailchimp Transactional message via the API.
   *
   * @param array $message
   *   All information on message to send.
   * @param string $template_id
   *   The name of the template in mailchimp transactional to use.
   * @param array $template_content
   *   Array with two keys: a content block id and the content to fill it.
   *
   * @return array
   *   Of sending result objects, one per recipient.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function sendTemplate(array $message, $template_id, array $template_content): array;

  /**
   * Sends an email via the API.
   *
   * Also the function used by hook_mailchimp_transactional_mailsend().
   *
   * @param array $message
   *   Associative array containing message data.
   *
   * @return array
   *   Results of sending the message.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function send(array $message): array;

}

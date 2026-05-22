<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional;

/**
 * This interface stores global constants.
 */
interface MailchimpTransactionalInterface {

  /*
   * A dummy API key for tests that don't rely on the actual API.
   *
   * @todo refactor the test api into a test module.
   */
  const MAILCHIMP_TRANSACTIONAL_TEST_API_KEY = 'undefined';

  /*
   * The queue name for this module to use for queued mail.
   */
  const MAILCHIMP_TRANSACTIONAL_QUEUE = 'mailchimp_transactional_queue';

  /*
   * Regex to break up 'name <email@domain.com>' strings.
   *
   * Used in preg_match to separate name and email.
   */
  const MAILCHIMP_TRANSACTIONAL_EMAIL_REGEX = '/^\s*(.+?)\s*<\s*([^>]+)\s*>$/';

}

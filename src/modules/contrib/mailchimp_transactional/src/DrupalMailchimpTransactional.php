<?php

declare(strict_types=1);
namespace Drupal\mailchimp_transactional;

use GuzzleHttp\Client;
use MailchimpTransactional\ApiClient;
use MailchimpTransactional\ApiException;

/**
 * Overrides default Mailchimp Transactional library.
 *
 * Intercepts API responses and makes sure ApiException is thrown.
 */
class DrupalMailchimpTransactional extends ApiClient {
  /**
   * Timeout in seconds for requests to the Mailchimp Transactional API.
   *
   * @var int
   */
  protected $timeout;

  /**
   * {@inheritdoc}
   *
   * Override constructor to remove curl operations.
   *
   * @throws \MailchimpTransactional\ApiException
   */
  public function __construct(Client $http_client, $apikey = NULL, $timeout = 60) {
    parent::__construct();

    if (!$apikey) {
      throw new ApiException('You must provide a Mailchimp Transactional API key');
    }

    $this->setApiKey($apikey);

    $this->timeout = $timeout;

    $this->host = rtrim($this->host, '/');
    $this->requestClient = $http_client;
  }

  /**
   * Override __destruct() to prevent calling curl_close().
   */
  public function __destruct() {}

  /**
   * Override call method to throw an exception.
   *
   * Otherwise, only a string with error information is returned.
   *
   * @throws \MailchimpTransactional\ApiException
   */
  public function post($path, $body) {
    $results = parent::post($path, $body);

    if (is_string($results) && ($results != 'PONG!')) {
      throw new ApiException('API Library returned:' . $results);
    }

    return $results;
  }

}

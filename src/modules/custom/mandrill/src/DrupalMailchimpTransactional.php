<?php
declare(strict_types=1);

namespace Drupal\mandrill;

use Drupal\Component\Serialization\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use MailchimpTransactional\Api;
use MailchimpTransactional\ApiClient;
use MailchimpTransactional\ApiException;

/**
 * Overrides default Mandrill library to provide custom API call function.
 */
class DrupalMailchimpTransactional extends ApiClient {
  /**
   * The timeout length in seconds for requests to the Mailchimp Transactional API.
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
   * {@inheritdoc}
   *
   * Override __destruct() to prevent calling curl_close().
   */
  public function __destruct() {}

  /**
   * {@inheritdoc}
   *
   * Override call method to throw an exception, otherwise only a string with error information is returned.
   *
   * Throws statement here
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

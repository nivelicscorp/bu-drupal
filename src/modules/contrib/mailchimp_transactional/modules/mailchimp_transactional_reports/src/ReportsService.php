<?php

declare(strict_types=1);

namespace Drupal\mailchimp_transactional_reports;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mailchimp_transactional\ApiInterface;

/**
 * Mailchimp Transactional Reports service.
 */
class ReportsService implements ReportsServiceInterface {

  /**
   * The Mailchimp Transactional API service.
   *
   * @var \Drupal\mailchimp_transactional\ApiInterface
   */
  protected $mailchimpTransactionalApi;

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The mailchimp_transactional cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */

  protected $cache;

  /**
   * Constructs the service.
   *
   * @param \Drupal\mailchimp_transactional\ApiInterface $mailchimp_transactional_api
   *   The Mailchimp Transactional API service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The mailchimp_transactional cache service.
   */
  public function __construct(ApiInterface $mailchimp_transactional_api, ConfigFactoryInterface $config_factory, CacheBackendInterface $cache) {
    $this->mailchimpTransactionalApi = $mailchimp_transactional_api;
    $this->config = $config_factory;
    $this->cache = $cache;
  }

  /**
   * Object representing the API-connected user.
   *
   * @return object|null
   *   The user object from the Mailchimp Transactional API Key.
   */
  public function getUser(): ?object {
    return $this->mailchimpTransactionalApi->getUser();
  }

  /**
   * Gets recent history for all tags.
   *
   * @return array
   *   The recent history (hourly stats for the last 30 days) for all tags.
   */
  public function getTagsAllTimeSeries() {
    $cached_tags_series = $this->cache->get('tags_series');

    if (!empty($cached_tags_series)) {
      return $cached_tags_series->data;
    }

    $data = $this->mailchimpTransactionalApi->getTagsAllTimeSeries();

    $this->cache->set('tags_series', $data);

    return $data;
  }

}

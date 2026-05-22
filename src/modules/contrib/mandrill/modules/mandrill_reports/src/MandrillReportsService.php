<?php
declare(strict_types=1);

/**
 * @file
 * Contains \Drupal\mandrill_reports\MandrillReportsService.
 */

namespace Drupal\mandrill_reports;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mandrill\MandrillAPIInterface;

/**
 * Mandrill Reports service.
 */
class MandrillReportsService implements MandrillReportsInterface {

  /**
   * The Mandrill API service.
   *
   * @var \Drupal\mandrill\MandrillAPIInterface
   */
  protected $mandrillApi;

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructs the service.
   *
   * @param \Drupal\mandrill\MandrillAPIInterface $mandrill_api
   *   The Mandrill API service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(MandrillAPIInterface $mandrill_api, ConfigFactoryInterface $config_factory) {
    $this->mandrillApi = $mandrill_api;
    $this->config = $config_factory;
  }

  /**
   * @return object
   *   Object representing the API-connected user.
   */
  public function getUser() {
    return $this->mandrillApi->getUser();
  }

  /**
   * Gets tag data formatted for reports.
   *
   * @return array
   */
  public function getTags() {
    $cache = \Drupal::cache('mandrill');
    $cached_tags = $cache->get('tags');

    if (!empty($cached_tags)) {
      return $cached_tags->data;
    }

    $data = [];
    $tags = $this->mandrillApi->getTags();
    foreach ($tags as $tag) {
      if (!empty($tag['tag'])) {
        $data[$tag['tag']] = $this->mandrillApi->getTag($tag['tag']);
        $data[$tag['tag']]['time_series'] = $this->mandrillApi->getTagTimeSeries($tag['tag']);
      }
    }

    $cache->set('tags', $data);

    return $data;
  }

  /**
   * Gets recent history for all tags.
   *
   * @return array
   */
  public function getTagsAllTimeSeries() {
    $cache = \Drupal::cache('mandrill');
    $cached_tags_series = $cache->get('tags_series');

    if (!empty($cached_tags_series)) {
      return $cached_tags_series->data;
    }

    $data = $this->mandrillApi->getTagsAllTimeSeries();

    $cache->set('tags_series', $data);

    return $data;
  }

}

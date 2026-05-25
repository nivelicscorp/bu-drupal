<?php

namespace Drupal\media_entity_facebook;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Template\Attribute;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Fetches embedded data.
 */
class FacebookFetcher {

  /**
   * Stores logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $loggerChannel;

  /**
   * Guzzle HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Tracks when an error has occurred when interacting with the API.
   *
   * @var bool
   */
  protected $apiErrorEncountered = FALSE;

  /**
   * The facebook config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Language codes for Facebook SDK localization.
   *
   * @var string[]
   */
  protected $langcodes = [
    'af' => 'af_ZA',
    'am' => 'am_ET',
    'ar' => 'ar_AR',
    'ast' => 'en_US',
    'az' => 'az_AZ',
    'be' => 'be_BY',
    'bg' => 'bg_BG',
    'bn' => 'bn_IN',
    // 'bo' => 'en_US',
    'bs' => 'bs_BA',
    'ca' => 'ca_ES',
    'cs' => 'cs_CZ',
    'cy' => 'cy_GB',
    'da' => 'da_DK',
    'de' => 'de_DE',
    // 'dz' => 'en_US',
    'el' => 'el_GR',
    'en' => 'en_US',
    'en-x-simple' => 'en_US',
    // 'eo' => 'en_US',
    'es' => 'es_ES',
    'et' => 'et_EE',
    'eu' => 'eu_ES',
    'fa' => 'fa_IR',
    'fi' => 'fi_FI',
    // 'fil' => 'en_US',
    'fo' => 'fo_FO',
    'fr' => 'fr_FR',
    'fy' => 'fy_NL',
    'ga' => 'ga_IE',
    // 'gd' => 'en_US',
    'gl' => 'gl_ES',
    'gsw-berne' => 'de_DE',
    'gu' => 'gu_IN',
    'he' => 'he_IL',
    'hi' => 'hi_IN',
    'hr' => 'hr_HR',
    // 'ht' => 'en_US',
    'hu' => 'hu_HU',
    'hy' => 'hy_AM',
    'id' => 'id_ID',
    'is' => 'is_IS',
    'it' => 'it_IT',
    'ja' => 'ja_JP',
    'jv' => 'jv_ID',
    'ka' => 'ka_GE',
    'kk' => 'kk_KZ',
    'km' => 'km_KH',
    'kn' => 'kn_IN',
    'ko' => 'ko_KR',
    'ku' => 'ku_TR',
    // 'ky' => 'en_US',
    // 'lo' => 'en_US',
    'lt' => 'lt_LT',
    'lv' => 'lv_LV',
    'mg' => 'mg_MG',
    'mk' => 'mk_MK',
    'ml' => 'ml_IN',
    'mn' => 'mn_MN',
    'mr' => 'mr_IN',
    'ms' => 'ms_MY',
    'my' => 'my_MM',
    'ne' => 'ne_NP',
    'nl' => 'nl_BE',
    'nb' => 'nb_NO',
    'nn' => 'nn_NO',
    // 'oc' => 'en_US',
    'pa' => 'pa_IN',
    'pl' => 'pl_PL',
    'pt-pt' => 'pt_PT',
    'pt-br' => 'pt_BR',
    'ro' => 'ro_RO',
    'ru' => 'ru_RU',
    // 'sco' => 'en_US',
    // 'se' => 'en_US',
    'si' => 'si_LK',
    'sk' => 'sk_SK',
    'sl' => 'sl_SI',
    'sq' => 'sq_AL',
    'sr' => 'sr_RS',
    'sv' => 'sv_SE',
    'sw' => 'sw_KE',
    'ta' => 'ta_IN',
    'ta-lk' => 'ta_IN',
    'te' => 'te_IN',
    'th' => 'th_TH',
    'tr' => 'tr_TR',
    // 'tyv' => 'en_US',
    // 'ug' => 'en_US',
    'uk' => 'uk_UA',
    'ur' => 'ur_PK',
    'vi' => 'vi_VN',
    // 'xx-lolspeak' => 'en_US',
    'zh-hans' => 'zh_CN',
    'zh-hant' => 'zh_CN',
  ];

  /**
   * Constructor for FacebookFetcher.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   The logger factory.
   * @param \GuzzleHttp\ClientInterface $client
   *   The Guzzle HTTP client.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Time service.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_channel_factory, ClientInterface $client, LanguageManagerInterface $language_manager, ConfigFactoryInterface $configFactory, CacheBackendInterface $cache, TimeInterface $time) {
    $this->loggerChannel = $logger_channel_factory->get('media_entity_facebook');
    $this->httpClient = $client;
    $this->languageManager = $language_manager;
    $this->config = $configFactory->get('media_entity_facebook.settings');
    $this->cache = $cache;
    $this->time = $time;
  }

  /**
   * Fetch and return response from Facebook's oEmbed API endpoint.
   *
   * @param string $resource_url
   *   The URL to pass to Facebook's oembed API.
   */
  public function getOembedData($resource_url) {
    $useEmbeddedPosts = !empty($this->config->get('use_embedded_posts'));
    $isIFrame = TRUE;
    $scriptAttributes = new Attribute();

    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $fbLangcode = !empty($this->langcodes[$langcode]) ? $this->langcodes[$langcode] : 'en_US';

    if ($useEmbeddedPosts) {
      $oembedResponse = [];

      if (strpos($resource_url, '<iframe') === FALSE) {
        $isIFrame = FALSE;
      }

      $oembedResponse['html'] = [
        '#theme' => 'media_entity_facebook',
        '#url' => $resource_url,
        '#script_attributes' => $scriptAttributes,
        '#is_iframe' => $isIFrame,
        '#fb_sdk_langcode' => $fbLangcode,
      ];

      $oembedResponse['author_name'] = 'Facebook';
      $oembedResponse['width'] = '500';
      $oembedResponse['height'] = 'auto';
      $oembedResponse['url'] = $resource_url;
      return $oembedResponse;
    }

    // If there was an error interacting with the Facebook API, like a network
    // timeout due to Facebook being down, we don't want to clog up the Drupal
    // site's resources by making lots of API requests that may all timeout.
    // To do this, we mark when a request exception occurred and back out of
    // subsequent requests if so. This of course only matters if there are many
    // embeds on a single page request.
    if ($this->apiErrorEncountered) {
      $this->loggerChannel->error('Aborting Facebook oembed API request due to a previously encountered error on the same request.');
      return FALSE;
    }

    $appId = $this->config->get('facebook_app_id') ?: '';
    $appSecret = $this->config->get('facebook_app_secret') ?: '';
    if (empty($appId) || empty($appSecret)) {
      $this->loggerChannel->error('Cannot retrieve Facebook embed as the Facebook app ID and/or app secret are missing from configuration. Visit /admin/config/media/facebook-settings to provide these values.');
      return FALSE;
    }

    $endpoint = $this->getApiEndpointUrl($resource_url) . '?url=' . $resource_url . '&access_token=' . $appId . '|' . $appSecret . '&sdklocale=' . $fbLangcode;

    $cid = 'media_entity_facebook:' . Crypt::hashBase64(serialize($endpoint));
    $cacheItem = $this->cache->get($cid);
    if ($cacheItem) {
      $oembedResponse = $cacheItem->data;
    }
    else {
      $options = [
        'timeout' => 5,
      ];
      try {
        $response = $this->httpClient->request('GET', $endpoint, $options);
      }
      catch (GuzzleException $e) {
        $this->loggerChannel->error('Error retrieving oEmbed data for a Facebook media entity: @error', ['@error' => $e->getMessage()]);
        $this->apiErrorEncountered = TRUE;
        return FALSE;
      }
      $oembedResponse = json_decode((string) $response->getBody(), TRUE);

      // Cache the result for 10 minutes.
      $cacheTime = $this->time->getRequestTime() + 600;
      $this->cache->set($cid, $oembedResponse, $cacheTime);
    }

    $oembedResponse['html'] = [
      '#theme' => 'media_entity_facebook',
      '#url' => $oembedResponse['html'] ?? '',
      '#script_attributes' => $scriptAttributes,
      '#is_iframe' => $isIFrame,
    ];

    return $oembedResponse;
  }

  /**
   * Return the appropriate Facebook oEmbed API endpoint for the content URL.
   *
   * @param string $content_url
   *   The content URL contains the URL to the resource.
   *
   * @return string
   *   The oEmbed endpoint URL.
   */
  protected function getApiEndpointUrl($content_url) {
    if (preg_match('/\/videos\//', $content_url) || preg_match('/\/video.php\//', $content_url) || preg_match('/^https:\/\/(www\.)?fb\.watch\//i', $content_url)) {
      return 'https://graph.facebook.com/v11.0/oembed_video';
    }
    else {
      return 'https://graph.facebook.com/v11.0/oembed_post';
    }
  }

}

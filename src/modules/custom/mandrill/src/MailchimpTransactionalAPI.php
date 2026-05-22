<?php
declare(strict_types=1);

namespace Drupal\mandrill;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use MailchimpTransactional\ApiException;

/**
 * Service class to integrate with Mandrill.
 */
class MailchimpTransactionalAPI implements MandrillAPIInterface {

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The Logger Factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $log;

  /**
   * The http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructs the service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory service.
   * @param \GuzzleHttp\Client $http_client
   *   The http client.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory, Client $http_client) {
    $this->config = $config_factory->get('mandrill.settings');
    $this->log = $logger_factory->get('mandrill');
    $this->httpClient = $http_client;
  }

  /**
   * Check if the Mandrill PHP library is available.
   *
   * @return bool
   *   TRUE if it is installed, FALSE otherwise.
   */
  public function isLibraryInstalled(): bool {
    return class_exists('\MailchimpTransactional\ApiClient');
  }

  /**
   * Gets messages received by an email address.
   *
   * @param string $email
   *   The email address of the message recipient.
   *
   * @return array
   *   Array of objects representing email messages
   *   sent to the provided email address.
   */
  public function getMessages($email): array {
    $messages = [];
    try {
      if ($mandrill = $this->getApiObject()) {
        $messages = $mandrill->messages->search(['query' => 'email:' . $email]);
      }
    }
    catch (RequestException $e) {
      \Drupal::messenger()->addError(
        t('Mandrill: %message', ['%message' => $e->getMessage()])
      );
      $this->log->error($e->getMessage());
    }
    return $messages;
  }

  /**
   * Gets a list of mandrill template objects.
   *
   * @return array|null
   *   Array of available templates, as objects with complete data,
   *   or NULL if none available.
   */
  public function getTemplates(): array {
    $templates = NULL;
    try {
      if ($mandrill = $this->getApiObject()) {
        $templates = $mandrill->templates->list();
      }
    }
    catch (RequestException $e) {
      \Drupal::messenger()->addError(t('Mandrill: %message', ['%message' => $e->getMessage()]));
      $this->log->error($e->getMessage());
    }
    return $templates;
  }

  /**
   * Gets a list of sub accounts.
   *
   * @return array
   *   Array of objects, each representing a subaccount.
   */
  public function getSubAccounts(): array {
    $accounts = [];
    try {
      if ($mandrill = $this->getApiObject()) {
        $accounts = $mandrill->subaccounts->list();
      }
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError(t('Mandrill: %message', ['%message' => $e->getMessage()]));
      $this->log->error($e->getMessage());
    }
    return $accounts;
  }

  /**
   * Gets current API user information.
   *
   * @return object
   *   Describes the API-connected user.
   */
  public function getUser() {
    $user = NULL;
    try {
      if ($mandrill = $this->getApiObject()) {
        $user = $mandrill->users->info();
      }
    }
    catch (RequestException $e) {
      \Drupal::messenger()->addError(t('Mandrill: %message', ['%message' => $e->getMessage()]));
      $this->log->error($e->getMessage());
    }
    return $user;
  }

  /**
   * Gets a list of tags.
   *
   * @return array
   *   Array of objects, each describing a user-defined tag.
   */
  public function getTags(): array {
    $tags = [];
    try {
      if ($mandrill = $this->getApiObject()) {
        $tags = $mandrill->tags->list();
      }
    }
    catch (RequestException $e) {
      \Drupal::messenger()->addError(t('Mandrill: %message', ['%message' => $e->getMessage()]));
      $this->log->error($e->getMessage());
    }
    return $tags;
  }

  /**
   * Gets a single tag.
   *
   * @param string $tag
   *   An existing tag name.
   *
   * @return array|null
   *   The tag information, or null if no such tag.
   */
  public function getTag($tag): array {
    $tag_info = NULL;
    try {
      if ($mandrill = $this->getApiObject()) {
        $tag_info = $mandrill->tags->info(['tag' => $tag]);
      }
    }
    catch (RequestException $e) {
      \Drupal::messenger()->addError(t('Mandrill: %message', ['%message' => $e->getMessage()]));
      $this->log->error($e->getMessage());
    }
    return $tag_info;
  }

  /**
   * Gets recent history for a tag.
   *
   * @param string $tag
   *   The tag as a string.
   *
   * @return array
   *   Array of tag history.
   */
  public function getTagTimeSeries($tag): array {
    $data = [];
    try {
      if ($mandrill = $this->getApiObject()) {
        $data = $mandrill->tags->timeSeries($tag);
      }
    }
    catch (RequestException $e) {
      \Drupal::messenger()->addError(t('Mandrill: %message', ['%message' => $e->getMessage()]));
      $this->log->error($e->getMessage());
    }
    return $data;
  }

  /**
   * Gets recent history for all tags.
   *
   * @return array
   *   Array of objects, each a tag's history (hourly stats for the last 30 days).
   */
  public function getTagsAllTimeSeries(): array {
    $data = [];
    try {
      if ($mandrill = $this->getApiObject()) {
        $data = $mandrill->tags->allTimeSeries();
      }
    }
    catch (RequestException $e) {
      \Drupal::messenger()->addError(t('Mandrill: %message', ['%message' => $e->getMessage()]));
      $this->log->error($e->getMessage());
    }
    return $data;
  }

  /**
   * Ping the API to validate an API key.
   *
   * @return bool
   *   True if API returns expected "PONG!" otherwise false
   */
  public function isApiKeyValid($api_key = NULL): bool {
    $response = FALSE;
    try {
      if ($mandrill = $this->getNewApiObject($api_key)) {
        $response = ($mandrill->users->ping() === 'PONG!');
      }
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError(t('Mandrill: %message', ['%message' => $e->getMessage()]));
      $this->log->error($e->getMessage());
    }
    return $response;
  }

  /**
   * Sends a templated Mandrill message.
   *
   * This function checks for appropriate settings in the message, then uses the
   * template API call to send the message if the settings are valid.
   *
   * @param array $message
   * @param string $template_id
   * @param array $template_content
   *
   * @return array|null
   *   Array of message objects, one per recipient.
   */
  public function sendTemplate($message, $template_id, $template_content): array {
    $result = NULL;
    try {
      if ($mandrill = $this->getApiObject()) {
        $result = $mandrill->messages->sendTemplate([
          'message' => $message,
          'template_name' => $template_id,
          'template_content' => $template_content,
        ]);
      }
    }
    catch (RequestException $e) {
      \Drupal::messenger()->addError(t('Mandrill: %message', ['%message' => $e->getMessage()]));
      $this->log->error($e->getMessage());
    }
    return $result;
  }

  /**
   * The function that calls the API send message.
   *
   * This is the default function used by mandrill_mailsend().
   *
   * @param array $message
   *   Associative array containing message data.
   *
   * @return array
   *   Results of sending the message.
   *
   * @throws \Exception
   */
  public function send(array $message): array {
    $result = NULL;
    try {
      if ($mailer = $this->getApiObject()) {
        $result = $mailer->messages->send($message);
      }
    }
    catch (RequestException $e) {
      \Drupal::messenger()->addError(t('Could not load Mandrill API: %message', ['%message' => $e->getMessage()]));
      $this->log->error($e->getMessage());
    }
    return $result;
  }

  /**
   * Return Mandrill API object for communication with the mandrill server.
   *
   * @param bool $reset
   *   Pass in TRUE to reset the statically cached object.
   *
   * @return \MailchimpTransactional\ApiClient|bool
   *   Mandrill Object upon success
   *   FALSE if 'mandrill_api_key' is unset
   */
  private function getApiObject($reset = FALSE, $api_key = NULL) {
    $api =& drupal_static(__FUNCTION__, NULL);
    if ($api === NULL || $reset || $api_key) {
      $api = $this->getNewApiObject($api_key);
    }
    return $api;
  }

  /**
   * @param $api_key
   */
  private function getNewApiObject($api_key) {
    if (!$this->isLibraryInstalled()) {
      $msg = t('Failed to load Mandrill PHP library. Please refer to the installation requirements.');
      $this->log->error($msg);
      \Drupal::messenger()->addError($msg);
      return NULL;
    }

    $api_key ?? $api_key = $this->config->get('mandrill_api_key');
    $api_timeout = $this->config->get('mandrill_api_timeout');
    if (empty($api_key)) {
      $msg = t('Failed to load Mandrill API Key. Please check your Mandrill settings.');
      $this->log->error($msg);
      \Drupal::messenger()->addError($msg);
      return FALSE;
    }
    // We allow the class name to be overridden, following the example of core's
    // mailsystem, in order to use alternate Mandrill classes.
    $class_name = $this->config->get('mandrill_api_classname');
    return new $class_name($this->httpClient, $api_key, $api_timeout);
  }
}

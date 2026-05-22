<?php

namespace Drupal\bu_sections\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Serialization\Json;

/**
 * Provides a controller to manage emblue features
 */
class EmBlueController extends ControllerBase implements EmBlueControllerInterface {

  use StringTranslationTrait;

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClientFactory;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Construct controller.
   *
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   *   Http client to make http calls.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   Log system.
   */
  public function __construct(
    ClientFactory $http_client_factory,
    LoggerChannelFactory $logger
  ) {
    $this->httpClientFactory = $http_client_factory;
    $this->logger = $logger->get('bu_sections.emblue.controller');
  }

  /**
   * Makes a requets to send data about an event to emBlue
   *
   * @param string $eventName
   *  Event name
   * @param array $attributes
   *  Attributes to send
   * @return array
   *  Result data
   */
  private function emBlueApi($eventName, $email, $attributes) {
    $endpoint = 'https://track.embluemail.com/contacts/event';
    $authorization = 'Basic Nzk4MzBiZDllN2I0NDE3NDk0YjNhZGE3MmRlM2I3OTY=';
    $result = [
      'status' => 500,
      'message' => 'Something went wrong, contact the administrator'
    ];

    try {
      if (empty($endpoint)) {
        throw new \Exception("Empty url");
      }
      $url = rtrim($endpoint, '/');
      $body = [
        'email' => $email ?? \Drupal::config('system.site')->get('mail'),
        'eventName' => $eventName,
        'attributes' => $attributes
      ];
      $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => $authorization,
      ];
      $clientOptions = [
        'verify' => FALSE,
        'connect_timeout' => 10,
      ];

      $clientOptions['curl'][CURLOPT_SSL_VERIFYPEER] = 0;
      $clientOptions['curl'][CURLOPT_SSL_VERIFYHOST] = 0;
      $client = $this->httpClientFactory->fromOptions($clientOptions);

      $response = $client->post($url, [
        'headers' => $headers,
        'body' => Json::encode($body),
      ]);

      $result =  [
        'status' => $response->getStatusCode(),
        'result' => Json::decode($response->getBody()),
      ];

      $this->logger->notice(
        $this->t(
          "The EmBlue Api of form Contact response: \n%response",
          [
            '%response' => print_r((string) $response->getBody(), TRUE),
          ]
        )
      );
    } catch (\Exception $e) {
      $result['message'] = $e->getMessage();
      if ($e instanceof \GuzzleHttp\Exception\ClientException) {
        $response = json_decode($e->getResponse()->getBody()->getContents());
        $result['status'] = $e->getResponse()->getStatusCode();
        if (isset($response->result)) {
          $result['message'] = $response->result;
        }
      }
      $variables = \Drupal\Core\Utility\Error::decodeException($e);
      $this->logger->error('%type: @message in %function (line %line of %file).', $variables);
    }
    return $result;
  }

  /**
   * Send the contact form data to emBlue
   *
   * @param array $attributes
   *  Form attributes to send
   * @return array
   *  Action result
   */
  public function sendNewsletterFormData($attributes) {
    $email = isset($attributes['correo_electronico_']) ? $attributes['correo_electronico_']: NULL;
    $this->deleteArrayItem($attributes,
      'si_doy_autorizacion_expresa_para_el_tratamiento_de_los_datos_aqu',
      'captcha', 'captcha_sid', 'captcha_token',
      'captcha_response',
      'captcha_cacheable',
      'submit',
      'form_build_id',
      'form_token',
      'form_id',
      'op',
      'correo_electronico_'
    );
    $this->addTaxonomyTermData($attributes);
    return $this->emBlueApi('registros_boletin_home', $email, $attributes);
  }

  /**
   * Delete items from a array
   *
   * @param array $array
   *  Array base
   * @param array $items
   *  Items to delete
   */
  private function deleteArrayItem(&$array, ...$items) {
    foreach ($items as $item) {
      if (isset($array[$item])) {
        unset($array[$item]);
      }
    }
  }

  /**
   * Add to the elements of type array the taxonomy term name
   *
   * @param array $array
   *  Array base
   */
  private function addTaxonomyTermData(&$array) {
    $array = array_map(function ($item) {
      if(is_array($item)){
        /**
         * @var \Drupal\taxonomy\Entity\Term $term
         */
        $terms = $this->entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($item);
        $terms = array_map(function($term){
          return $term->getName();
        }, $terms);
        $item = join(',', $terms);
      }
      return $item;
    },$array);
  }
}

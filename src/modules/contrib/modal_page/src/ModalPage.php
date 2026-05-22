<?php

namespace Drupal\modal_page;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Component\Utility\Xss;

/**
 * Modal Page Class.
 */
class ModalPage {

  use StringTranslationTrait;

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  public $queryFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Path Matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(LanguageManagerInterface $language_manager, QueryFactory $query_factory, EntityTypeManagerInterface $entity_manager, ConfigFactory $config_factory, RequestStack $request_stack, PathMatcherInterface $path_matcher, UuidInterface $uuid_service) {
    $this->languageManager = $language_manager;
    $this->queryFactory = $query_factory;
    $this->entityTypeManager = $entity_manager;
    $this->pathMatcher = $path_matcher;
    $this->request = $request_stack->getCurrentRequest();
    $this->configFactory = $config_factory;
    $this->uuidService = $uuid_service;
  }

  /**
   * Import Modal Config to Entity.
   */
  public function importModalConfigToEntity() {

    $language = $this->languageManager->getCurrentLanguage()->getId();

    $config = $this->configFactory->get('modal_page.settings');

    $modals = $config->get('modals');

    $modals_by_parameter = $config->get('modals_by_parameter');

    if (empty($modals) && empty($modals_by_parameter)) {
      return FALSE;
    }

    if (!empty($modals)) {

      $modals_settings = explode(PHP_EOL, $modals);

      foreach ($modals_settings as $modal_settings) {

        $modal = explode('|', $modal_settings);

        $path = $modal[0];

        if ($path != '<front>') {
          $path = Xss::filter($modal[0]);
        }

        $path = trim($path);
        $path = ltrim($path, '/');

        $title = Xss::filter($modal[1]);
        $title = trim($title);

        $text = Xss::filter($modal[2]);
        $text = trim($text);

        $button = Xss::filter($modal[3]);
        $button = trim($button);

        $uuid = $this->uuidService->generate();

        $modal = [
          'uuid' => $uuid,
          'title' => $title,
          'body' => $text,
          'type' => 'page',
          'pages' => $path,
          'ok_label_button' => $button,
          'langcode' => $language,
          'created' => time(),
          'changed' => time(),
        ];

        $query = db_insert('modal');
        $query->fields($modal);
        $query->execute();
      }
    }

    if (!empty($modals_by_parameter)) {

      $modals_settings = explode(PHP_EOL, $modals_by_parameter);

      foreach ($modals_settings as $modal_settings) {

        $modal = explode('|', $modal_settings);

        $parameter_settings = Xss::filter($modal[0]);

        $parameter = trim($parameter_settings);

        $parameter_data = explode('=', $parameter);

        $parameter_value = $parameter_data[1];

        $title = Xss::filter($modal[1]);
        $title = trim($title);

        $text = Xss::filter($modal[2]);
        $text = trim($text);

        $button = Xss::filter($modal[3]);
        $button = trim($button);

        $uuid = $this->uuidService->generate();

        $modal = [
          'uuid' => $uuid,
          'title' => $title,
          'body' => $text,
          'type' => 'parameter',
          'parameters' => $parameter_value,
          'ok_label_button' => $button,
          'langcode' => $language,
          'created' => time(),
          'changed' => time(),
        ];

        $query = db_insert('modal');
        $query->fields($modal);
        $query->execute();

      }
    }
  }

  /**
   * Function to check Modal will show.
   */
  public function checkModalToShow() {

    $modal_to_show = FALSE;
    $modal_parameter = FALSE;

    $current_uri = $this->request->getRequestUri();
    $current_path = ltrim($current_uri, '/');

    $is_front_page = $this->pathMatcher->isFrontPage();

    if ($is_front_page) {
      $current_path = '<front>';
    }

    $parameters = $this->request->query->all();

    if (!empty($parameters['modal'])) {
      $modal_parameter = $parameters['modal'];
    }

    $query = $this->queryFactory->get('modal_page_modal');

    if ($modal_parameter) {

      $query->condition('parameters', '%' . $modal_parameter . '%', 'like');
    }
    else {
      $query->condition('pages', '%' . $current_path . '%', 'like');
    }

    if (!empty($this->languageManager->getCurrentLanguage()->getId())) {

      $lang_code = $this->languageManager->getCurrentLanguage()->getId();

      $condition = $query->orConditionGroup()->condition('langcode', $lang_code, '=')->condition('langcode', '', '=');

      $query->condition($condition);
    }

    $modal_ids = $query->execute();

    if (!empty($modal_ids)) {
      $modal_storage = $this->entityTypeManager->getStorage('modal_page_modal');
      foreach ($modal_ids as $modal_id) {

        $type = 'page';

        $modal = $modal_storage->load($modal_id);

        $type = $modal->type->value;

        if ($type == 'parameter') {

          $parameters = $modal->parameters->value;

          $parameters = explode(PHP_EOL, $parameters);

          foreach ($parameters as $parameter) {
            if ($modal_parameter == $parameter) {
              $modal_to_show = $modal;
            }
          }
        }
        else {

          $pages = $modal->pages->value;

          $pages = explode(PHP_EOL, $pages);

          foreach ($pages as $page) {

            $path = $page;

            if ($path != '<front>') {
              $path = Xss::filter($path);
            }

            $path = trim($path);
            $path = ltrim($path, '/');

            if ($current_path == $path) {
              $modal_to_show = $modal;
              break;
            }
          }
        }
      }
    }

    if (empty($modal_to_show)) {
      return FALSE;
    }

    $id = $modal_to_show->id->value;

    $title = $modal_to_show->title->value;
    $title = Xss::filter($title);
    $title = trim($title);

    $text = $modal_to_show->body->value;
    $text = Xss::filter($text);
    $text = trim($text);

    $button = $this->t('OK');

    if (!empty($modal_to_show->ok_label_button->value)) {
      $button = $modal_to_show->ok_label_button->value;
      $button = Xss::filter($button);
      $button = trim($button);
    }

    $label_do_not_show_again = $this->t('Do not show again');

    $modal = [
      'id' => $id,
      'title' => $title,
      'text' => $text,
      'button' => $button,
      'do_not_show_again' => $label_do_not_show_again,
    ];

    return $modal;
  }

}

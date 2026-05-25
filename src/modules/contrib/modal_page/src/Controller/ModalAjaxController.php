<?php

namespace Drupal\modal_page\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ExtensionList;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\modal_page\Service\ModalPageHelperService;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller routines for Ajax routes.
 */
class ModalAjaxController extends ControllerBase {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The extension list module.
   *
   * @var \Drupal\Core\Extension\ExtensionList
   */
  protected $extensionListModule;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Modal Page Helper Service.
   *
   * @var \Drupal\modal_page\Service\ModalPageHelperService
   */
  protected $modalPageHelperService;

  /**
   * The project handler.
   *
   * @var \Drupal\Core\Extension\ProjectHandler
   */
  protected $projectHandler;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Creates a new HelpController.
   */
  public function __construct(RouteMatchInterface $route_match, ExtensionList $extension_list_module, ConfigFactoryInterface $config_factory, ModalPageHelperService $modalPageHelperService, ModuleHandlerInterface $projectHandler, EntityTypeManagerInterface $entityManager, RequestStack $requestStack) {
    $this->routeMatch = $route_match;
    $this->extensionListModule = $extension_list_module;
    $this->configFactory = $config_factory;
    $this->modalPageHelperService = $modalPageHelperService;
    $this->projectHandler = $projectHandler;
    $this->entityTypeManager = $entityManager;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('extension.list.module'),
      $container->get('config.factory'),
      $container->get('modal_page.helper'),
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function hookModalSubmit() {

    $response = new JsonResponse();
    $jsonResponse = [];

    if (empty($this->requestStack->getCurrentRequest()->request->get('id'))) {

      $jsonResponse = [
        'success' => FALSE,
        'message' => 'Invalid modal ID',
      ];

      return $response->setData($jsonResponse);
    }

    $modalId = $this->requestStack->getCurrentRequest()->request->get('id');

    // Load Modal by ID.
    $modal = $this->entityTypeManager->getStorage('modal')->load($modalId);

    if (empty($modal)) {

      $jsonResponse = [
        'success' => FALSE,
        'message' => 'Modal not found',
      ];

      return $response->setData($jsonResponse);
    }

    // Verify if User Has Access on this Modal.
    $userHasAccessOnModal = $this->modalPageHelperService->verifyIfUserHasAccessOnModal($modal);

    if (empty($userHasAccessOnModal)) {

      $jsonResponse = [
        'success' => FALSE,
        'message' => 'User does not have access to this modal',
      ];

      return $response->setData($jsonResponse);
    }

    // Load Methods.
    if (method_exists($this->projectHandler, 'invokeAllWith')) {
      $projectsThatImplementsHookModalSubmit = [];
      $this->projectHandler->invokeAllWith(
        'modal_submit',
        function (callable $hook, string $project) use (&$projectsThatImplementsHookModalSubmit) {
          $projectsThatImplementsHookModalSubmit[] = $project;
        }
      );
    }
    else {
      // Use the deprecated getImplementations() for Drupal < 9.4.
      $projectsThatImplementsHookModalSubmit = $this->projectHandler->getImplementations('modal_submit');
    }

    if (empty($projectsThatImplementsHookModalSubmit)) {

      $jsonResponse = [
        'success' => FALSE,
        'message' => 'No implementations found for modal_submit hook',
      ];

      return $response->setData($jsonResponse);
    }

    $modalState = [];
    if (!empty($this->requestStack->getCurrentRequest()->request->all('modal_state'))) {
      $modalState = $this->requestStack->getCurrentRequest()->request->all('modal_state');
    }

    // Arguments to be sent to Hook.
    $argsToHookModalSubmit = [
      'modal' => $modal,
      'modal_state' => $modalState,
      'modal_id' => $modalId,
    ];

    $hookNameModalIdModalSubmit = $modalId . '_modal_submit';

    $this->projectHandler->invokeAll($hookNameModalIdModalSubmit, $argsToHookModalSubmit);

    $hookNameModalSubmit = 'modal_submit';

    $this->projectHandler->invokeAll($hookNameModalSubmit, $argsToHookModalSubmit);

    $jsonResponse = [
      'success' => TRUE,
    ];

    return $response->setData($jsonResponse);
  }

  /**
   * {@inheritdoc}
   */
  public function enableBootstrapAutomatically() {

    $response = new JsonResponse();
    $jsonResponse = [];

    $settings = $this->configFactory->getEditable('modal_page.settings');

    $verifyLoadBootstrapAutomatically = $settings->get('verify_load_bootstrap_automatically');

    if (empty($verifyLoadBootstrapAutomatically)) {

      $jsonResponse = [
        'success' => FALSE,
        'message' => 'Bootstrap auto-load verification failed',
      ];

      return $response->setData($jsonResponse);
    }

    $settings->set('load_bootstrap', TRUE);
    $settings->save();

    // If is running on Drupal 11 or above, use BS5 instead of BS3.
    if (\Drupal::VERSION >= '11.0.0') {
      $config = \Drupal::configFactory()->getEditable('modal_page.settings');

      $config->set('bootstrap_version', '5x');

      $config->save();
    }

    $jsonResponse = [
      'success' => TRUE,
    ];

    return $response->setData($jsonResponse);
  }

}

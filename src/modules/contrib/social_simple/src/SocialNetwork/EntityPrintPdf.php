<?php

namespace Drupal\social_simple\SocialNetwork;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * The social network Twitter.
 */
class EntityPrintPdf implements SocialNetworkInterface {

  use StringTranslationTrait;

  /**
   * The entity print view route.
   */
  const ENTITY_PRINT_ROUTE = 'entity_print.view';

  /**
   * The export type engine.
   */
  const ENTITY_PRINT_EXPORT_TYPE = 'pdf';

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Extension\ModuleHandlerInterface
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new Entity Print Pdf object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return 'entity_print_pdf';
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('PDF');
  }

  /**
   * {@inheritdoc}
   */
  public function getShareLink($share_url, $title = '', EntityInterface $entity = NULL, array $additional_options = []) {
    $link = [
      'url' => Url::fromUserInput('#'),
      'title' => ['#markup' => '<i class="fa fa-file-pdf-o"></i><span class="visually-hidden">' . $this->getLabel() . '</span>'],
      'attributes' => ['style' => 'display: none;'],
    ];
    if (!$this->moduleHandler->moduleExists('entity_print')) {
      return $link;
    }
    if (!$entity instanceof ContentEntityInterface) {
      return $link;
    }

    $entity_type_id = $entity->getEntityTypeId();
    $entity_id = $entity->id();

    $enabled_entity_type_ids = $this->configFactory->get('entity_print.settings')->get('enabled_entity_type_ids');
    if (!empty($enabled_entity_type_ids) && !in_array($entity_type_id, $enabled_entity_type_ids)) {
      return $link;
    }

    $route_params = [
      'entity_type' => $entity_type_id,
      'entity_id' => $entity_id,
      'export_type' => trim(self::ENTITY_PRINT_EXPORT_TYPE, '_engine'),
    ];

    $options = [];
    if ($additional_options) {
      foreach ($additional_options as $id => $value) {
        $options['query'][$id] = $value;
      }
    }

    $url = Url::fromRoute(self::ENTITY_PRINT_ROUTE, $route_params, $options);
    $link = [
      'url' => $url,
      'title' => ['#markup' => '<i class="fa fa-file-pdf-o"></i><span class="visually-hidden">' . $this->getLabel() . '</span>'],
      'attributes' => $this->getLinkAttributes($this->getLabel()),
    ];

    return $link;
  }

  /**
   * {@inheritdoc}
   */
  public function getLinkAttributes($network_name) {
    $attributes = [
      'title' => $network_name,
      'target' => '_blank',
      'rel' => 'noopener noreferrer nofollow',
    ];
    return $attributes;
  }

}

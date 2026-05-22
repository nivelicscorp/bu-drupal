<?php

namespace Drupal\bu_sections\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Promotional Content' Block
 *
 * @Block(
 *   id = "other_contents_home_block",
 *   admin_label = @Translation("Other contents - insights"),
 * )
 */
class OtherContentsHomeBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Creates a HelpBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration, $plugin_id, $plugin_definition, $container->get('current_route_match'), $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $nids  = [];
    $items = [];
    $term_id = null;
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if (\Drupal::routeMatch()->getRouteName() == 'entity.taxonomy_term.canonical') {
      $term_id = \Drupal::routeMatch()->getRawParameter('taxonomy_term');
    } else{
      $node = \Drupal::routeMatch()->getParameter('node');
      if ($node instanceof \Drupal\node\NodeInterface) {
        if ($node->bundle() == 'lawyer') {
          // You can get nid and anything else you need from the node object.
          $nids[] = (isset($node->get('field_boletin')->getValue()[0]['target_id'])) ? $node->get('field_boletin')->getValue()[0]['target_id'] : '';
          $nids[] = (isset($node->get('field_podcast')->getValue()[0]['target_id'])) ? $node->get('field_podcast')->getValue()[0]['target_id'] : '';
          $nids[] = (isset($node->get('field_informe')->getValue()[0]['target_id'])) ? $node->get('field_informe')->getValue()[0]['target_id'] : '';
          $nids[] = (isset($node->get('field_recurso')->getValue()[0]['target_id'])) ? $node->get('field_recurso')->getValue()[0]['target_id'] : '';
        }
      }
    }
    if (empty($nids)) {
      $nids[] = $this->getContentId('bulletin', $term_id);
      $nids[] = $this->getContentId('podcast', $term_id);
      $nids[] = $this->getContentId('recursos', $term_id);
      $nids[] = $this->getContentId('informe', $term_id);
    }

    foreach ($nids as $nid) {
      if ($nid != '') {
        $node = Node::load($nid);
        if ($node->hasTranslation($language)) {
          $translation = $node->getTranslation($language);
          $items[] = $translation;
        }
        else {
          $items[] = $node;
        }
      }
    }

    if ($items) {
      return [
        '#theme' => 'bu_other_contents_block',
        '#items' => $items,
        '#language ' => $language ,
        '#cache' => [
          'disabled' => TRUE
        ],
      ];
    }



    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  public function getContentId($type, $term_id) {
    $query = \Drupal::entityQuery('node')
    ->accessCheck(TRUE);
    $query->condition('status', 1);
    $query->condition('type', $type);
    if ($term_id != null) {
      $orGroup = $query->orConditionGroup()
        ->condition('field_area', $term_id)
        ->condition('field_industrias', $term_id)
        ->condition('field_nuevos_servicios', $term_id);
      $query->condition($orGroup);
    }
    $query->range(0, 1);
    $query->sort('created' , 'DESC');
    $entity_ids = $query->execute();
    $nid = '';
    foreach ($entity_ids as $id){
      $nid = $id;
    }
    return $nid;
  }

}

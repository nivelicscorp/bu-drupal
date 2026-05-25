<?php

namespace Drupal\bu_sections\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a 'Promotional Content' Block
 *
 * @Block(
 *   id = "subsections_lateral_menu_block",
 *   admin_label = @Translation("Subsections Lateral Menu Block"),
 * )
 */
class SubsectionsLateralMenuBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

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
   *
   * @todo Refactor new static() to use parent::create() pattern for Drupal 11 compatibility.
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
    $items = [];
    $route_name = $this->routeMatch->getRouteName();

    $language =  \Drupal::languageManager()->getCurrentLanguage()->getId();

    if ($route_name == 'entity.taxonomy_term.canonical') {
      $taxonomy_term = $this->routeMatch->getParameter('taxonomy_term');
      $current_tid = $taxonomy_term->id();
//      if ($taxonomy_term->bundle() == 'section') {
      if ($parents = $this->entityTypeManager->getStorage('taxonomy_term')->loadParents($current_tid)) {
        $parent_section = reset($parents);
        if ($children_sections = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('section', $parent_section->id(), 1, TRUE)) {
          foreach ($children_sections as $section_term) {
            if($section_term->hasTranslation($language)){
              $translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($section_term, $language);
              $section_term_id = $section_term->id();
              $items[] = [
                'url' => \Drupal\Core\Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $translated_term->id()]),
                'text' => $translated_term->label(),
                'active' => $current_tid == $section_term_id,
              ];
            }
          }
        }
      }
//      }
    }

    if ($items) {
      return [
        '#theme' => 'bu_subsections_lateral_menu',
        '#items' => $items,
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

}

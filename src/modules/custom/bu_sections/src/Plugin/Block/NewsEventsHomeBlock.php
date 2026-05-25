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
 *   id = "news_events_home_block",
 *   admin_label = @Translation("News and events home new"),
 * )
 */
class NewsEventsHomeBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

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
    $connection = \Drupal::database();
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $noticias = $eventos = array();

    $q = "SELECT DISTINCT node_field_data.created AS node_field_data_created, node_field_data.nid AS nid
      FROM 
      {node_field_data} node_field_data
      LEFT JOIN {node__field_pertenece_a_boletin} node__field_pertenece_a_boletin ON node_field_data.nid = node__field_pertenece_a_boletin.entity_id AND node__field_pertenece_a_boletin.deleted = '0'
      WHERE (node_field_data.status = '1') AND (node_field_data.type IN ('noticia')) AND (node_field_data.langcode IN ('$language')) AND (node__field_pertenece_a_boletin.field_pertenece_a_boletin_value = '0')
      ORDER BY node_field_data_created DESC
      LIMIT 3 OFFSET 4";

    $query = $connection->query($q);
    $result = $query->fetchAll();
    foreach ($result as $record) {
      $new = Node::load($record->nid);
      $translation = $new->getTranslation($language);
      $areas = $translation->get('field_area')->getValue();
      $translation->area = $areas[0]['target_id'];
      $noticias[] = $translation;
    }

    $q_eventos = "SELECT DISTINCT node_field_data.created AS node_field_data_created, node_field_data.nid AS nid
        FROM 
        {node_field_data} node_field_data
        LEFT JOIN {node__field_es_privado} node__field_es_privado ON node_field_data.nid = node__field_es_privado.entity_id AND node__field_es_privado.deleted = '0'
        WHERE (node_field_data.status = '1') AND (node_field_data.type IN ('evento')) AND (node_field_data.langcode IN ('$language')) AND (node__field_es_privado.field_es_privado_value = '0')
        ORDER BY node_field_data_created DESC
        LIMIT 3 OFFSET 0";

    $query = $connection->query($q_eventos);
    $result = $query->fetchAll();
    foreach ($result as $record) {
      $event = Node::load($record->nid);
      $translation = $event->getTranslation($language);
      $eventos[] = $translation;
    }
    $items = array();
    $c = 0;
    if(count($eventos) > 0){
      foreach($eventos as $ev){
        $items['eventos'][] = $ev;
      }
    }
    if(count($items['eventos']) < 3 && count($noticias) > 0){
      $ocupadas = count($items['eventos']);
      foreach($noticias as $nt){
        $items['noticias'][] = $nt;
        $ocupadas++ ;
        if($ocupadas == 3){
          break;
        }
      }
    }
    if ($items) {
      return [
        '#theme' => 'bu_news_and_events_block',
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

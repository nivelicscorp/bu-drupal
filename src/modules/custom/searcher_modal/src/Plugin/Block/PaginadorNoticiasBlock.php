<?php
/**
 * @file
 * Contains \Drupal\searcher_modal\Plugin\Block\ModalBlock.
 */

namespace Drupal\searcher_modal\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Serialization\Json;

/**
 * Provides a 'Modal' Block
 *
 * @Block(
 *   id = "paginador_noticias_block",
 *   admin_label = @Translation("Paginador noticias block"),
 * )
 */
class PaginadorNoticiasBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $paginador = '';
    if (isset($_GET['boletin'])) {
      $language = \Drupal::languageManager()->getCurrentLanguage();
      $node = NULL;
      foreach (\Drupal::routeMatch()->getParameters() as $param) {
        if ($param instanceof \Drupal\Core\Entity\EntityInterface) {
          $node = $param;
          $nid = $node->id();
        }
      }
      $url_options = [
        'language' => $language,
        'query' => array(
          'boletin' => $_GET['boletin']
        ),
      ];
      $boletin = \Drupal\node\Entity\Node::load($_GET['boletin']);
      $relacionadas = $boletin->get('field_noticias_asociadas')->getValue();
      if(count($relacionadas) > 0){
        foreach ($relacionadas as $k => $noticia){
          if($noticia['target_id'] == $nid){
            if(isset($relacionadas[$k - 1])) {
              $url = Url::fromRoute('entity.node.canonical', ['node' => $relacionadas[$k - 1]['target_id']], $url_options);
              $paginador .= '<a href="' . $url->toString() . '" class="anterior-link">' . t('Previous') . '</a>';
            }
            if(isset($relacionadas[$k + 1])) {
              $url = Url::fromRoute('entity.node.canonical', ['node' => $relacionadas[$k + 1]['target_id']], $url_options);
              $paginador .= '<a href="' . $url->toString() . '" class="siguiente-link">' . t('Next') . '</a>';
            }
          }
        }
      }
    }
    return array(
      '#markup' => '<div class="block-views-blockduplicado-de-noticias-asociadas-boletines-block-1">' . $paginador . '</div>',
      );
  }
  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }
}

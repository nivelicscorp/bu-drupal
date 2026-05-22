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
 *   id = "modal_searcher_block",
 *   admin_label = @Translation("Modal searcher block"),
 * )
 */
class ModalBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    /*$link_url = Url::fromRoute('searcher_modal.modal');
    $link_url->setOptions([
      'attributes' => [
        'class' => ['use-ajax', 'button', 'button--small'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(['width' => 400]),
      ]
    ]);

    return array(
      '#type' => 'markup',
      '#markup' => Link::fromTextAndUrl(t('Open modal'), $link_url)->toString(),
      '#attached' => ['library' => ['core/drupal.dialog.ajax']]
    );*///\Drupal\searcher_modal\Form\SearcherModalForm
    $form = \Drupal::formBuilder()->getForm('Drupal\searcher_modal\Form\SearcherModalForm');
    return $form;
  }
}

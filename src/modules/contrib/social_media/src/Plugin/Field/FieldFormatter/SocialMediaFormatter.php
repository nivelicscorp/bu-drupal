<?php

namespace Drupal\social_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'field_example_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "social_media_default",
 *   module = "social_media",
 *   label = @Translation("Rendered social media"),
 *   field_types = {
 *     "social_media"
 *   }
 * )
 */
class SocialMediaFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The AccountProxyInterface.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->blockManager = $container->get('plugin.manager.block');
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {

      $config = [];
      $block_instance = $this->blockManager->createInstance('social_sharing_block', $config);
      // Some blocks might implement access check.
      $access_result = $block_instance->access($this->account);
      // Return empty render array if user doesn't have access.
      // $access_result can be boolean or an AccessResult class.
      if (is_object($access_result) && $access_result->isForbidden() || is_bool($access_result) && !$access_result) {
        return [];
      }

      $elements[$delta] = [
        '#theme' => 'block',
        '#attributes' => [],
        '#configuration' => $block_instance->getConfiguration(),
        '#plugin_id' => $block_instance->getPluginId(),
        '#base_plugin_id' => $block_instance->getBaseId(),
        '#derivative_plugin_id' => $block_instance->getDerivativeId(),
        'content' => $block_instance->build(),
      ];
    }

    return $elements;
  }

}

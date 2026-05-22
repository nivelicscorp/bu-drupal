<?php

namespace Drupal\social_media\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Template\Attribute;
use Drupal\social_media\Event\SocialMediaEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'SocialSharingBlock' block.
 *
 * @Block(
 *  id = "social_sharing_block",
 *  admin_label = @Translation("Social Sharing block"),
 * )
 */
class SocialSharingBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * An event dispatcher instance to use for configuration events.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The list of available modules.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $extensionListModule;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->configFactory = $container->get('config.factory');
    $instance->token = $container->get('token');
    $instance->eventDispatcher = $container->get('event_dispatcher');
    $instance->currentPath = $container->get('path.current');
    $instance->extensionListModule = $container->get('extension.list.module');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    global $base_url;
    $library = ['social_media/basic'];
    $settings = [];
    $icon_path = $base_url . '/' . $this->extensionListModule->getPath('social_media') . '/icons/';
    $elements = [];
    $social_medias = $this->configFactory->get('social_media.settings')
      ->get('social_media');

    // Call pre_execute event before doing anything.
    $event = new SocialMediaEvent($social_medias);
    $this->eventDispatcher->dispatch($event, 'social_media.pre_execute');
    $social_medias = $event->getElement();

    $social_medias = $this->sortSocialMedias($social_medias);
    foreach ($social_medias as $name => $social_media) {

      // Replace api url with different link.
      if ($name == "email" && isset($social_media['enable_forward']) && $social_media['enable_forward']) {
        $social_media['api_url'] = str_replace('mailto:', '/social-media-forward', $social_media['api_url']);
        $social_media['api_url'] .= '&destination=' . $this->currentPath->getPath();
        if (isset($social_media['show_forward']) && $social_media['show_forward'] == 1) {
          $library[] = 'core/drupal.dialog.ajax';
        }
      }

      if ($social_media['enable'] == 1 && !empty($social_media['api_url'])) {
        $elements[$name]['text'] = $social_media['text'];
        $elements[$name]['api'] = new Attribute([$social_media['api_event'] => $this->token->replace($social_media['api_url'])]);

        if (isset($social_media['library']) && !empty($social_media['library'])) {
          $library[] = $social_media['library'];
        }
        if (isset($social_media['attributes']) && !empty($social_media['attributes'])) {
          $elements[$name]['attr'] = $this->socialMediaConvertAttributes($social_media['attributes']);
        }
        if (isset($social_media['drupalSettings']) && !empty($social_media['drupalSettings'])) {
          $settings['social_media'] = $this->socialMediaConvertDrupalSettings($social_media['drupalSettings']);
        }

        if (isset($social_media['default_img']) && $social_media['default_img']) {
          $elements[$name]['img'] = $icon_path . $name . '.svg';
        }
        elseif (!empty($social_media['img'])) {
          $elements[$name]['img'] = $base_url . '/' . $social_media['img'];
        }

        if (isset($social_media['enable_forward']) && $social_media['enable_forward']) {
          if (isset($social_media['show_forward']) && $social_media['show_forward'] == 1) {
            $elements[$name]['forward_dialog'] = $social_media['show_forward'];
          }

        }

      }
    }

    $build = [];

    // Call prerender event before render.
    $event = new SocialMediaEvent($elements);
    $this->eventDispatcher->dispatch($event, 'social_media.pre_render');
    $elements = $event->getElement();

    $build['social_sharing_block'] = [
      '#theme' => 'social_media_links',
      '#elements' => $elements,
      '#attached' => [
        'library' => $library,
        'drupalSettings' => $settings,
      ],
    ];
    return $build;
  }

  /**
   * Sorts the provided social media elements by weight.
   *
   * @param array $element
   *   An associative array of elements to sort.
   */
  protected function sortSocialMedias(array &$element) {
    $weight = [];
    foreach ($element as $key => $row) {
      $weight[$key] = $row['weight'];
    }
    array_multisort($weight, SORT_ASC, $element);
    return $element;
  }

  /**
   * Converts a pipe-delimited attributes string to an Attributes array.
   *
   * @param string $variables
   *   A string of multi-line pipe delimited key-value pairs.
   *
   * @return array
   *   An array of attribute objects.
   */
  protected function socialMediaConvertAttributes($variables) {
    $variable = explode("\n", $variables);
    $attributes = [];
    if (count($variable)) {
      foreach ($variable as $each) {
        if ($each === '') {
          continue;
        }
        $var = explode("|", $each);
        $value = str_replace(["\r\n", "\n", "\r"], "", $var[1]);
        $attributes[$var[0]] = new Attribute([$var[0] => $value]);
      }
    }
    return $attributes;
  }

  /**
   * Converts a pipe-delimited drupal settings string to an associative array.
   *
   * @param string $variables
   *   A string of multi-line pipe delimited key-value pairs.
   *
   * @return array
   *   An associative array.
   */
  protected function socialMediaConvertDrupalSettings($variables) {
    $variable = explode("\n", $variables);
    $settings = [];
    if (count($variable)) {
      foreach ($variable as $each) {
        $var = explode("|", $each);
        $settings[$var[0]] = str_replace(["\r\n", "\n", "\r"], "", $var[1]);
      }
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'social_media:' . $this->currentPath->getPath(),
      'config:social_media.settings',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}

<?php

namespace Drupal\video_embed_facebook\Plugin\video_embed_field\Provider;

use Drupal\video_embed_field\ProviderPluginBase;

/**
 * @VideoEmbedProvider(
 *   id = "facebook",
 *   title = @Translation("Facebook")
 * )
 */
class Facebook extends ProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function renderEmbedCode($width, $height, $autoplay) {
    $embed_code = [
      '#type' => 'video_embed_iframe',
      '#provider' => 'facebook',
      '#url' => sprintf('https://www.facebook.com/plugins/video.php?href=%s', $this->getInput()),
      '#query' => [
        'autoplay' => $autoplay,
        'show_text' => '0',
      ],
      '#attributes' => [
        'width' => $width,
        'height' => $height,
        'frameborder' => '0',
        'allowfullscreen' => 'allowfullscreen',
      ],
    ];

    return $embed_code;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteThumbnailUrl() {
    return sprintf('https://graph.facebook.com/%d/picture', $this->getVideoId());
  }

  /**
   * {@inheritdoc}
   */
  public static function getIdFromInput($input) {
    preg_match('/^https?:\/\/(www\.)?facebook.com\/([\w\-\.]*\/videos\/|video\.php\?v\=)(?<id>[0-9]*)\/?$/', $input, $matches);
    return isset($matches['id']) ? $matches['id'] : FALSE;
  }

}

<?php

namespace Drupal\bu_twig\TwigExtension;

use Drupal\Core\Entity\EntityTypeManagerInterface,
    Drupal\Core\Render\RendererInterface,
    Drupal\Core\Template\TwigExtension,
    Drupal\image\Entity\ImageStyle,
    Drupal\file\Entity\File,
    Twig\TwigFilter,
    Drupal\Core\Routing\UrlGeneratorInterface,
    Drupal\Core\Theme\ThemeManagerInterface,
    Twig\Extension\AbstractExtension,
    Drupal\Core\Datetime\DateFormatterInterface,
    Drupal\Core\File\FileUrlGeneratorInterface;

/**
 * @package Twig Utilities
 */
class Util extends AbstractExtension {

  /**
   * The entity type manager.
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var TwigExtension
   */
  protected $coreTwigExtension;

  /**
   * @var string
   */
  protected $themeName;

  /**
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param RendererInterface $renderer
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, UrlGeneratorInterface $url_generator, ThemeManagerInterface $theme_manager, DateFormatterInterface $date_formatter, FileUrlGeneratorInterface $file_url_generator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->coreTwigExtension = new TwigExtension($renderer, $url_generator, $theme_manager, $date_formatter, $file_url_generator);
    $this->themeName = \Drupal::theme()->getActiveTheme()->getName();
  }

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'twig_food.twig_extension';
  }

  /**
   * Generate a list of all twig functions
   * @return array
   */
  public function getFunctions() {
    return [
      new TwigFilter('svg', [$this, 'renderSVG'], ['is_safe' => ['html']]),
      new TwigFilter('load_block', [$this, 'loadBlock']),
      new TwigFilter('load_region', [$this, 'loadRegion']),
      new TwigFilter('get_main_node', [$this, 'getMainNode']),
      new TwigFilter('load_gallery_prev', [$this, 'loadGalleryPrev']),
      new TwigFilter('load_gallery_next', [$this, 'loadGalleryNext']),
      new TwigFilter('load_gallery_thumbs', [$this, 'loadGalleryThumbs']),
      new TwigFilter('view_embed', [$this, 'viewEmbed']),
      new TwigFilter('url_alias_by_path', [$this, 'getUrlAliasByPath']),
      new TwigFilter('blazy_image', [$this, 'renderBlazyImage']),
      new TwigFilter('blazy_image_style', [$this, 'renderBlazyImageStyle']),
    ];
  }

  /**
   * Retrieves the generated url for the given path
   * todo: check the context
   * @param $path
   * @return mixed
   */
  public function getUrlAliasByPath($path) {
    return \Drupal::service('path.alias_manager')->getAliasByPath($path);
  }

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new TwigFilter('force_raw', [$this, 'renderForceRaw']),
      new TwigFilter('max_length', [$this, 'renderWithMaxLength']),
      new TwigFilter('image_style', [$this, 'renderImageStyle']),
    ];
  }

  /**
   * Return SVG source code as string to Twig - usage: {{ svg('bgCarousel.svg')|raw }}
   * @param $path
   * @return string
   */
  public function renderSVG($path) {
    $theme = \Drupal::service('extension.list.theme')->getPath($this->themeName);
    $fullPath = "{$theme}/images/{$path}";
    $handle = fopen($fullPath, "r");
    $contents = fread($handle, filesize($fullPath));
    fclose($handle);

    return $contents;
  }

  /**
   * Make render of var, removes html comments from string, do strip_tags, remove new lines => forceRaw string
   * Example: A string which has value  <!-- Start DEBUG --> ABCD <!-- End DEBUG -->
   * will be returned the output ABCD after using the the following function.
   * @param string $string A string, which have html comments.
   * @return string A string, which have no html comments.
   */
  public function renderForceRaw($string) {
    $rendered = $this->coreTwigExtension->renderVar($string);
    $withoutComments = preg_replace('/<!--(.|\s)*?-->/', '', $rendered);
    $forceRaw = strip_tags(str_replace(["\n", "\r"], '', html_entity_decode($withoutComments, ENT_QUOTES, 'UTF-8')));

    return $forceRaw;
  }

  /**
   * Check string length and return him summary or in original
   * @param $string
   * @param int $max Max. length of string
   * @param bool|true $dots add "..." after summary string
   * @return string
   */
  public function renderWithMaxLength($string, $max = 0, $dots = true) {
    $field = $this->renderForceRaw($string);

    if (mb_strlen($field) > $max && $max > 0) {
      $break = "*-*-*";
      $wrap = wordwrap($field, $max, $break);
      $items = explode($break, $wrap);
      $string = (isset($items[0]) ? $items[0] : "") . ($dots ? "..." : "");
    }

    return $string;
  }

  /**
   * Return array of selected block
   * @param $id string
   * @return array|string
   */
  public function loadBlock($id) {
    $block = $this->entityTypeManager->getStorage('block')->load($id);
    return $block ? $this->entityTypeManager->getViewBuilder('block')->view($block) : '';
  }

  /**
   * Render region by id
   * @param $id
   * @return array
   */
  public function loadRegion($id) {
    $blocks = $this->entityTypeManager->getStorage('block')->loadByProperties([
      'region' => $id,
      'theme' => $this->themeName
    ]);

    $result = [];
    foreach ($blocks as $id => $values) {
      $result[] = $this->loadBlock($id);
    }

    return $result;
  }

  /**
   * Prev gallery
   * @param $id
   * @param string $thumbnail
   * @return array|null
   */
  public function loadGalleryPrev($id, $thumbnail = 'thumbnail') {
    return $this->getMediaData($id, '<', 'DESC', $thumbnail);
  }

  /**
   * Next gallery
   * @param $id
   * @param string $thumbnail
   * @return array|null
   */
  public function loadGalleryNext($id, $thumbnail = 'thumbnail') {
    return $this->getMediaData($id, '>', 'ASC', $thumbnail);
  }

  /**
   * Load gallery images
   * @param $id
   * @param string $thumbnail
   * @return array
   */
  public function loadGalleryThumbs($id, $thumbnail = 'thumbnail') {
    $gallery = $this->entityTypeManager
        ->getStorage('media')
        ->load($id);

    $images = $gallery->get('field_media_images');

    if ($images) {
      $result = [];
      foreach ($images as $image) {
        $mid = $image->entity->id();
        $fileEntity = $image->entity->field_image->entity;
        $fid = $image->entity->field_image->entity->id();
        $imageUrl = $fileEntity->getFileUri();

        $result[] = [
          'mid' => $mid,
          'fid' => $fid,
          'thumb' => ImageStyle::load($thumbnail)->buildUrl($imageUrl),
        ];
      }

      return $result;
    }

    return [];
  }

  /**
   * Load main node object anywhere
   * @param bool|true $returnId
   * @return mixed|null
   */
  public function getMainNode($returnId = true) {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node) {
      return $returnId ? $node->id() : $node;
    }

    return null;
  }

  /**
   * Load one gallery
   * @param $currentId
   * @param $dateComparator
   * @param $sortOrder
   * @param $thumbnail
   * @return array|null
   */
  public function getMediaData($currentId, $dateComparator, $sortOrder, $thumbnail) {
    /**
     * @var \Drupal\media_entity\Entity\Media
     */
    $current = $this->entityTypeManager
        ->getStorage('media')
        ->load($currentId);

    if (!$current) {
      return null;
    }

    $prev_or_next = \Drupal::entityQuery('media')
        ->condition('bundle', $current->bundle())
        ->condition('status', 1)
        ->condition('created', $current->getCreatedTime(), $dateComparator)
        ->sort('created', $sortOrder)
        ->range(0, 1)
        ->execute();

    if (!$prev_or_next) {
      return null;
    }

    $gallery = $this->entityTypeManager
        ->getStorage('media')
        ->load(array_values($prev_or_next)[0]);

    $all = $gallery->get('field_media_images');
    if (isset($all[0])) {
      $file = $all[0]->entity->field_image->entity->getFileUri();

      return [
        'id' => $gallery->id(),
        'title' => $gallery->label(),
        'path' => $gallery->toUrl()->toString(),
        'images' => $all,
        'thumb' => ImageStyle::load($thumbnail)->buildUrl($file)
      ];
    }

    return null;
  }

  /**
   * @param $viewName
   * @param $displayId
   * @return string
   */
  public function viewEmbed($viewName, $displayId) {
    if ($viewName && $displayId) {
      $result = views_embed_view($viewName, $displayId);
      if ($result) {
        return $this->coreTwigExtension->renderVar($result);
      }
    }

    return "Missing viewName or displayId parameter";
  }

  /**
   * Render an image with a given image_style.
   * @param integer $fid The entity id of the File that corresponds to the image to be rendered
   * @param string $image_style The style name
   * @param array $attributes Associative array of attributes to be placed in the img tag.
   * @return array Renderable array with '#theme' => 'image_style'.
   */
  public function renderImageStyle($fid, $image_style = NULL, $attributes = []) {
    $file = File::load($fid);

    if ($file) {
      if (substr_count($file->getMimeType(), 'image/')) {
        if ($image_style) {
          return [
            '#theme' => 'image_style',
            '#style_name' => $image_style,
            '#uri' => $file->uri->value,
            '#attributes' => $attributes,
          ];
        }
        else {
          return [
            '#theme' => 'image',
            '#uri' => $file->uri->value,
            '#attributes' => $attributes,
          ];
        }
      }
    }

    return '';
  }

  /**
   * Render an image with with metadata for b-lazy.
   * @param array $attributes Associative array of attributes to be placed in the img tag.
   * @return array Renderable array with '#theme' => 'image'.
   */
  public function renderBlazyImage($attributes = []) {
    $moduleHandler = \Drupal::service('module_handler');
    if (!empty($attributes['data-src'])) {
      if ($moduleHandler->moduleExists('blazy')) {
        $attributes['src'] = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
      }
      else {
        $attributes['src'] = $attributes['data-src'];
      }
      $attributes['class'] = 'b-lazy';
      return [
        '#theme' => 'image',
        '#attributes' => $attributes,
      ];
    }
    return '';
  }

  /**
   * Render an image with metadata for b-lazy and a given image style.
   * @param int $fid File id of the image to be rendered
   * @param string $image_style image style to be applied
   * @param array $attributes Associative array of attributes to be placed in the img tag.
   * @return array Renderable array with '#theme' => 'image'.
   */
  public function renderBlazyImageStyle($fid, $image_style, $attributes = []) {
    $moduleHandler = \Drupal::service('module_handler');
    $file = File::load($fid);
    if ($file) {
      $file_uri = $file->getFileUri();
      $attributes['data-src'] = ImageStyle::load($image_style)->buildUrl($file_uri);
    }

    if (!empty($attributes['data-src'])) {
      if ($moduleHandler->moduleExists('blazy')) {
        $attributes['src'] = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
        $attributes['class'] = 'b-lazy';
        return [
          '#theme' => 'image',
          '#attributes' => $attributes,
        ];
      }
      else {
        $attributes['src'] = $attributes['data-src'];
        return [
          '#theme' => 'image_style',
          '#style_name' => $image_style,
          '#uri' => $attributes['data-src'],
          '#attributes' => $attributes,
        ];
      }
    }
    return '';
  }

}

<?php

namespace Drupal\bu_ckeditor\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to switch the visualization of the media gallery entity
 * depending on where it is being viewed: the ckeditor or a node landing page
 *
 * @Filter(
 *   id = "entity_gallery_embed_transform_formatter",
 *   title = @Translation("Switch visualization for embedded galleries. <strong>Important: use it before the 'Display embedded entities' filter </strong>"),
 *   description = @Translation("When an embedded gallery is in the context of an editor, display it as a thumbnail and when the gallery is being viewed in a node landing page, display it using the full view mode."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class EntityGalleryEmbedTransformFormatter extends FilterBase {
  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    $route_match = \Drupal::routeMatch();
    $route_name = $route_match->getRouteName();
    
    // Get the route name parts
    $route_name_parts = explode('.', $route_name);
    $route_name_parts = array_filter($route_name_parts);
    
    // Identify if the route corresponds to an entity page
    if (count($route_name_parts) == 3 && reset($route_name_parts) == 'entity' && end($route_name_parts) == 'canonical') {
      $entity_type = $route_name_parts[1];
      $entity = \Drupal::routeMatch()->getParameter($entity_type);
    }
    
    if (strpos($text, 'data-entity-type') !== FALSE && (strpos($text, 'data-entity-embed-display') !== FALSE || strpos($text, 'data-view-mode') !== FALSE)) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//drupal-entity[@data-entity-type and @data-embed-button and (@data-entity-uuid or @data-entity-id) and (@data-entity-embed-display or @data-view-mode)]') as $node) {
        // Check if the entity is being embedded via the 'media_gallery' embed
        // button
        if ($node->getAttribute('data-embed-button') == 'media_gallery') {
          try {
            // If a node object exists, it means the embedded gallery entity is
            // being viewed in node landing page, so we want to display it using
            // the full view mode
            if (isset($entity) && is_object($entity) && ($entity instanceof \Drupal\Core\Entity\ContentEntityInterface)) {
              $node->setAttribute('data-entity-embed-display', 'view_mode:media.full');
            }
            // If no node object exists, it means the gallery is in the context
            // of a editor, so we display it as a thumbnail
            else {
              $node->setAttribute('data-entity-embed-display', 'entity_reference:media_thumbnail');
              $node->setAttribute('data-entity-embed-display-settings', '{"image_style":"max_325x325","image_link":""}');
              $node->setAttribute('data-caption', 'Aquí irá tu galería');
            }
          }
          catch (\Exception $e) {
            watchdog_exception('entity_embed', $e);
          }
        }
      }

      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
  }

}

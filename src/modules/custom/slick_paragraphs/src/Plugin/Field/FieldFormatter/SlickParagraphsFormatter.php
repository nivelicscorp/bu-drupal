<?php

namespace Drupal\slick_paragraphs\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\slick\Plugin\Field\FieldFormatter\SlickMediaFormatter;

/**
 * Plugin implementation of the 'Slick Paragraphs Media' formatter.
 *
 * @FieldFormatter(
 *   id = "slick_paragraphs_media",
 *   label = @Translation("Slick Paragraphs Media"),
 *   description = @Translation("Display the rich paragraph as a Slick Carousel."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
class SlickParagraphsFormatter extends SlickMediaFormatter {

  /**
   * {@inheritdoc}
   */
  protected function getPluginScopes(): array {
    $target_type = $this->getFieldSetting('target_type');
    $media       = $this->getFieldOptions(['entity_reference'], $target_type, 'media', FALSE);
    $stages      = ['image', 'entity_reference'];
    $stages      = $this->getFieldOptions($stages, $target_type);

    return [
      'images'   => $stages,
      'overlays' => $stages + $media,
    ] + parent::getPluginScopes();
  }

  /**
   * {@inheritdoc}
   */
  public function prepareView(array $entities_items) {
    // Entity revision loading currently has no static/persistent cache and no
    // multiload. As entity reference checks _loaded, while we don't want to
    // indicate a loaded entity, when there is none, as it could cause errors,
    // we actually load the entity and set the flag.
    foreach ($entities_items as $items) {
      foreach ($items as $item) {

        if ($item->entity) {
          $item->_loaded = TRUE;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $storage = $field_definition->getFieldStorageDefinition();

    // Excludes host, prevents complication with multiple nested paragraphs.
    $paragraph = $storage->getTargetEntityTypeId() === 'paragraph';
    return $paragraph && $storage->isMultiple() && $storage->getSetting('target_type') === 'paragraph';
  }

}

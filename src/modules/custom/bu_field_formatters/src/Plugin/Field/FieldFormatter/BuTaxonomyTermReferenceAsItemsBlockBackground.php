<?php

namespace Drupal\bu_field_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'bu_taxonomy_term_reference_as_items_block_background' formatter.
 *
 * @FieldFormatter(
 *   id = "bu_taxonomy_term_reference_as_items_block_background",
 *   label = @Translation("Taxonomy terms in a Simple Items Block"),
 *   description = @Translation("Display taxonomy terms in a Simple Items Block. See theme bu_simple_items_block_with_background"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class BuTaxonomyTermReferenceAsItemsBlockBackground extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $default_settings = parent::defaultSettings();
    $default_settings['title'] = '';
    $default_settings['is_floating'] = FALSE;
    $default_settings['background_color_options'] = [
      'red' => t('Red'),
      'blue' => t('Blue'),
    ];
    $default_settings['background_color'] = 'red';

    return $default_settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Block title'),
      '#description' => $this->t('Leave empty to use the taxonomy vocabulary title'),
      '#default_value' => $this->getSetting('title'),
    ];

    $form['background_color'] = [
      '#type' => 'radios',
      '#title' => $this->t('Background color of the block'),
      '#options' => $this->getSetting('background_color_options'),
      '#default_value' => $this->getSetting('background_color'),
    ];

    $form['is_floating'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Is floating'),
      '#description' => $this->t('Indicates whether this block has to float and stay visible as the user scrolls the page'),
      '#default_value' => $this->getSetting('is_floating'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $title = $this->getSetting('title');
    $title = trim($title);
    $title_text = $title ? : 'The title of the Taxonomy Vocabulary will be used';
    $summary[] = 'Block title: ' . $title_text;

    $background_color_options = $this->getSetting('background_color_options');
    $summary[] = 'Background color of the block: ' . $background_color_options[$this->getSetting('background_color')];

    $is_floating_text = $this->getSetting('is_floating') ? t('Yes') : t('No');
    $summary[] = 'Is floating: ' . $is_floating_text;

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $language =  \Drupal::languageManager()->getCurrentLanguage()->getId();
    if (!empty($items) && method_exists($items, 'referencedEntities')) {
      if ($taxonomy_terms = $items->referencedEntities()) {
        $block_items = [];
        $block_title = $this->getSetting('title');
        $block_title = trim($block_title);

        if (empty($block_title)) {
          $field_definition = $items->getFieldDefinition();
          $field_entity_reference_settings = $field_definition->getSettings();
          if (!empty($field_entity_reference_settings['handler_settings']['target_bundles'])) {
            $target_bundles = $field_entity_reference_settings['handler_settings']['target_bundles'];
            $referenced_vid = reset($target_bundles);
            $vocabulary = Vocabulary::load($referenced_vid);
            $block_title = $vocabulary->label();
          }
        }
        foreach ($taxonomy_terms as $term) {
          $translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $language);
          $block_items[] = [
            '#type' => 'link',
            '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $translated_term->id()]),
            '#title' => $translated_term->label(),
          ];
        }
        return [
          '#theme' => 'bu_simple_items_block_with_background',
          '#title' => $block_title,
          '#items' => $block_items,
          '#is_floating' => $this->getSetting('is_floating'),
          '#background_color' => $this->getSetting('background_color'),
        ];
      }
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available for entity types that reference taxonomy
    // term entities.
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    return $target_type == 'taxonomy_term';
  }

}

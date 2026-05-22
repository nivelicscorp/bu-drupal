<?php

namespace Drupal\field_group_background_image\Plugin\field_group\FieldGroupFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Template\Attribute;
use Drupal\field_group\FieldGroupFormatterBase;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\media\Entity\Media;
use Drupal\media\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'background image' formatter.
 *
 * @FieldGroupFormatter(
 *   id = "background_image",
 *   label = @Translation("Background Image"),
 *   description = @Translation("Field group as a background image."),
 *   supported_contexts = {
 *     "view",
 *   }
 * )
 */
class BackgroundImage extends FieldGroupFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Returns the file_url_generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Returns the entity_field.manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Returns the module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, FileUrlGeneratorInterface $file_url_generator, EntityFieldManagerInterface $entity_field_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct($plugin_id, $plugin_definition, $configuration['group'], $configuration['settings'], $configuration['label']);

    $this->fileUrlGenerator = $file_url_generator;
    $this->entityFieldManager = $entity_field_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('file_url_generator'),
      $container->get('entity_field.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    $attributes = new Attribute();

    // Add the HTML ID.
    if ($id = $this->getSetting('id')) {
      $attributes['id'] = Html::getId($id);
    }

    // Add the HTML classes.
    $attributes['class'] = $this->getClasses();

    // Add the image as a background.
    $image = $this->getSetting('image');
    $image_style = $this->getSetting('image_style');
    $color_field = $this->getSetting('color_field');

    if ($style = $this->generateStyleAttribute($rendering_object, $image, $image_style)) {
      // Add inline styles to the output.
      if ($inline_styles = $this->getSetting('inline_styles')) {
        $style .= " {$inline_styles}";
      }

      // Add background color to the output.
      if ($background_color = $this->getBackgroundColor($rendering_object, $color_field)) {
        $style .= " background-color: {$background_color};";
      }

      $attributes['style'] = $style;
    }
    elseif ($this->getSetting('hide_if_missing')) {
      hide($element);
    }

    // Render the element as a HTML div and add the attributes.
    $element['#type'] = 'container';
    $element['#attributes'] = $attributes;
  }

  /**
   * Generates the background image style attribute.
   *
   * @param object $rendering_object
   *   Rendering Object.
   * @param string $image
   *   Image.
   * @param string $image_style
   *   Image Style.
   *
   * @return string
   *   Background Image style inline with absolute url.
   */
  protected function generateStyleAttribute($rendering_object, $image, $image_style) {
    $style = '';

    $valid_image = array_key_exists($image, $this->imageFields());
    $valid_image_style = ($image_style === '') || array_key_exists($image_style, image_style_options(FALSE));

    if ($valid_image && $valid_image_style && $url = $this->imageUrl($rendering_object, $image, $image_style)) {
      $style = strtr('background-image: url(\'@url\');', ['@url' => $url]);
    }

    return $style;
  }

  /**
   * Generates the background color.
   *
   * @param object $rendering_object
   *   Rendering Object.
   * @param string $color_field
   *   Color field.
   *
   * @return string
   *   Background color.
   */
  protected function getBackgroundColor($rendering_object, $color_field) {
    $background = '';

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    if (!($entity = $rendering_object['#' . $this->group->entity_type])) {
      return $background;
    }

    if ($entity->hasField($color_field) && !$entity->get($color_field)->isEmpty()) {
      $value = $entity->get($color_field)->getValue();

      $color = $value[0]['color'];
      if (str_starts_with($color, '#')) {
        $color = substr($color, 1);
      }

      $hexdec = hexdec($color);

      $red = (($hexdec & 0xFF0000) >> 16);
      $green = (($hexdec & 0x00FF00) >> 8);
      $blue = (($hexdec & 0x0000FF));

      $red = max(0, min(255, $red));
      $green = max(0, min(255, $green));
      $blue = max(0, min(255, $blue));

      $background = isset($value[0]['opacity'])
        ? 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $value[0]['opacity'] . ')'
        : 'rgb(' . $red . ',' . $green . ',' . $blue . ')';
    }

    return $background;
  }

  /**
   * {@inheritdoc}
   */
  protected function getClasses() {
    $classes = parent::getClasses();
    $classes[] = 'field-group-background-image';
    $classes = array_map(['\Drupal\Component\Utility\Html', 'getClass'], $classes);

    return $classes;
  }

  /**
   * Returns an image URL to be used in the Field Group.
   *
   * @param object $rendering_object
   *   The object being rendered.
   * @param string $field
   *   Image field name.
   * @param string $image_style
   *   Image style name.
   *
   * @return string
   *   Image URL.
   */
  protected function imageUrl($rendering_object, $field, $image_style) {
    $image_url = '';

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    if (!($entity = $rendering_object['#' . $this->group->entity_type])) {
      return $image_url;
    }

    if ($image_field_value = $rendering_object['#' . $this->group->entity_type]->get($field)->getValue()) {

      // Fid for image or entity_id.
      if (!empty($image_field_value[0]['target_id'])) {
        $entity_id = $image_field_value[0]['target_id'];

        $field_definition = $entity->getFieldDefinition($field);
        // Get the media or file URI.
        if (
          $field_definition->getType() == 'entity_reference' &&
          $field_definition->getSetting('target_type') == 'media'
        ) {

          // Load media.
          $entity_media = Media::load($entity_id);

          // Loop over entity fields.
          $file_uri = '';
          $media_fields = $entity_media instanceof MediaInterface ? $entity_media->getFields() : [];
          foreach ($media_fields as $field_name => $media_field) {
            if (
              $media_field->getFieldDefinition()->getType() === 'image' &&
              $media_field->getFieldDefinition()->getName() !== 'thumbnail'
            ) {
              $file_uri = method_exists($entity_media->{$field_name}->entity, 'getFileUri') ? $entity_media->{$field_name}->entity->getFileUri() : '';
            }
          }
        }
        else {
          $file_uri = File::load($entity_id)->getFileUri();
        }

        if (!$file_uri) {
          return $image_url;
        }

        // When no image style is selected, use the original image.
        if ($image_style === '') {
          $image_url = $this->fileUrlGenerator->generateAbsoluteString($file_uri);
        }
        else {
          $image_url = ImageStyle::load($image_style)->buildUrl($file_uri);
        }
      }
    }

    return $this->fileUrlGenerator->transformRelative($image_url);
  }

  /**
   * Get all image fields for the current entity and bundle.
   *
   * @return array
   *   Image field key value pair.
   */
  protected function imageFields() {
    $fields = $this->entityFieldManager->getFieldDefinitions($this->group->entity_type, $this->group->bundle);

    $image_fields = [];
    foreach ($fields as $field) {
      if ($field->getType() === 'image' || ($field->getType() === 'entity_reference' && $field->getSetting('target_type') == 'media')) {
        $image_fields[$field->get('field_name')] = $field->label();
      }
    }

    return $image_fields;
  }

  /**
   * Get all color fields for the current entity and bundle.
   *
   * @return array
   *   Color field key value pair.
   */
  protected function colorFields() {
    $fields = $this->entityFieldManager->getFieldDefinitions($this->group->entity_type, $this->group->bundle);

    $image_fields = [];
    foreach ($fields as $field) {
      if ($field->getType() === 'color_field_type') {
        $image_fields[$field->get('field_name')] = $field->label();
      }
    }

    return $image_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();

    $form['label']['#access'] = FALSE;

    if ($image_fields = $this->imageFields()) {
      $form['image'] = [
        '#title' => $this->t('Image'),
        '#type' => 'select',
        '#options' => [
          '' => $this->t('- Select -'),
        ],
        '#default_value' => $this->getSetting('image'),
        '#weight' => 1,
      ];
      $form['image']['#options'] += $image_fields;

      $form['image_style'] = [
        '#title' => $this->t('Image style'),
        '#type' => 'select',
        '#options' => [
          '' => $this->t('- Select -'),
        ],
        '#default_value' => $this->getSetting('image_style'),
        '#weight' => 2,
      ];
      $form['image_style']['#options'] += image_style_options(FALSE);

      $color_module = $this->moduleHandler->moduleExists('color_field');
      $form['color_field'] = [
        '#title' => $this->t('Background color field'),
        '#type' => 'select',
        '#access' => $color_module,
        '#options' => [
          '' => $this->t('- Select -'),
        ],
        '#default_value' => $color_module && $this->getSetting('color_field') ? $this->getSetting('color_field') : '',
        '#weight' => 3,
      ];
      $form['color_field']['#options'] += $this->colorFields();

      $form['inline_styles'] = [
        '#title' => $this->t('Inline styles'),
        '#type' => 'textfield',
        '#maxlength' => 255,
        '#default_value' => $this->getSetting('inline_styles'),
        '#weight' => 4,
      ];

      $form['hide_if_missing'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Hide if missing image'),
        '#description' => $this->t('Do not render the field group if the image is missing from the selected field.'),
        '#default_value' => $this->getSetting('hide_if_missing'),
        '#weight' => 5,
      ];
    }
    else {
      $form['error'] = [
        '#markup' => $this->t('Please add an image field to continue.'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($image = $this->getSetting('image')) {
      $image_fields = $this->imageFields();
      $summary[] = $this->t('Image field: @image', ['@image' => $image_fields[$image]]);
    }

    if ($image_style = $this->getSetting('image_style')) {
      $summary[] = $this->t('Image style: @style', ['@style' => $image_style]);
    }

    if ($color_field = $this->getSetting('color_field')) {
      $summary[] = $this->t('Background color field: @color', ['@color' => $color_field]);
    }

    return $summary;
  }

}

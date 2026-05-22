<?php

namespace Drupal\modal_page\Entity\Controller;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for Modal entity.
 */
class ModalListBuilder extends EntityListBuilder {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
        $container->get('language_manager'), $entity_type, $container->get('entity.manager')->getStorage($entity_type->id()), $container->get('url_generator')
    );
  }

  /**
   * Constructs a new ModalListBuilder object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(LanguageManagerInterface $language_manager, EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator) {
    $this->languageManager = $language_manager;
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['langcode'] = $this->t('Language');
    $header['pages'] = $this->t('Pages');
    $header['parameters'] = $this->t('Parameters');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id->value;

    $row['title'] = $entity->title->value;

    $language_label = '- Any -';

    if (!empty($entity->langcode->value)) {

      $language_label = $entity->langcode->value;

      $languages = $this->languageManager->getLanguages();

      if (!empty($languages[$language_label]->getName())) {
        $language_label = $languages[$language_label]->getName();
      }
    }

    $row['langcode'] = $language_label;

    $type = $entity->type->value;

    $pages = $entity->pages->value;

    $pages_value = 'N/A';

    if (!empty($pages) && $type == 'page') {

      $pages_value = '';

      $pages = explode(PHP_EOL, $pages);

      foreach ($pages as $key => $page) {

        if ($key != 0) {
          $pages_value .= ', ';
        }

        $pages_value .= trim($page);
      }
    }

    if (strlen($pages_value) > 44) {
      $pages_value = substr($pages_value, 0, 44) . ' ...';
    }

    $row['pages'] = $pages_value;

    $parameters = $entity->parameters->value;

    $parameters_value = 'N/A';

    if (!empty($parameters) && $type == 'parameter') {

      $parameters_value = '';

      $parameters = explode(PHP_EOL, $parameters);

      foreach ($parameters as $key => $parameter) {

        if ($key != 0) {
          $parameters_value .= ', ';
        }

        $parameters_value .= 'modal=' . trim($parameter);
      }
    }

    if (strlen($parameters_value) > 44) {
      $parameters_value = substr($parameters_value, 0, 44) . ' ...';
    }

    $row['parameters'] = $parameters_value;

    return $row + parent::buildRow($entity);
  }

}

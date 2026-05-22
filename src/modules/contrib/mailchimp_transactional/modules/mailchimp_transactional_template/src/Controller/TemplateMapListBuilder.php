<?php

declare(strict_types=1);
/**
 * @file
 * Contains \Drupal\mailchimp_transactional_template\Controller\TemplateMapListBuilder.
 */

namespace Drupal\mailchimp_transactional_template\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of TemplateMap entities.
 *
 * @ingroup mailchimp_transactional_template
 */
class TemplateMapListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['label'] = $this->t('Label');
    $header['template_name'] = $this->t('Template Name');
    $header['content_area'] = $this->t('Content Area');
    $header['mailsystem_key'] = $this->t('In Use By');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\mailchimp_transactional_template\Entity\TemplateMap $entity */
    $row['label'] = $entity->label() . ' (Machine name: ' . $entity->id() . ')';
    $row['template_name'] = $entity->template_name;
    $row['content_area'] = $entity->content_area;
    $row['mailsystem_key'] = $entity->mailsystem_key;

    return $row + parent::buildRow($entity);
  }

}

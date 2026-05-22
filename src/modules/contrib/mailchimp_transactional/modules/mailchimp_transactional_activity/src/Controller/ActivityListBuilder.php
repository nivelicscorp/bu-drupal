<?php

declare(strict_types=1);
/**
 * @file
 * Contains \Drupal\mailchimp_transactional_activity\Controller\ActivityListBuilder.
 */

namespace Drupal\mailchimp_transactional_activity\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Activity entities.
 *
 * @ingroup mailchimp_transactional_activity
 */
class ActivityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label() . ' (Machine name: ' . $entity->id() . ')';

    return $row + parent::buildRow($entity);
  }

}

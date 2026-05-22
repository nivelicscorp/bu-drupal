<?php

namespace Drupal\bu_sections;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class BuSectionsManager {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entity_manager;

  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entity_manager = $entity_manager;
  }

  // Put your methods here...
  
}

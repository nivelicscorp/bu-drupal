<?php

namespace Drupal\modal_page;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Modal entity.
 */
interface ModalPageInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}

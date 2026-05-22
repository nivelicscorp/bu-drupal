<?php

namespace Drupal\social_media\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * An event type that is dispatched to alter social media information.
 */
class SocialMediaEvent extends Event {

  /**
   * The information that is being altered.
   *
   * This may be one of the following:
   *   social_media.add_more_social_media: An array of social media types.
   *   social_media.pre_execute: A build array.
   *   social_media.pre_render: A build array.
   *
   * @var array
   */
  protected $element;

  /**
   * Creates a new social media event.
   *
   * @param array $element
   *   The element that is being altered.
   */
  public function __construct(array $element) {
    $this->element = $element;
  }

  /**
   * Return the element.
   *
   * @return array
   *   The element that is being altered.
   */
  public function getElement() {
    return $this->element;
  }

  /**
   * Sets the element to alter.
   *
   * @param array $element
   *   The element that is being altered.
   */
  public function setElement(array $element) {
    $this->element = $element;
  }

}

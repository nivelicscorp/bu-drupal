<?php

namespace Drupal\bu_sections\Controller;

/**
 * Defines a controller to manage the emBlue features
 */
interface EmBlueControllerInterface {
  /**
   * Send the contact form data to emBlue
   *
   * @param array $attributes
   *  Form attributes to send
   * @return array
   *  Action result
   */
  public function sendNewsletterFormData($attributes);
}

<?php

namespace Drupal\modal_page\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * The modal field validator service.
 */
class ModalFieldValidationService {

  use StringTranslationTrait;

  /**
   * Verify if "Video Link" is a valid YouTube URL.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function validateVideoLink(FormStateInterface &$form_state) {

    $values = $form_state->getValues();

    if (empty($values['modal_video_link'])) {
      return;
    }

    // Define images allowed.
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff'];

    // Get the extension.
    $extension = strtolower(pathinfo($values['modal_video_link'], PATHINFO_EXTENSION));

    // Verify if the extension is in the list of image extensions.
    if (in_array($extension, $imageExtensions)) {
      return;
    }

    if (!$this->isValidModalVideoLink($values['modal_video_link'])) {
      $form_state->setErrorByName(
        'modal_video_link',
        $this->t('Invalid video YouTube URL')
      );
    }
  }

  /**
   * Verify if "Video Link" is a valid YouTube URL.
   *
   * @param string $modal_video_link
   *   Modal video link.
   *
   * @return bool
   *   Return TRUE if is a valid YouTube url, or FALSE otherwise.
   */
  private function isValidModalVideoLink(string $modal_video_link) : bool {

    return preg_match(
        "
        #(?<=v=)[a-zA-Z0-9-]+(?=&)|
        (?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#
        ",
        $modal_video_link
      );

  }

}

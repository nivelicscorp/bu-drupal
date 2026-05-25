/**
 * @file
 * Utility functions for Modal Page.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.modalPage = Drupal.modalPage || {};

  /**
   * Stops all YouTube videos within a given container.
   *
   * @param {jQuery} container
   *   The jQuery object containing iframes to check and stop.
   */
  Drupal.modalPage.stopYouTubeVideos = function (container) {
    container.find('iframe').each(function () {
      var src = $(this).attr('src');
      if (src && (src.indexOf('youtube.com') !== -1 || src.indexOf('youtu.be') !== -1)) {
        // Reset the iframe src to stop the video.
        $(this).attr('src', src);
      }
    });
  };

})(jQuery, Drupal);

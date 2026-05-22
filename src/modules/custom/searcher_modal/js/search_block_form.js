(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.buSearchBlockForm = {
    attach: function (context, settings) {
      var $blockSearchContainer = $('.block-search', context);
      var $blockSearchButton = $blockSearchContainer.find('form .input-group .input-group-btn button');
      var $keysInput = $blockSearchContainer.find('form .input-group input[name="keys"]');
      var $fullSearchInput = $blockSearchContainer.find('form input[name="fulltext_input"]');
      var $blockSearchFormSubmitButton = $blockSearchContainer.find('form .form-actions button[data-drupal-selector="edit-submit"]');
      $blockSearchButton.on('click', function (event) {
        event.preventDefault();
        $fullSearchInput.val($keysInput.val());
        $blockSearchFormSubmitButton.click();
      })
    }
  }
})(jQuery, Drupal);
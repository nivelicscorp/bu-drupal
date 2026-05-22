/**
 * @file
 * Admin JavaScript file for Modal Page.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.modalPage = {
    attach: function (context, settings) {

      var modal_type = $(context).find('#edit-type');
      var field_pages = $(context).find('.field--name-pages');
      var field_parameters = $(context).find('.field--name-parameters');

      function check_modal_by_page_parameter() {

        var modal_type_value = modal_type.val();

        if (modal_type_value && modal_type_value === 'page') {
          field_pages.show();
          field_parameters.hide();
        }

        if (modal_type_value && modal_type_value === 'parameter') {
          field_pages.hide();
          field_parameters.show();
        }
      }

      check_modal_by_page_parameter();

      $(modal_type).on('change', function () {
        check_modal_by_page_parameter();
      });
    }
  };
})(jQuery, Drupal);

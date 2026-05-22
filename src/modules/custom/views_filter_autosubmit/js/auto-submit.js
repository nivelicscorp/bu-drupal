(function ($, Drupal) {

  Drupal.behaviors.ViewsAutoSubmit = {
    attach: function (context) {
      function triggerSubmit(e) {
        var $this = $(this);
        if (!$this.hasClass('views-ajaxing')) {
          $this.find('.views-auto-submit-click').click();
        }
      }

      once('views-auto-submit', $('form.views-auto-submit-full-form', context)).forEach(function (form) {
        $(form)
          .add('.views-auto-submit', context)
          .filter('form, select, input:not(:text, :submit)')
          .on('change', function (e) {
            if ($(e.target).is(':not(:text, :submit)')) {
              triggerSubmit.call(e.target.form);
            }
          });
      });

      var discardKeyCode = [16, 17, 18, 20, 33, 34, 35, 36, 37, 38, 39, 40, 9, 13, 27];

      once('views-auto-submit', $('.views-auto-submit-full-form input:text, input:text.views-auto-submit', context).get()).forEach(function (element) {
        var timeoutID = 0;
        $(element)
          .bind('keydown keyup', function (e) {
            if ($.inArray(e.keyCode, discardKeyCode) === -1) {
              timeoutID && clearTimeout(timeoutID);
            }
          })
          .keyup(function (e) {
            if ($.inArray(e.keyCode, discardKeyCode) === -1) {
              timeoutID = setTimeout($.proxy(triggerSubmit, this.form), 500);
            }
          });
      });
    }
  };

})(jQuery, Drupal);
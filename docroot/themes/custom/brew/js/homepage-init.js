(function ($, Drupal) {
  Drupal.behaviors.homepageInit = {
    attach: function (context, settings) {
      $('.homepage-feature', context).once('homepageInit').each(function () {

        // WOW animates elements with custom js and css.
        let wow = new WOW({
          boxClass:     'wow',
          animateClass: 'animated',
          offset:       0,
          mobile:       true,
          live:         true
        });
        wow.init();

      });
    }
  };
})(jQuery, Drupal);

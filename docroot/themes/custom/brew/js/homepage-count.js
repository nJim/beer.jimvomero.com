(function ($, Drupal) {
  Drupal.behaviors.homepageStats = {
    attach: function (context, settings) {
      $('.homepage-stats', context).once('homepageStats').each(function () {

        // Map source data comes from PHP
        let count = drupalSettings.breweryTypeCount;
        let $statsBand = $(this);

        // Easing function provided by the project page.
        let easingFn = function (t, b, c, d) {
          let ts = (t /= d) * t;
          let tc = ts * t;
          return b + c * (tc * ts + -5 * ts * ts + 10 * tc + -10 * ts + 5 * t);
        };

        let options = {
          useEasing : true,
          easingFn: easingFn,
          useGrouping : true,
        };

        // Creating three separate bubbles as I may animate them differently one day
        let bubble1 = new CountUp("brewery-count", 0, count.brewery, 0, 2.5, options),
            bubble2 = new CountUp("brewpub-count", 0, count.brewpub, 0, 2.5, options),
            bubble3 = new CountUp("taproom-count", 0, count.taproom, 0, 2.5, options);

        // Start the animation when the stats band appears on screen.
        // Set a time out to delay the start of the animation.
        $(window).on('resize scroll', function() {
          if ($($statsBand).isOnScreen()) {
            setTimeout(function () {
                bubble1.start();
                bubble2.start();
                bubble3.start();
              }, 600
            );
          }
        });

      });
    }
  };
})(jQuery, Drupal);

/**
 * jQuery isOnscreen
 * My custom library for checking if an element is fully onscreen.
 * Requires jQuery-Migrate 3.0 as offset() conflicts with jQuery UI version.
 */

;(function($) {

  $.fn.isOnScreen = function() {
    let elementTop = $(this).offset().top;
    let elementBottom = elementTop + $(this).outerHeight();
    let viewportTop = $(window).scrollTop();
    let viewportBottom = viewportTop + $(window).height();
    return elementBottom > viewportTop && elementTop < viewportBottom;
  };

})(jQuery);

(function ($, Drupal) {
  Drupal.behaviors.tileImgLazyLoader = {
    attach: function (context, settings) {
      $('.tile-list__item', context).once('tileImgLazyLoader').each(function () {

        // Attache lazy loader to each image.
        let $image = $('img', this).unveil(500);
        let $mapUrl = $('.tile-back', this).attr('data-bg-url');

        // When clicking a tile, remove flip class from any currently flipped
        // tiles and toggle the flip class for the clicked tile.
        $(this).click(function() {
          $(this).siblings().removeClass('flip');
          $(this).toggleClass('flip');
          $('.tile-back', this).css('background-image', 'url('+$mapUrl+')');
        });

        // Some of the effects are hover-based. Since this does not translate
        // to touch-screen interfaces, I'm adding a class of 'onscreen' to
        // some elements to create a manufactured state change.
        $(window).on('resize scroll', function() {
            if ($($image).isOnScreen()) {
              $image.addClass('onscreen');
            } else {
              $image.removeClass('onscreen');
            }
        });

      });
    }
  };
})(jQuery, Drupal);


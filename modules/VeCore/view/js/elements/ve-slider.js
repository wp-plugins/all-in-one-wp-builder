var ve_front=ve_front||{};
(function(ve_front) {
    var VeSlider = VeFront.extend({
        setup: function ($gallery) {

            if ($gallery.hasClass('ve_flexslider')) {
                var sliderSpeed = 800,
                    sliderTimeout = parseInt($gallery.attr('data-interval')) * 1000,
                    sliderFx = $gallery.attr('data-flex_fx'),
                    slideshow = true;
                if (sliderTimeout == 0) slideshow = false;

                    $gallery.flexslider({
                        animation: sliderFx,
                        slideshow: slideshow,
                        slideshowSpeed: sliderTimeout,
                        sliderSpeed: sliderSpeed,
                        smoothHeight: true
                    });

                $gallery.addClass('loaded');
            } else if ($gallery.hasClass('ve_slider_nivo')) {
                var sliderSpeed = 800,
                    sliderTimeout = $gallery.attr('data-interval') * 1000;

                if (sliderTimeout == 0) sliderTimeout = 9999999999;

                $gallery.find('.nivoSlider').nivoSlider({
                    effect: 'boxRainGrow,boxRain,boxRainReverse,boxRainGrowReverse', // Specify sets like: 'fold,fade,sliceDown'
                    slices: 15, // For slice animations
                    boxCols: 8, // For box animations
                    boxRows: 4, // For box animations
                    animSpeed: sliderSpeed, // Slide transition speed
                    pauseTime: sliderTimeout, // How long each slide will show
                    startSlide: 0, // Set starting Slide (0 index)
                    directionNav: true, // Next & Prev navigation
                    directionNavHide: true, // Only show on hover
                    controlNav: true, // 1,2,3... navigation
                    keyboardNav: false, // Use left & right arrows
                    pauseOnHover: true, // Stop animation while hovering
                    manualAdvance: false, // Force manual transitions
                    prevText: 'Prev', // Prev directionNav text
                    nextText: 'Next' // Next directionNav text
                });
            } else if ($gallery.hasClass('ve_image_grid')) {
                var isotope = $gallery.find('.ve_image_grid_ul');
                isotope.isotope({
                    // options
                    itemSelector: '.isotope-item',
                    layoutMode: 'fitRows'
                });
                isotope.isotope("layout");
            }
            this.prettyPhoto($gallery);
        },
        prettyPhoto: function (block) {
            try {
                jQuery('a.prettyphoto, .gallery-icon a[href*=".jpg"]', block).prettyPhoto({
                    animationSpeed: 'normal', /* fast/slow/normal */
                    padding: 15, /* padding for each side of the picture */
                    opacity: 0.7, /* Value betwee 0 and 1 */
                    showTitle: true, /* true/false */
                    allowresize: true, /* true/false */
                    counter_separator_label: '/', /* The separator for the gallery counter 1 "of" 2 */
                    //theme: 'light_square', /* light_rounded / dark_rounded / light_square / dark_square */
                    hideflash: false, /* Hides all the flash object on a page, set to TRUE if flash appears over prettyPhoto */
                    deeplinking: false, /* Allow prettyPhoto to update the url to enable deeplinking. */
                    modal: false, /* If set to true, only the close button will close the window */
                    callback: function () {
                        var url = location.href;
                        var hashtag = (url.indexOf('#!prettyPhoto')) ? true : false;
                        if (hashtag) location.hash = "!";
                    } /* Called when prettyPhoto is closed */,
                    social_tools: ''
                });
            }catch (e){}
        }

    });
    ve_front.slider = new VeSlider({el: '.ve_gallery_slides'});
})(ve_front);

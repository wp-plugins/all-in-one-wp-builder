var ve_front=ve_front||{};
(function(ve_front) {
    var VeImage = VeFront.extend({
        setup: function ($gallery) {
            this.prettyPhoto($gallery);
        },
        prettyPhoto: function (block) {
            try {
                jQuery('a.prettyphoto', block).prettyPhoto({
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
    ve_front.image = new VeImage({el: '.ve_image'});
})(ve_front);

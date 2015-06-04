/**
 * Created by Alt on 4/14/2015.
 */
var ve_front=ve_front||{};
(function(ve_front) {
    var VeRow = VeFront.extend({
        setup: function ($row) {
            this.rowFullWidth($row);
        },
        rowFullWidth: function (block) {

            var $ = window.jQuery;
            var is_iframe=(typeof ve_iframe!='undefined');
            var root=window;
            var local_function = function () {
                var $elements = $('[data-ve-full-width="true"]');
                $.each($elements, function (key, item) {

                    var $el = $(this);
                    var $root = $(root);
                    var $el_full = $el.next('.ve_row-full-width');
                    var full_offset=$el_full.offset();
                    if(is_iframe){
                        $root=$('body');
                        full_offset.left-=$root.offset().left;
                    }
                    var offset = 0 - full_offset.left - parseInt($el.css('margin-left'));
                    $el.css({
                        'position': 'relative',
                        'left': offset,
                        'box-sizing': 'border-box',
                        'width': $root.width()
                    });
                    if (!$el.data('veStretchContent')) {
                        var padding = (-1 * offset);
                        if(padding < 0) { padding = 0; }
                        $el.css({'padding-left': padding+'px', 'padding-right': padding+'px'});
                    }
                });
            };
            $(window).unbind('resize.veRow').bind('resize.veRow',local_function);
            local_function();

        }

    });
    ve_front.ve_row = new VeRow({el: '.ve_row'});
})(ve_front);

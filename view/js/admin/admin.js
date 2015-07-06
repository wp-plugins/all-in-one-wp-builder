var VE_Admin={};
(function($){
    VE_Admin= {
        initialize: function () {
            this.formInputCondition();
        },

        formInputCondition: function () {
            $('[data-show-if]', this.$el).each(function () {
                var $block = $(this),
                    handle_id = $block.data('show-if'),
                    handle_value = $block.data('show-value'),
                    comparator = $block.data('compare') || '==';

                if (!$block.data('bind-show-if')) {
                    var handle = $('#' + handle_id);
                    if (handle.is('select')) {
                        var select_handle_function = function () {
                            var result;
                            if ($.isArray(handle_value)) {
                                result = (-1 !== handle_value.indexOf(handle.val()));
                            } else {
                                result = handle_value == handle.val();
                            }
                            if (comparator != '==') {
                                result = !result;
                            }
                            if (result) {
                                $block.removeClass('ve_hide').show();
                            } else {
                                $block.addClass('ve_hide').hide();
                            }
                        };
                        handle.on('change', select_handle_function);
                        select_handle_function();
                    }
                    //console.log(handle);
                    if (handle.is('input[type=checkbox]')) {
                        var checkbox_handle_function = function () {
                            var result;
                            result = handle.is(handle_value);
                            if (comparator != '==') {
                                result = !result;
                            }
                            if (result) {
                                $block.removeClass('ve_hide').show();
                            } else {
                                $block.addClass('ve_hide').hide();
                            }
                        };
                        handle.on('click', checkbox_handle_function);
                        checkbox_handle_function();
                    }
                    $block.data('bind-show-if', true);
                }

            })
        }

    };
    $(document).ready(function(){
        VE_Admin.initialize();
    });
})(jQuery);
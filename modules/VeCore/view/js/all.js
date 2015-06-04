/**
 * this file load with all elements form
 */
(function($){
    $('.ve_color-control').wpColorPicker();
    $('.gallery_widget_attached_images_list').each(function (index) {
        var $img_ul = $(this);
        var $block=$img_ul.closest('.edit_form_line');
        var $hiddenInput=$('.gallery_widget_attached_images_ids',$block);
        var $addButton=$('.gallery_widget_add_images',$block);
        if($addButton.data('multiple')) {
            $img_ul.sortable({
                forcePlaceholderSize: true,
                placeholder: "widgets-placeholder-gallery",
                cursor: "move",
                items: "li",
                update: function () {
                    var img_ids = [];
                    $(this).find('.added img').each(function () {
                        img_ids.push($(this).attr("rel"));
                    });
                    $hiddenInput.val(img_ids.join(',')).trigger('change');
                }
            });
        }
    });
    $('[data-show-if]').each(function(){
        var $block=$(this),
            handle_id=$block.data('show-if'),
            handle_value=$block.data('show-value'),
            comparator=$block.data('compare')||'==';

        if(!$block.data('bind-show-if')){
            var handle=$('#'+handle_id);
            if(handle.is('select')) {
                var select_handle_function=function () {
                    var result;
                    if($.isArray(handle_value)){
                        result=(-1!==handle_value.indexOf(handle.val()));
                    }else {
                        result = handle_value == handle.val();
                    }
                    if(comparator!='=='){
                        result=!result;
                    }
                    if (result) {
                        $block.removeClass('ve_hide');
                    } else {
                        $block.addClass('ve_hide');
                    }
                };
                handle.on('change', select_handle_function);
                select_handle_function();
            }
            //console.log(handle);
            if(handle.is('input[type=checkbox]')){
                var checkbox_handle_function=function () {
                    var result;
                    result=handle.is(handle_value);
                    if(comparator!='=='){
                        result=!result;
                    }
                    if (result) {
                        $block.removeClass('ve_hide');
                    } else {
                        $block.addClass('ve_hide');
                    }
                };
                handle.on('click', checkbox_handle_function);
                checkbox_handle_function();
            }
            $block.data('bind-show-if',true);
        }

    })
})(jQuery);
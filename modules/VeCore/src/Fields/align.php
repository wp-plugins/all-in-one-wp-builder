<?php
class VE_Core_Field_Align{
    function __construct(){
        add_action('ve_element_form_before',array($this,'form'),10,3);
        add_action('ve_update_element',array($this,'update'),10,3);
    }
    function update(Ve_Element $element,$instance){
        $instance=wp_parse_args($instance,array('align'=>''));
        if($instance['align']){
            $element->addClass('ve-align'.$instance['align']);
        }
    }
    function form(Ve_Element $element,$instance){
        $instance=wp_parse_args($instance,array('align'=>''));
        $align=$instance['align'];

        ?>
        <div class="ve_input_block" id="ve-aligns">
            <label>Align:</label>
            <i class="align-button fa fa-align-left<?php echo $align=='left'?' align-current':'';?>" data-align="left"></i>
            <i class="align-button fa fa-align-center<?php echo $align=='center'?' align-current':'';?>" data-align="center"></i>
            <i class="align-button fa fa-align-right<?php echo $align=='right'?' align-current':'';?>" data-align="right"></i>
            <input type="hidden" name="<?php $element->field_name('align');?>" id="ve-align-input" value="<?php echo $align;?>"/>
        </div>
        <script type="text/javascript">
            (function($){
                var align=$('#ve-aligns');
                align.on('click','.align-button',function(){
                    if(!$(this).hasClass('align-current')){
                        $('.align-button',align).removeClass('align-current');
                    }
                    $(this).toggleClass('align-current');
                    if($(this).hasClass('align-current')){
                        $('#ve-align-input').val($(this).data('align'));
                    }else{
                        $('#ve-align-input').val('');
                    }
                })

            })(jQuery);
        </script>
        <?php
    }
}
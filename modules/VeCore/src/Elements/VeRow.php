<?php
class VeCore_VeRow extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_row';
        $name='Row';
        $options=array(
            'title'=>'VE Row',
            'description'=>'Row description',
            'icon_class'=>"fa fa-bars",
            'container'=>true,
            'lv'=>1,
            'classname'=>'ve-row',
        );
        parent::__construct($id_base,$name,$options);
    }
    function init(){
        $this->support('CssEditor');
        $this->enqueue_js('el-row',__DIR__.'/../../view/js/elements/ve-row.js');
        $this->ready('ve_front.ve_row.start();');
    }
    function element($instance,$content=''){
        $instance=shortcode_atts(array(
            'font_color'=>'',
            'class'=>'',
            'full_width'=>''
        ),$instance);
        $font_color=$instance['font_color'];
        $class=esc_attr($instance['class']);
        $full_width=$instance['full_width'];
        if($full_width){
            switch($full_width){
                case 'stretch_row_content_no_spaces':
                    $this->addClass('ve_row-no-padding');
                    $this->attr('data-ve-stretch-content','true');
                    $this->attr('data-ve-full-width','true');
                    $this->after('<div class="ve_row-full-width"></div>');
                    break;
                case 'stretch_row_content':
                    $this->attr('data-ve-stretch-content','true');
                    $this->attr('data-ve-full-width','true');
                    $this->after('<div class="ve_row-full-width"></div>');
                    break;
                case 'stretch_row':
                    $this->attr('data-ve-full-width','true');
                    $this->after('<div class="ve_row-full-width"></div>');
                    break;
            }
        }
        $this->css('color',$font_color);
        $this->addClass($class);
        echo do_shortcode($content);
    }
    function form($instance,$content=''){
        $instance=shortcode_atts(array(
            'font_color'=>'',
            'class'=>'',
            'full_width'=>''
        ),$instance);
        $font_color=$instance['font_color'];
        $class=esc_attr($instance['class']);
        $full_width_options=array(
            ''=>'Default',
            'stretch_row'=>'Stretch row',
            'stretch_row_content'=>'Stretch row and content',
            'stretch_row_content_no_spaces'=>'Stretch row and content without spaces'
        );
        $full_width=$instance['full_width'];
        ?>
        <p>
            <label for="<?php $this->field_id('full_width');?>">Row stretch</label><br/>
            <select name="<?php $this->field_name('full_width');?>" id="<?php $this->field_id('full_width');?>">
                <?php foreach($full_width_options as $o_value=>$o_title){
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$full_width,false),$o_title);
                }?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('font_color');?>">Font Color</label><br/>
            <input class="ve_color-control" name="<?php echo $this->get_field_name('font_color');?>" id="<?php echo $this->get_field_id('font_color');?>" value="<?php echo $font_color;?>"/>
        </p>
        <p><label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('Extra class:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo $class; ?>" /></p>

    <?php
    }
}
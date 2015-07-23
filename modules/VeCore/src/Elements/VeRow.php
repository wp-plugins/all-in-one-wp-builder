<?php
class VeCore_VeRow extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_row';
        $name='Row';
        $options=array(
            'title'=>'VE Row',
            'description'=>'Row description',
            'icon_class'=>"fa fa-align-justify",
            'container'=>true,
            'lv'=>1,
            'classname'=>'ve-row',
        );
        parent::__construct($id_base,$name,$options);
    }
    function init(){
        $this->support('CssEditor');
        $this->enqueue_js('el-row',dirname(__FILE__).'/../../view/js/elements/ve-row.js');
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
    function getLayouts(){
        $row_layouts = array(
            /*
         * How to count mask?
         * mask = column_count . sum of all numbers. Example layout 12_12 mask = (column count=2)(1+2+1+2=6)= 26
        */
            array( 'cells' => '11', 'mask' => '12', 'title' => '1/1', 'icon_class' => 'l_11' ),
            array( 'cells' => '12_12', 'mask' => '26', 'title' => '1/2 + 1/2', 'icon_class' => 'l_12_12' ),
            array( 'cells' => '23_13', 'mask' => '29', 'title' => '2/3 + 1/3', 'icon_class' => 'l_23_13' ),
            array( 'cells' => '13_13_13', 'mask' => '312', 'title' => '1/3 + 1/3 + 1/3', 'icon_class' => 'l_13_13_13' ),
            array( 'cells' => '14_14_14_14', 'mask' => '420', 'title' => '1/4 + 1/4 + 1/4 + 1/4', 'icon_class' => 'l_14_14_14_14' ),
            array( 'cells' => '14_34', 'mask' => '212', 'title' => '1/4 + 3/4', 'icon_class' => 'l_14_34' ),
            array( 'cells' => '14_12_14', 'mask' => '313', 'title' => '1/4 + 1/2 + 1/4', 'icon_class' => 'l_14_12_14' ),
            array( 'cells' => '56_16', 'mask' => '218', 'title' => '5/6 + 1/6', 'icon_class' => 'l_56_16' ),
            array( 'cells' => '16_16_16_16_16_16', 'mask' => '642', 'title' => '1/6 + 1/6 + 1/6 + 1/6 + 1/6 + 1/6', 'icon_class' => 'l_16_16_16_16_16_16' ),
            array( 'cells' => '16_23_16', 'mask' => '319', 'title' => '1/6 + 4/6 + 1/6', 'icon_class' => 'l_16_46_16' ),
            array( 'cells' => '16_16_16_12', 'mask' => '424', 'title' => '1/6 + 1/6 + 1/6 + 1/2', 'icon_class' => 'l_16_16_16_12' )
            //Momizat
        ,
            array('cells' => '68_39', 'mask' => '226', 'title' => 'sidebar right', 'icon_class' => 'l_23_13'),
            array('cells' => '88_77', 'mask' => '230', 'title' => 'sidebar left', 'icon_class' => 'l_14_34'),
            array('cells' => '48_28_39', 'mask' => '334', 'title' => 'Both sidebars right', 'icon_class' => 'bsr'),
            array('cells' => '88_99_77', 'mask' => '348', 'title' => 'Both sidebars left', 'icon_class' => 'bsl'),
            array('cells' => '99_48_39', 'mask' => '342', 'title' => 'Both sidebars', 'icon_class' => 'l_14_12_14'),

        );
        return $row_layouts;
    }
}
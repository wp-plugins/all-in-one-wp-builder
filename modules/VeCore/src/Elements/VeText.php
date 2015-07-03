<?php
class VeCore_VeText extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_text';
        $name='Text';
        $options=array(
            'title'=>'Text Block',
            'description'=>'Row description',
            'icon_class'=>'fa fa-text-width',
            'container'=>false,
            'has_content'=>true,
            'defaults'=>array('content'=>'this is a text block'),

        );
        parent::__construct($id_base,$name,$options);
    }
    function init(){
        $this->enqueue_js('el-text',__DIR__.'/../../view/js/elements/ve-text.js');
        $this->ready('ve_front.text.start();');
        $this->support('CssEditor');
    }
    function element($instance,$content=''){
        $instance=shortcode_atts(array('class'=>''),$instance);
        $this->addClass($instance['class']);
        echo do_shortcode($content);
        //$this->ready('alert("text block ready")');
    }
    function form($instance,$content=''){
        $instance=shortcode_atts(array('class'=>''),$instance);
        wp_editor($content,$this->get_field_id('content'),array(
            'textarea_name'=>$this->get_field_name('content'),
            'editor_class'=>'ve-html-editor',
            'textarea_rows'=>5,
        ));
        ?>
        <p><label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('Extra class:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo esc_attr($instance['class']); ?>" /></p>

    <?php
    }
}
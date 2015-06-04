<?php
class VeCore_VeQuote extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_quote';
        $name='Quote Text';
        $options=array(
            'title'=>'Quote Text Block',
            'description'=>'Row description',
            'icon_class'=>'fa fa-quote-left',
            'container'=>false,
            'has_content'=>true,
            'defaults'=>array('content'=>'this is a quote text block'),

        );
        parent::__construct($id_base,$name,$options);
    }
    function element($instance,$content=''){
        $instance=wp_parse_args($instance,array('class'=>''));

        printf('<blockquote class="%s">',$instance['class']);
        echo do_shortcode($content);
        echo '</blockquote>';
    }
    function form($instance,$content=''){
        ?>
        <textarea name="<?php echo $this->get_field_name('content');?>" id="<?php echo $this->get_field_id('content');?>"><?php echo $content;?></textarea>
        <p><label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('Extra class:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo esc_attr($instance['class']); ?>" /></p>

    <?php
    }
}
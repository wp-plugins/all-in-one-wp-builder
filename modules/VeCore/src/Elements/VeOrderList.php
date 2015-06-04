<?php
class VeCore_VeOrderList extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_ol';
        $name='Ordered list';
        $options=array(
            'title'=>'Order list Block',
            'description'=>'Row description',
            'icon_class'=>'fa fa-list-ol',
            'container'=>false,
            'has_content'=>true,
            'defaults'=>array('content'=>"item 1\nitem 2"),

        );
        parent::__construct($id_base,$name,$options);
        $this->enablePreview();
    }
    function element($instance,$content=''){
        $instance=wp_parse_args($instance,array('class'=>''));
        printf('<ol class="%s">',$instance['class']);
        $items = explode("\n", $content);
        $items = array_filter(array_map('trim',$items));

        foreach($items as $item){
            printf('<li>%s</li>',$item);
        }
        echo '</ol>';
    }
    function form($instance,$content=''){
        $instance=wp_parse_args($instance,array('class'=>''));
        wp_editor($content,$this->get_field_id('content'),array(
            'textarea_name'=>$this->get_field_name('content'),
            'editor_class'=>'ve-html-editor',
            'textarea_rows'=>5,
            'editor_height'=>300,
        ));
        ?>

        <p><label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('Extra class:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo esc_attr($instance['class']); ?>" /></p>

    <?php
    }
}
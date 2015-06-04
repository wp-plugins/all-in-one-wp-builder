<?php
class Wp_Widget_Additional_Fields{
    function __construct(){
        add_action('ve_element_form',array($this,'form'),10,3);
        add_action('ve_update_element',array($this,'update'),10,3);
    }
    function update(Ve_Element $element,$instance){
        $instance=wp_parse_args($instance,array('class'=>''));
        if($element->getWpWidget()){
            $element->addClass($instance['class']);
        }
    }
    function form(Ve_Element $element,$instance){
        $instance=wp_parse_args($instance,array('class'=>''));
        if(!$element->getWpWidget()){
            return ;
        }
        ?>
        <p><label for="<?php echo $element->get_field_id('class'); ?>"><?php _e('Extra class:'); ?></label>
            <input class="widefat" id="<?php echo $element->get_field_id('class'); ?>" name="<?php echo $element->get_field_name('class'); ?>" type="text" value="<?php echo esc_attr($instance['class']); ?>" /></p>

    <?php
    }
}
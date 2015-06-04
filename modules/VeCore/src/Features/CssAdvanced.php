<?php
class VeCore_CssAdvanced extends Ve_Feature_Abstract
{

    function _construct()
    {
        $this->setTitle('Advanced');
        //die('hehe');
    }
    function init_once(){
        add_action('wp_print_styles',array($this,'print_css'));
    }
    function print_css(){
        static $printed = false;

        if ( $printed ) {
            return;
        }

        $printed = true;
        $post_id=get_the_ID();
        $custom_css=get_post_meta($post_id,'_ve_element_custom_css',true);

        if($custom_css){
            ?>
            <style type="text/css" id="ve_element_custom_css">
                <?php echo $custom_css;?>
            </style>
            <?php
        }
    }
    function update($instance){
        if(!empty($instance['custom_css'])&&!empty($instance['custom_css_class'])){
            $this->getElement()->addClass($instance['custom_css_class']);
        }
    }
    function form($instance){
        $instance=shortcode_atts(array('custom_css'=>'','custom_css_class'=>$this->generate_class_name()),$instance);
        ?>
        <p class="edit_form_line">
            <label for="custom_css">Custom Css</label>
            <textarea id="custom_css" name="custom_css" class="widefat" rows="10"><?php echo esc_textarea($instance['custom_css']);?></textarea>
            <input type="hidden" name="custom_css_class" value="<?php echo $instance['custom_css_class'];?>"/>
        </p>
        <?php
    }
    function generate_class_name(){
        return 've_custom_'.time();
    }
}
<?php
class VeCore_VeVideo extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_video';
        $name='Video';
        $options=array(
            'title'=>'Video',
            'description'=>'Button description',
            'icon_class'=>'fa fa-youtube-play',
            'container'=>false,
            'has_content'=>false,
            'defaults'=>array(),

        );
        parent::__construct($id_base,$name,$options);
    }
    function init(){
        $this->support('CssEditor');
    }
    function element($instance,$content=''){
        $title=$link=$class='';
        extract( shortcode_atts( array(
            'title' => '',
            'link' => 'http://vimeo.com/92033601',
            'size' => ( isset( $content_width ) ) ? $content_width : 500,
            'class' => '',
            'css' => ''

        ), $instance ) );

        $this->addClass($class);

        $video_w = ( isset( $content_width ) ) ? $content_width : 500;
        $video_h = $video_w / 1.61; //1.61 golden ratio
        global $wp_embed;
        /**
         * @var WP_Embed $wp_embed
         */

        $embed = $wp_embed->run_shortcode( '[embed width="' . $video_w . '"' . $video_h . ']' . $link . '[/embed]' );
        //echo $embed;die;
        $this->element_title($title);
        $embed=sprintf('<div class="video-container">%s</div>',$embed);
        $this->element_content($embed);


    }
    function form($instance,$content=''){
        $instance=wp_parse_args($instance,array('title'=>'','link'=>'','class'=>''));
        $title=esc_attr($instance['title']);
        $video_link=esc_attr($instance['link']);
        $class=esc_attr($instance['class']);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

        <p><label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('Video Link:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo $video_link; ?>" /></p>

        <p><label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('Extra class:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo $class; ?>" /></p>

    <?php
    }
}

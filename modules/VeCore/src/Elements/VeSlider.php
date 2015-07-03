<?php
class VeCore_VeSlider extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_slide';
        $name='Slide';
        $options=array(
            'title'=>'Slide',
            'description'=>'Slide description',
            'icon_class'=>'fa fa-image',
            'container'=>false,
            'has_content'=>false,
            'defaults'=>array(),

        );
        parent::__construct($id_base,$name,$options);
    }
    function init(){
        $this->support('CssEditor');

        $this->enqueue_js('el-slider',__DIR__.'/../../view/js/elements/ve-slider.js');
        $this->ready('ve_front.slider.start();');
    }
    function element($instance,$content=''){
        $instance=shortcode_atts( array(
            'title' => '',
            'type' => 'flexslider',
            'onclick' => 'link_image',
            'custom_links' => '',
            'custom_links_target' => '',
            'img_size' => 'thumbnail',
            'images' => '',
            'class' => '',
            'interval' => '5',
        ), $instance );
        extract($instance);
        $title=$instance['title'];
        $type=$instance['type'];
        $onclick=$instance['onclick'];
        $images=$instance['images'];
        $custom_links=$instance['custom_links'];
        $img_size=$instance['img_size'];
        $interval=$instance['interval'];
        $this->addClass($instance['class']);
        $gal_images = '';
        $el_start = '';
        $el_end = '';
        $slides_wrap_start = '';
        $slides_wrap_end = '';


        if ( $type == 'nivo' ) {
            $type = ' ve_slider_nivo theme-default';
            wp_enqueue_script( 'nivo-slider' );
            wp_enqueue_style( 'nivo-slider-css' );
            wp_enqueue_style( 'nivo-slider-theme' );

            $slides_wrap_start = '<div class="nivoSlider">';
            $slides_wrap_end = '</div>';
        } else if ( $type == 'flexslider' || $type == 'flexslider_fade' || $type == 'flexslider_slide' || $type == 'fading' ) {
            $el_start = '<li>';
            $el_end = '</li>';
            $slides_wrap_start = '<ul class="slides">';
            $slides_wrap_end = '</ul>';
            wp_enqueue_style( 'flexslider' );
            wp_enqueue_script( 'flexslider' );
        } else if ( $type == 'image_grid' ) {
            wp_enqueue_script( 'isotope' );

            $el_start = '<li class="isotope-item">';
            $el_end = '</li>';
            $slides_wrap_start = '<ul class="ve_image_grid_ul">';
            $slides_wrap_end = '</ul>';
        }

        if ( $onclick == 'link_image' ) {
            wp_enqueue_script( 'prettyphoto' );
            wp_enqueue_style( 'prettyphoto' );
        }

        $flex_fx = '';
        if ( $type == 'flexslider' || $type == 'flexslider_fade' || $type == 'fading' ) {
            $type = ' ve_flexslider flexslider_fade flexslider';
            $flex_fx = ' data-flex_fx="fade"';
        } else if ( $type == 'flexslider_slide' ) {
            $type = ' ve_flexslider flexslider_slide flexslider';
            $flex_fx = ' data-flex_fx="slide"';
        } else if ( $type == 'image_grid' ) {
            $type = ' ve_image_grid';
        }

        
        if ( $images == '' ) $images = '-1,-2,-3';

        $pretty_rel_random = ' rel="prettyPhoto[rel-' . rand() . ']"'; //rel-'.rand();

        if ( $onclick == 'custom_link' ) {
            $custom_links = explode( ',', $custom_links );
        }
        $images = explode( ',', $images );
        $i = - 1;

        foreach ( $images as $attach_id ) {
            $i ++;
            if ( $attach_id > 0 ) {
                $post_thumbnail = ve_get_attachment_image( array( 'attach_id' => $attach_id, 'thumb_size' => $img_size ) );
            } else {
                $post_thumbnail = array();
                $post_thumbnail['thumbnail'] = '<img src="' . ve_resource_url( __DIR__.'/../../view/images/no_image.png' ) . '" />';
                $post_thumbnail['p_img_large'][0] = ve_resource_url(  __DIR__.'/../../view/images/no_image.png' );
            }

            $thumbnail = $post_thumbnail['thumbnail'];
            $p_img_large = $post_thumbnail['p_img_large'];
            $link_start = $link_end = '';

            if ( $onclick == 'link_image' ) {
                $link_start = '<a class="prettyphoto" href="' . $p_img_large[0] . '"' . $pretty_rel_random . '>';
                $link_end = '</a>';
            } else if ( $onclick == 'custom_link' && isset( $custom_links[$i] ) && $custom_links[$i] != '' ) {
                $link_start = '<a href="' . $custom_links[$i] . '"' . ( ! empty( $custom_links_target ) ? ' target="' . $custom_links_target . '"' : '' ) . '>';
                $link_end = '</a>';
            }
            $gal_images .= $el_start . $link_start . $thumbnail . $link_end . $el_end;
        }


        $this->element_title($title);
        $output = '<div class="ve_gallery_slides' . $type . '" data-interval="' . $interval . '"' . $flex_fx . '>' . $slides_wrap_start . $gal_images . $slides_wrap_end . '</div>';
        $this->element_content($output);

    }
    function form($instance,$content=''){
        $instance=shortcode_atts( array(
            'title' => '',
            'type' => 'flexslider',
            'onclick' => 'link_image',
            'custom_links' => '',
            'custom_links_target' => '',
            'img_size' => 'thumbnail',
            'images' => '',
            'class' => '',
            'interval' => '5',
        ), $instance );
        $img_ids=$instance['images'];
        $title=esc_attr($instance['title']);
        $type=$instance['type'];
        $interval=$instance['interval'];
        $img_size=esc_attr($instance['img_size']);
        $class=$instance['class'];
        $onclick=$instance['onclick'];
        $gallery_types=array(
            'flexslider_fade'=>__( 'Flex slider fade', 'visual_editor' ),
             'flexslider_slide'=>__( 'Flex slider slide', 'visual_editor' ) ,
            'nivo'=> __( 'Nivo slider', 'visual_editor' ),
            'image_grid'=>__( 'Image grid', 'visual_editor' ) 

        );
        $interval_list=array(
            '3'=>'3',
            '5'=>'5',
            '10'=>'10',
            '15'=>'15',
            '0'=>'Disabled'
        );
        $link_list=array(
            'link_image'=>__( 'Open prettyPhoto', 'visual_editor' ),
            'link_no'=>__( 'Do nothing', 'visual_editor' ) ,
            'custom_link'=>__( 'Open custom link', 'visual_editor' ),
        );
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
        <p>
            <label for="<?php $this->field_id('type');?>"><?php _e('Gallery type');?></label>
            <select name="<?php echo $this->get_field_name('type');?>" id="<?php $this->field_id('type');?>">
                <?php foreach($gallery_types as $_type=>$option_title){?>
                    <option value="<?php echo $_type;?>"<?php selected($_type,$type);?>><?php echo $option_title?></option>
                <?php }?>
            </select>
        </p>
        <p>
            <label for="<?php $this->field_id('interval');?>">Auto rotate slides</label>
            <select name="<?php $this->field_name('interval');?>" id="<?php $this->field_id('interval');?>">
                <?php foreach($interval_list as $o_value=>$o_title){
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$interval,false),$o_title);
                }?>
            </select>
        </p>
        <div class="ve_input_block">
            <label>Images</label>
            <input type="hidden" class="ve-media-selected-images-ids" name="<?php echo $this->get_field_name('images');?>" value="<?php echo $img_ids;?>"/>
            <div class="ve-media-selected-images">
                <ul class="ve-media-selected-images-list">
                    <?php echo fieldAttachedImages(explode(',',$img_ids));?>
                </ul>
            </div>
            <a class="ve-media-add-images-btn" href="#" data-multiple="true" title="Add images">Add images</a>
            <div class="ve_clearfix"></div>
        </div>
        <p class="ve_input_block">
            <label for="<?php $this->field_id('img_size');?>">Image size:</label>
            <select name="<?php $this->field_name('img_size');?>" id="<?php $this->field_id('img_size');?>">
                <?php
                $allImgSizes=get_intermediate_image_sizes();
                $allImgSizes[]='full';
                foreach($allImgSizes as $o_size){
                    printf('<option value="%s"%s>%s</option>',$o_size,selected($o_size,$img_size,false),$o_size);
                }
                if(strpos($img_size,'x')!==false){
                    printf('<option value="%s"%s>%s</option>',$img_size,selected($img_size,$img_size,false),$img_size);
                }
                ?>
            </select>
            <script type="text/javascript">
                jQuery("#<?php $this->field_id('img_size');?>").select2({
                    width:"360",
                    tags: true
                });
            </script>
            <span class="ve_description ve_clearfix">Enter image size. Example: "thumbnail", "medium", "large", "full" or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.</span>

        </p>
        <p>
            <label for="<?php $this->field_id('onclick');?>">On click</label>
            <select id="<?php $this->field_id('onclick');?>" name="<?php $this->field_name('onclick');?>">
                <?php foreach($link_list as $o_value=>$o_title){
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$onclick,false),$o_title);
                }?>
            </select>
        </p>
        <div class="ve_input_block ve_hide" data-show-if="<?php $this->field_id('onclick');?>" data-show-value="custom_link">
            <textarea name="<?php $this->field_name('custom_links');?>"><?php echo esc_textarea($instance['custom_links']);?></textarea>
            <span class="ve_description">Enter links for each slide here. Divide links with line breaks (Enter) . </span>
        </div>
        <p><label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('Extra class:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo $class; ?>" /></p>
        <?php

    }
}

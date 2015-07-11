<?php
class VeCore_VeImage extends Ve_Element implements VE_Element_Interface
{
    function __construct()
    {
        $id_base = 've_image';
        $name = 'Image';
        $options = array(
            'title' => 'Image',
            'description' => 'Image description',
            'icon_class' => 'fa fa-image',
            'container' => false,
            'has_content' => false,
            'defaults' => array(),

        );
        parent::__construct($id_base, $name, $options);
    }
    function init(){
        $this->enqueue_js('el-image',dirname(__FILE__).'/../../view/js/elements/ve-image.js');
        $this->ready('ve_front.image.start();');
        $this->support('CssEditor');
    }
    function element($instance,$content=''){
        $instance=shortcode_atts( array(
            'title' => '',
            'css_animation' => '',
            'alignment'=>'',
            'style' => '',
            'img_link_large'=>'',
            'img_link_target'=>'_self',
            'link'=>'',
            'img_size' => 'thumbnail',
            'image' => '',
            'class' => '',
            'width' => '',
            'height' => '',
            'interval' => '5',
        ), $instance );
        $style = $instance['style'];
        $image=$instance['image'];
        $img_size=$instance['img_size'];

        if($img_size == "custom")
        {
            $img_size = $instance['width'] .'x' . $instance['height'];
        }
        $img_link_large=$instance['img_link_large'];
        $css_animation=$instance['css_animation'];
        $alignment=$instance['alignment'];
        $title=$instance['title'];
        $link=$instance['link'];
        $img_id = preg_replace( '/[^\d]/', '', $image );
        $img = ve_get_attachment_image( array( 'attach_id' => $img_id, 'thumb_size' => $img_size, 'class' => $style ) );
        if ( $img == NULL ) $img['thumbnail'] = '<img class="' . $style  . '" src="' . ve_resource_url( dirname(__FILE__).'/../../view/images/no_image.png' ) . '" />';

        $el_class = $instance['class'];

        $a_class = '';
        if ( $el_class != '' ) {
            $tmp_class = explode( " ", strtolower( $el_class ) );
            $tmp_class = str_replace( ".", "", $tmp_class );
            if ( in_array( "prettyphoto", $tmp_class ) ) {
                wp_enqueue_script( 'prettyphoto' );
                wp_enqueue_style( 'prettyphoto' );
                $a_class = ' class="prettyphoto"';
                $el_class = str_ireplace( "prettyphoto", "", $el_class );
            }
        }

        $link_to = '';
        if ( $img_link_large == true ) {
            $link_to = wp_get_attachment_image_src( $img_id, 'large' );
            $link_to = $link_to[0];
        } else if ( strlen($link) > 0 ) {
            $link_to = $link;
        } else if ( ! empty( $img_link ) ) {
            $link_to = $img_link;
            if ( ! preg_match( '/^(https?\:\/\/|\/\/)/', $link_to ) ) $link_to = 'http://' . $link_to;
        }
        $img_link_target=$instance['img_link_target'];

        $img_output = ( $style == 've_box_shadow_3d' ) ? '<span class="ve_box_shadow_3d_wrap">' . $img['thumbnail'] . '</span>' : $img['thumbnail'];
        $image_string = ! empty( $link_to ) ? '<a' . $a_class . ' href="' . $link_to . '"' . ' target="' . $img_link_target . '"'. '>' . $img_output . '</a>' : $img_output;
        $css_class = $el_class;
        $css_class .= $this->getCSSAnimation( $css_animation );
        if($alignment)
        $css_class .= ' ve_align_' . $alignment;
        $this->addClass($css_class);
        $this->element_title($title);
        $this->element_content($image_string);

    }
    public function getCSSAnimation( $css_animation ) {
        $output = '';
        if ( $css_animation != '' ) {
            wp_enqueue_script( 'waypoints' );
            $output = ' ve_animate_when_almost_visible ve_ani_' . $css_animation;
        }
        return $output;
    }
    function form($instance){
        $instance=shortcode_atts( array(
            'title' => '',
            'css_animation' => '',
            'alignment'=>'',
            'style' => '',
            'img_link_large'=>'',
            'link'=>'',
            'img_size' => 'thumbnail',
            'image' => '',
            'width' => '',
            'height' => '',
            'class' => '',
            'interval' => '5',
        ), $instance );
        $title=$instance['title'];
        $img_id=$instance['image'];
        $css_animation=$instance['css_animation'];
        $img_size=$instance['img_size'];
        $width=$instance['width'];
        $height=$instance['height'];

        $alignment=$instance['alignment'];
        $style=$instance['style'];
        $img_link_large=$instance['img_link_large'];
        $link=esc_attr($instance['link']);
        $class=$instance['class'];
        $css_animations=array(
            ''=>'No',
            'top-to-bottom'=>'Top to bottom',
            'bottom-to-top'=>'Bottom to top',
            'left-to-right'=>'Left to right',
            'right-to-left'=>'Right to left',
            'appear'=>'Appear from center',

        );
        $img_alignments=array(
            ''=>'Align left',
            'right'=>'Align right',
            'center'=>'Align center',
        );
        $img_styles=array(
            ''=>'Default',
            've_box_rounded'=>'Rounded',
            've_box_border'=>'Border',
            've_box_outline'=>'Outline',
            've_box_shadow'=>'Shadow',
            've_box_shadow_border'=>'Bordered shadow',
            've_box_shadow_3d'=>'3D Shadow',
            've_box_circle'=>'Circle',
            've_box_border_circle'=>'Circle Border',
            've_box_outline_circle'=>'Circle Outline',
            've_box_shadow_circle'=>'Circle Shadow',
            've_box_shadow_border_circle'=>'Circle Border Shadow',
        );
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
        <div class="ve_input_block">
            <label>Image</label>
            <input type="hidden" class="ve-media-selected-images-ids" name="<?php echo $this->get_field_name('image');?>" value="<?php echo $img_id;?>"/>
            <div class="ve-media-selected-images">
                <ul class="ve-media-selected-images-list">
                    <?php echo fieldAttachedImages($img_id);?>
                </ul>
            </div>
            <a class="ve-media-add-images-btn" href="#" title="Add image">Add image</a>
            <div class="ve_clearfix"></div>
        </div>
        <div class="ve_input_block">
            <label for="<?php $this->field_id('css_animation');?>">Css Animation</label>
            <br/>
            <select id="<?php $this->field_id('css_animation');?>" name="<?php $this->field_name('css_animation');?>">
                <?php foreach($css_animations as $o_value=>$o_title){
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$css_animation,false),$o_title);
                }?>
            </select>
        </div>

        <p class="ve_input_block">
            <label for="<?php $this->field_id('img_size');?>">Image size:</label>
            <select name="<?php $this->field_name('img_size');?>" id="<?php $this->field_id('img_size');?>">
                <?php
                $allImgSizes=get_intermediate_image_sizes();
                $allImgSizes[]='full';
                $allImgSizes[]='custom';
                foreach($allImgSizes as $o_size){
                    printf('<option value="%s"%s>%s</option>',$o_size,selected($o_size,$img_size,false),$o_size);
                }

                ?>
            </select>

            <span class="ve_description ve_clearfix">Enter image size. Example: "thumbnail", "medium", "large", "full" or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.</span>

        </p>
        <p class="small-input" id="custom_size_setting">
            <strong>Custom Size:</strong><br/>

            <label for="top">Width:</label>
            <input class="" type="text" name="<?php $this->field_name('width');?>" value="<?php echo esc_attr($width);?>">

            <label for="left">Height:</label>
            <input type="text" name="<?php $this->field_name('height');?>" value="<?php echo esc_attr($height);?>">
        </p>
        <script type="text/javascript">
            jQuery("#<?php $this->field_id('img_size');?>").select2({
                width:"360",
                tags: true
            }).on('select2:select',function(e){
                if(jQuery(e.currentTarget).val() == "custom")
                {
                    jQuery('#custom_size_setting').show();
                }
                else
                {
                    jQuery('#custom_size_setting').hide();
                }
            });
            <?php
            if($img_size != "custom")
            {
                echo "jQuery('#custom_size_setting').hide();";
            }
            ?>

        </script>

        <div class="ve_input_block">
            <label for="<?php $this->field_id('alignment');?>">Image alignment</label>
            <br/>
            <select id="<?php $this->field_id('alignment');?>" name="<?php $this->field_name('alignment');?>">
                <?php foreach($img_alignments as $o_value=>$o_title){
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$alignment,false),$o_title);
                }?>
            </select>
        </div>

        <div class="ve_input_block">
            <label for="<?php $this->field_id('style');?>">Image style</label>
            <br/>
            <select id="<?php $this->field_id('style');?>" name="<?php $this->field_name('style');?>">
                <?php foreach($img_styles as $o_value=>$o_title){
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$style,false),$o_title);
                }?>
            </select>
        </div>

        <p>
            <label for="<?php $this->field_id('img_link_large');?>">Link to large image?</label>
            <br/>
            <input type="checkbox"<?php checked($img_link_large,'yes');?> name="<?php $this->field_name('img_link_large');?>" id="<?php $this->field_id('img_link_large');?>" value="yes"/>
        </p>
        <p data-show-if="<?php $this->field_id('img_link_large');?>" data-show-value=":checked" data-compare="!=">
            <label for="<?php $this->field_id('link');?>">Image link</label>
            <input class="widefat" type="text" placeholder="http://" name="<?php $this->field_name('link');?>" id="<?php $this->field_id('link');?>" value="<?php echo $link;?>"/>
        </p>
        <p><label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('Extra class:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo $class; ?>" /></p>
        <?php
    }
}

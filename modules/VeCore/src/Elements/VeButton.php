<?php
class VeCore_VeButton extends Ve_Element implements VE_Element_Interface{
    /**
     * @var VE_Post_Manager
     */
    var $post_manager;

    /**
     * @var VE_Popup_Manager
     */
    var $popup_manager;
    function __construct(){
        $id_base='ve_button';
        $name='Button';
        $options=array(
            'title'=>'Button',
            'description'=>'Button description',
            'icon_class'=>"fa fa-square",
            'container'=>false,
            'has_content'=>false,
            'defaults'=>array('value'=>'a button'),

        );
        parent::__construct($id_base,$name,$options);
    }
    function init(){
        $this->support('CssEditor');
        $this->post_manager=$this->getVeManager()->getPostManager();
        $this->popup_manager=$this->getVeManager()->getPopupManager();
        $this->getVeManager()->getResourceManager()->addCss('el-button',dirname(__FILE__).'/../../view/css/elements/buttons.css');
        $this->enqueue_js('el-button',dirname(__FILE__).'/../../view/js/elements/ve-button.js');
        $this->ready('ve_front.button.start();');
    }
    function element($instance,$content=''){
        $instance=shortcode_atts(array(
            'class'=>'',
            'value'=>'',
            'style'=>'',
            'size'=>'',
            'color'=>'',
            'shape'=>'',
            'link'=>'',
            'link_post'=>'',
            'link_popup'=>'',
            'link_url'=>'',
            'link_target'=>'',
            'align' => ''
        ),$instance);
        $this->addClass($instance['class']);
        $btnClass=array('ve-button');
        $style=$instance['style'];
        $size=$instance['size'];
        $color=$instance['color'];
        $shape=$instance['shape'];
        if($style){
            $btnClass[]='ve-button-'.$style;
        }
        if($shape){
            $btnClass[]='ve-button-'.$shape;
        }
        if($size){
            $btnClass[]='ve-button-'.$size;
        }
        if($color){
            $btnClass[]='ve-button-'.$color;
        }
        $btnClass[]=$instance['class'];
        $btnClass=join(' ',$btnClass);
        $link=$instance['link'];
        $href=$popup=$target='';
        if($link=='post'&&$instance['link_post']){
            $href=get_permalink($instance['link_post']);
        }elseif($link=='custom'){
            $href=$instance['link_url'];
        }elseif($link=='popup'){
            $popup=$instance['link_popup'];
        }
        $target=$instance['link_target'];

        $dataAttr=array();
        $data=array(
            'link'=>$instance['link'],
            'href'=>$href,
            'popup'=>$popup,
            'target'=>$target
        );
        foreach($data as $k=>$v){
            $dataAttr[]=sprintf('data-%s="%s"',$k,esc_attr($v));
        }
        //Move padding inside
        $paddingNames = array('padding-top','padding-bottom','padding-left','padding-right');
        $paddingAttrs = array();
        foreach($paddingNames as $patt){
            if($padding=$this->css($patt)){

                $paddingAttrs[]=sprintf('%s:%s;',$patt,$padding);
                $this->css($patt,'');
            }
        }
        $paddingAttrs = esc_attr(join(' ',$paddingAttrs));
        $dataAttr=join(' ',$dataAttr);
        printf('<div style="text-align: %4$s"><button style="%5$s" class="ve_el-button %1$s" value="%2$s" %3$s>%2$s</button></div>',$btnClass,$instance['value'],$dataAttr,$instance['align'], $paddingAttrs);
        if($popup&&$link=='popup'&&!ve_is_iframe()&&!ve_is_editor()) {
            //echo $this->popup_manager->getPopup($popup,array('open'=>''));
            //$this->popup_manager->popupScript();
        }
    }

    function form($instance,$content=''){
        $instance=shortcode_atts(array(
            'class'=>'',
            'value'=>'',
            'style'=>'',
            'color'=>'',
            'shape'=>'',
            'size'=>'',

            'link'=>'',
            'link_post'=>'',
            'link_popup'=>'',
            'link_url'=>'',
            'link_target'=>'',
            'align' => ''
        ),$instance);

        $button_styles=array(
            ''=>'Default',
            '3d'=>'3d',
            'raised'=>'raised',
            'glow'=>'glow',
            'wrap'=>'wrap',
        );
        $button_shapes=array(
            ''=>'Default',
            'rounded'=>'rounded',
            'square'=>'square',
            'box'=>'box',
            'circle'=>'circle',

        );

        $button_colors=array(
            ''=>'Default',
            'primary'=>'primary',
            'action'=>'action',
            'highlight'=>'highlight',
            'caution'=>'caution',
            'royal'=>'royal',
        );
        $button_sizes=array(
            ''=>'Default',
            'tiny'=>'tiny',
            'small'=>'small',
            'large'=>'Large',
            'jumbo'=>'Large',
            'giant'=>'giant',
            'block'=>'Full',
        );
        $button_links=array(
            ''=>'None',
            'post'=>'Link to a post',
            'popup'=>'Open popup',
            'custom'=>'Custom Link',
        );
        $link_targets=array(
            ''=>'_self',
            '_blank'=>'_blank',
            '_parent'=>'_parent',
            '_top'=>'_top',
        );
        $align=array(
            'left'=>'left',
            'right'=>'right',
            'center'=>'center'
        );
        $style=$instance['style'];
        $size=$instance['size'];
        $color=$instance['color'];
        $shape=$instance['shape'];
        $link=$instance['link'];
        $balign=$instance['align'];
        $link_post=$instance['link_post'];
        $link_popup=$instance['link_popup'];
        $link_custom=esc_attr($instance['link_url']);
        $link_target=$instance['link_target'];
        ?>
        <div class="ve_input_block">
            <label for="<?php echo $this->get_field_id('value');?>">Text:</label>
            <input class="medium" value="<?php echo $instance['value'];?>" name="<?php echo $this->get_field_name('value');?>" id="<?php echo $this->get_field_id('value');?>">
        </div>

        <div class="ve_input_block">
            <label for="<?php $this->field_id('style');?>">
                Button Style:
            </label>
            <select class="medium" id="<?php $this->field_id('style');?>" name="<?php $this->field_name('style');?>">
                <?php foreach($button_styles as $o_value=>$o_title){
                    $o_title=ucfirst($o_title);
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$style,false),$o_title);
                }?>
            </select>
        </div>

        <div class="ve_input_block">
            <label for="<?php $this->field_id('shape');?>">
                Button Shape:
            </label>
            <select class="medium" id="<?php $this->field_id('shape');?>" name="<?php $this->field_name('shape');?>">
                <?php foreach($button_shapes as $o_value=>$o_title){
                    $o_title=ucfirst($o_title);
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$shape,false),$o_title);
                }?>
            </select>
        </div>

        <div class="ve_input_block">
            <label for="<?php $this->field_id('color');?>">
               Button Color:
            </label>
            <select class="medium" id="<?php $this->field_id('color');?>" name="<?php $this->field_name('color');?>">
                <?php foreach($button_colors as $o_value=>$o_title){
                    $o_title=ucfirst($o_title);
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$color,false),$o_title);
                }?>
            </select>
        </div>

        <div class="ve_input_block">
            <label for="<?php $this->field_id('size');?>">
                Button size:
            </label>
            <select class="medium" id="<?php $this->field_id('size');?>" name="<?php $this->field_name('size');?>">
                <?php foreach($button_sizes as $o_value=>$o_title){
                    $o_title=ucfirst($o_title);
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$size,false),$o_title);
                }?>
            </select>
        </div>

        <div class="ve_input_block">
            <label for="<?php $this->field_id('link');?>">
                Button link:
            </label>
            <select class="medium" id="<?php $this->field_id('link');?>" name="<?php $this->field_name('link');?>">
                <?php foreach($button_links as $o_value=>$o_title){
                    $o_title=ucfirst($o_title);
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$link,false),$o_title);
                }?>
            </select>
        </div>

        <div class="ve_input_block" data-show-if="<?php $this->field_id('link');?>" data-show-value="post">
            <label>Select post or page:</label>
            <select id="<?php $this->field_id('link_post');?>" name="<?php $this->field_name('link_post');?>">
                <?php
                if($link_post&&$post=get_post($link_post))
                    printf('<option value="%s" selected="selected">%s</option>',$link_post,$post->post_title);?>
            </select>
            <script type="text/javascript">
                jQuery("#<?php $this->field_id('link_post');?>").select2({
                    width:"360",
                    ajax: {
                        url: ajaxurl,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page,
                                action:'ve_suggest',
                                type:'post,page'
                            };
                        },
                        processResults: function (data, page) {
                            // parse the results into the format expected by Select2.
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data
                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 1



                });
            </script>
        </div>
        <div class="ve_input_block" data-show-if="<?php $this->field_id('link');?>" data-show-value="popup">
            <label>Select popup:</label>
            <select id="<?php $this->field_id('link_popup');?>" name="<?php $this->field_name('link_popup');?>">
                <?php
                if($link_popup&&$post=get_post($link_popup))
                    printf('<option value="%s" selected="selected">%s</option>',$link_post,$post->post_title);?>
            </select>
            <script type="text/javascript">
                jQuery("#<?php $this->field_id('link_popup');?>").select2({
                    width:"360",
                    ajax: {
                        url: ajaxurl,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page,
                                action:'ve_suggest',
                                type:'<?php echo $this->post_manager->post_type_popup;?>'
                            };
                        },
                        processResults: function (data, page) {
                            // parse the results into the format expected by Select2.
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data
                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 1



                });
            </script>
        </div>
        <div class="ve_input_block" data-show-if="<?php $this->field_id('link');?>" data-show-value="custom">
            <label for="<?php $this->field_id('link_url');?>">Url:</label>
            <input type="text" class="medium" id="<?php $this->field_id('link_url');?>" name="<?php $this->field_name('link_url');?>" value="<?php echo $link_custom;?>"/>
        </div>
        <div class="ve_input_block" data-show-if="<?php $this->field_id('link');?>" data-show-value='["custom","post"]'>
            <label for="<?php $this->field_id('link_target');?>">Link Target:</label>
            <select class="medium" name="<?php $this->field_name('link_target');?>" id="<?php $this->field_id('link_target');?>">
                <?php foreach($link_targets as $o_value=>$o_title){

                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$link_target,false),$o_title);
                }?>
            </select>
        </div>
        <div class="ve_input_block">
            <label for="<?php $this->field_id('align');?>">
                Button Align:
            </label>
            <select class="medium" id="<?php $this->field_id('align');?>" name="<?php $this->field_name('align');?>">
                <?php foreach($align as $o_value=>$o_title){
                    $o_title=ucfirst($o_title);
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$balign,false),$o_title);
                }?>
            </select>
        </div>
        <div class="ve_element_preview" style="right: 20px;
position: absolute;
top: 80px;
width: auto;"></div>

        <p><label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('Extra class:'); ?></label>
            <input class="medium" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo esc_attr($instance['class']); ?>" /></p>

    <?php
    }
}

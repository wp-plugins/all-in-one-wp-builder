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
        $this->enqueue_js('el-button',__DIR__.'/../../view/js/elements/ve-button.js');
        $this->ready('ve_front.button.start();');
    }
    function element($instance,$content=''){
        $instance=shortcode_atts(array(
            'class'=>'',
            'value'=>'',
            'style'=>'',
            'size'=>'',
            'link'=>'',
            'link_post'=>'',
            'link_popup'=>'',
            'link_url'=>'',
            'link_target'=>'',
            'align' => ''
        ),$instance);
        $this->addClass($instance['class']);
        $btnClass='';
        $style=$instance['style'];
        $size=$instance['size'];
        if(!$style){
            $style='default';
        }
        $btnClass.=' ve-btn-'.$style;
        if($size){
            $btnClass.=' ve-btn-'.$size;
        }
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
        $dataAttr=join(' ',$dataAttr);
        printf('<div style="text-align: %4$s"><button class="ve_el-button ve-btn%1$s" value="%2$s"%3$s>%2$s</button></div>',$btnClass,$instance['value'],$dataAttr,$instance['align']);
        if($popup&&$link=='popup') {
            //echo $this->popup_manager->getPopup($popup);
            //$this->popup_manager->popupScript();
        }
    }
    function form($instance,$content=''){
        $instance=shortcode_atts(array(
            'class'=>'',
            'value'=>'',
            'style'=>'',
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
            'primary'=>'primary',
            'success'=>'success',
            'info'=>'info',
            'warning'=>'warning',
            'danger'=>'danger',
            'link'=>'link',
        );
        $button_sizes=array(
            ''=>'Default',
            'lg'=>'Large',
            'sm'=>'Small',
            'xs'=>'Extra Small',
            'block'=>'Full',
        );
        $button_links=array(
            ''=>'None',
            'post'=>'Link to a post',
            'popup'=>'Open popup',
            'custom'=>'Custom Link',
        );
        $link_targets=array(
            ''=>'Current window',
            '_blank'=>'New window',
            '_parent'=>'Parent',
            '_top'=>'Top',
        );
        $align=array(
            'left'=>'left',
            'right'=>'right',
            'center'=>'center'
        );
        $style=$instance['style'];
        $size=$instance['size'];
        $link=$instance['link'];
        $balign=$instance['align'];
        $link_post=$instance['link_post'];
        $link_popup=$instance['link_popup'];
        $link_custom=esc_attr($instance['link_url']);
        $link_target=$instance['link_target'];
        ?>
        <div class="edit_form_line">
            <label for="<?php echo $this->get_field_id('value');?>">Text</label>
            <input class="widefat" value="<?php echo $instance['value'];?>" name="<?php echo $this->get_field_name('value');?>" id="<?php echo $this->get_field_id('value');?>">
        </div>
        <div class="edit_form_line">
            <label for="<?php $this->field_id('style');?>">
               Button Style
            </label>
            <select id="<?php $this->field_id('style');?>" name="<?php $this->field_name('style');?>">
                <?php foreach($button_styles as $o_value=>$o_title){
                    $o_title=ucfirst($o_title);
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$style,false),$o_title);
                }?>
            </select>
        </div>

        <div class="edit_form_line">
            <label for="<?php $this->field_id('size');?>">
                Button size
            </label>
            <select id="<?php $this->field_id('size');?>" name="<?php $this->field_name('size');?>">
                <?php foreach($button_sizes as $o_value=>$o_title){
                    $o_title=ucfirst($o_title);
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$size,false),$o_title);
                }?>
            </select>
        </div>

        <div class="edit_form_line">
            <label for="<?php $this->field_id('link');?>">
                Button link
            </label>
            <select id="<?php $this->field_id('link');?>" name="<?php $this->field_name('link');?>">
                <?php foreach($button_links as $o_value=>$o_title){
                    $o_title=ucfirst($o_title);
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$link,false),$o_title);
                }?>
            </select>
        </div>
        <div class="edit_form_line">
            <label for="<?php $this->field_id('align');?>">
                Button Align
            </label>
            <select id="<?php $this->field_id('align');?>" name="<?php $this->field_name('align');?>">
                <?php foreach($align as $o_value=>$o_title){
                    $o_title=ucfirst($o_title);
                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$balign,false),$o_title);
                }?>
            </select>
        </div>
        <div class="edit_form_line" data-show-if="<?php $this->field_id('link');?>" data-show-value="post">
            <label>Select post or page:</label><br/>
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
        <div class="edit_form_line" data-show-if="<?php $this->field_id('link');?>" data-show-value="popup">
            <label>Select popup:</label><br/>
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
        <div class="edit_form_line" data-show-if="<?php $this->field_id('link');?>" data-show-value="custom">
            <label for="<?php $this->field_id('link_url');?>">Url:</label>
            <input type="text" class="widefat" id="<?php $this->field_id('link_url');?>" name="<?php $this->field_name('link_url');?>" value="<?php echo $link_custom;?>"/>
        </div>
        <div class="edit_form_line" data-show-if="<?php $this->field_id('link');?>" data-show-value='["custom","post"]'>
            <label for="<?php $this->field_id('link_target');?>">Link Target:</label>
            <select name="<?php $this->field_name('link_target');?>" id="<?php $this->field_id('link_target');?>">
                <?php foreach($link_targets as $o_value=>$o_title){

                    printf('<option value="%s"%s>%s</option>',$o_value,selected($o_value,$link_target,false),$o_title);
                }?>
            </select>
        </div>
        <div class="ve_element_preview" style="right: 20px;
position: absolute;
top: 80px;
width: auto;"></div>

        <p><label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('Extra class:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo esc_attr($instance['class']); ?>" /></p>

    <?php
    }
}

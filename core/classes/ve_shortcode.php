<?php
class VE_ShortCode extends VE_Manager_Abstract{
    var $post_ids=array();
    function _construct(){
        $this->set('widget','ve_widget');
        $this->set('popup','ve_popup');
    }
    function bootstrap(){
        $this->addShortCodes();
    }
    function addShortCodes(){
        add_shortcode($this->get('widget'),array($this,'renderWidget'));
        add_shortcode($this->get('popup'),array($this,'renderPopup'));
    }
    function renderWidget($atts){
        $atts=shortcode_atts(array(
            'id'=>0,
            'position'=>'',
            'top'=>'',
            'left'=>'',
            'right'=>'',
            'bottom'=>'',
            'margin-top'=>'',
            'margin-left'=>'',
            'margin-right'=>'',
            'margin-bottom'=>'',
            'width'=>'',
            'height'=>'',
            'align' => '',
            'style' => '',
            'class'=>'',

        ),$atts);
        if($atts['id']){
            $html_attributes=$atts;
            $pid=$atts['id'];
            if(!isset($this->post_ids[$pid])){
                $this->post_ids[$pid]=1;
            }else{
                $this->post_ids[$pid]++;
            }
            $html_id='ve-widget-'.$html_attributes['id'];
            if($this->post_ids[$pid]>1){
                $html_id.='-'.$this->post_ids[$pid];
            }
            $html_class='ve-widget '.$html_attributes['class'];
            if(!empty($html_attributes['align']))
            {
                $html_class.= " ve_widget_".$html_attributes['align'];
            }
            $widget=get_post($atts['id']);
            if(empty($html_attributes['width']))
            {
                $size  = ve_get_post_meta('screen_size',$atts['id']);
                if(!empty($size))
                {
                    $size = intval($size) - 82;
                    $html_attributes['max-width'] = $size . 'px';
                    $html_attributes['width'] = '100%';
                }

            }
            $extra_style = $html_attributes['style'];
            unset($html_attributes['id']);
            unset($html_attributes['class']);
            unset($html_attributes['align']);
            unset($html_attributes['style']);
            $attr=array();
            foreach($html_attributes as $k=>$v){
                $attr[]=esc_attr(sprintf('%s:%s;',$k,$v));
            }
            $attr=join(' ',$attr);
            $before=sprintf('<div id="%s" class="%s" style="%s %s">',$html_id,esc_html($html_class), $attr, $extra_style);
            $after='</div>';


            if($this->getVeManager()->getPostManager()->isWidget($widget))
                return $before.$this->content($widget).$after;
        }
        return '';
    }
    function renderPopup($atts){
        $atts=wp_parse_args($atts,array(
            'id'=>0,
        ));
        if($atts['id']){
            $popup=get_post($atts['id']);
            if($this->getVeManager()->getPostManager()->isPopup($popup)){
                $args=$atts;
                unset($args['id']);
                return $this->getVeManager()->getPopupManager()->getPopup($popup,$args);
            }
        }
        return '';
    }
    function content($post){
        $content='';
        if($post&&$post->post_content) {
            $content = $post->post_content;
            $content = apply_filters('the_content', $content);
            $content = str_replace(']]>', ']]&gt;', $content);
        }
        return $content;
    }

}
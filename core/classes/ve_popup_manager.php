<?php
class VE_Popup_Manager extends VE_Manager_Abstract{
    var $loadedPopup=array();
    var $forceLoadPopup=array();
    function bootstrap(){
        $this->setTemplate('popup');
        add_action('wp_footer',array($this,'loadPopups'));
        if(!ve_is_editor()) {
            add_action('wp_footer', array($this, 'popupScript'));
        }
    }
    function loadPopups(){
        if($this->canLoadPopups()) {
            $popups = $this->getPopups();
            //print_r($popups);
            foreach ($popups as $popup) {
                $this->loadPopup($popup);
            }
        }
    }
    function canLoadPopups(){
        if($this->getVeManager()->getPostManager()->isPopup(get_post())){
            return false;
        }
        return true;
    }
    function loadPopup($post,$args=array()){
        $post=get_post($post);
        if($post&&!in_array($post->ID,$this->loadedPopup)&&$this->shouldLoadPopup($post)) {
            $this->loadedPopup[]=$post->ID;
            if (!$this->getVeManager()->getPostManager()->isPopup($post)) {
                return;
            } else {
                $template=$this->getTemplate();
                $editor=$this->getVeManager()->getEditor();
                $this->getVeManager()->getViewManager()->render($template,array('popup'=>$post,'editor'=>$editor,'args'=>$args));
            }
        }

    }
    function getPopup($post,$args=array()){
        if(empty($post)){
            return '';
        }
        $post=get_post($post);
        if($post&&!in_array($post->ID,$this->loadedPopup)) {
            $this->loadedPopup[]=$post->ID;
            if (!$this->getVeManager()->getPostManager()->isPopup($post)) {
                return '';
            } else {
                ob_start();
                $template=$this->getTemplate();
                $editor=$this->getVeManager()->getEditor();
                $this->getVeManager()->getViewManager()->render($template,array('popup'=>$post,'editor'=>$editor,'args'=>$args));
                return ob_get_clean();
            }
        }
    }
    function shouldLoadPopup($popup){
        $placement=$popup->placement;
        $open=$popup->open;
        if($placement=='all'||empty($open)){
            return true;
        }
        $popup_category=$popup->popup_category;
        $popup_post=$popup->popup_post;
        $popup_page=$popup->popup_page;
        switch($placement){
            case 'post':
                if(is_single()){
                    if(!$popup_post){
                        return true;
                    }else{
                        return in_array(get_the_ID(),(array)$popup_post);
                    }
                }
                break;
            case 'page':
                if(is_page()){
                    if(!$popup_page){
                        return true;
                    }else{
                        return in_array(get_the_ID(),(array)$popup_page);
                    }
                }
                break;
            case 'category':
                if(is_single()){
                    if(!$popup_category){
                        return false;
                    }else{
                        global $post;
                        $categories=get_the_category($post->ID);
                        $category_ids=array_map(function($cat){return $cat->cat_ID;},$categories);
                        $found = false;
                        foreach($category_ids as $id)
                        {
                            if(in_array($id,(array)$popup_category))
                            {
                                $found = true;
                                break;
                            }
                        }
                        return $found;
                    }
                }
                break;
        }

        return false;//default
    }
    function getTemplate(){
        if($this->has('template')){
            return $this->get('template');
        }
        return false;
    }
    function setTemplate($template){
        $this->set('template',$template);
        return $this;
    }
    function getPopups($query=array(),$args=array()){
        $query=wp_parse_args($query,array(
            'numberposts'=>-1,
            'post_type'=>$this->getVeManager()->getPostManager()->post_type_popup,
        ));
        $popups=get_posts($query);
        return $popups;
    }
    function popupScript(){
        if(!empty($this->loadedPopup)){
            $src=ve_resource_url(__DIR__.'/../../view/js/ve_popup.js');
            wp_enqueue_script('ve_popup_js',$src,array('jquery'),VE_VERSION,true);
        }
    }
}
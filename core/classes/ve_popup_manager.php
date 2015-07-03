<?php
class VE_Popup_Manager extends VE_Manager_Abstract{
    var $loadedPopup=array();
    var $forceLoadPopup=array();
    var $popups;
    var $activeOptions = array('open_with_delay' => false, "open_on_mouse_out" => false);
    function bootstrap(){
        $this->setTemplate('popup');
        $this->setupMetaBox();
        add_action('wp_footer',array($this,'loadPopups'));
        if(!ve_is_editor()) {
            add_action('wp_footer', array($this, 'popupScript'));
        }
    }
    function loadPopups(){
        if($this->canLoadPopups()) {
            $this->loadPopupSingle();
            $popups = $this->getPopups();
            foreach ($popups as $popup) {
                $this->shouldLoadPopup($popup);
            }
            $index = 1;
            foreach($this->activeOptions as $option)
            {
                echo $this->getPopup($option,$index);
                $index++;
            }

        }
    }
    function addPopupOptions($options){
        $open = $options['open'];
        if(!empty($open) && isset($this->activeOptions[$open]))
        {
            if($this->activeOptions[$open] === false || intval($this->activeOptions[$open]['priority']) < intval($options['priority']))
            {
                $this->activeOptions[$open] = $options;
            }
        }
    }
    function canLoadPopups(){
        if(is_admin()|| is_ve()){
            return false;
        }
        return true;
    }

    function loadPopupSingle(){
        if(is_single()||is_page()){
            $post=get_post();
            if($popup_id=$post->ve_popup_id){
                $args=array(
                    'position'=>$post->ve_popup_position,
                    'open'=>$post->ve_popup_open,
                    'delay'=>$post->ve_popup_open_delay,
                    'priority'=>10,
                    'popup_id'=> $popup_id
                );
                $this->addPopupOptions($args);
            }
        }
    }
    function getPopup($option, $index){
        $post = get_post($option['popup_id']);
        $this->loadedPopup[] = $option;
        if(empty($post)){
            return '';
        }
        if($post) {
            if (!$this->getVeManager()->getPostManager()->isPopup($post)) {
                return '';
            } else {
                ob_start();
                $template=$this->getTemplate();
                $editor=$this->getVeManager()->getEditor();
                $this->getVeManager()->getViewManager()->render($template,array('popup'=>$post,'editor'=>$editor,'option'=>$option,'index' => $index));
                return ob_get_clean();
            }
        }
        return '';
    }
    function shouldLoadPopup($popup){
        $poptions = get_post_meta($popup->ID,'_ve_poptions',true);
        if(!is_array($poptions))
        {
            $poptions = array();
        }
        foreach($poptions as $option) {
            $option=wp_parse_args($option,array(
                'popup_category'=>'',
                'popup_post'=>'',
                'popup_page'=>'',
            ));
            $placement = $option['placement'];
            $open = $option['open'];
            $option['popup_id'] = $popup->ID;

            if ($placement == 'all') {
                $option['priority'] = 0;
                $this->addPopupOptions($option);
            }
            $popup_category = $option['popup_category'];
            $popup_post = $option['popup_post'];
            $popup_page = $option['popup_page'];
            switch ($placement) {
                case 'post':
                    if (is_single()) {
                        if (!$popup_post) {
                            $option['priority'] = 1;
                            $this->addPopupOptions($option);
                        } else {
                             if (in_array(get_the_ID(), (array)$popup_post))
                                 $option['priority'] = 3;
                                 $this->addPopupOptions($option);
                        }
                    }
                    break;
                case 'page':
                    if (is_page()) {
                        if (!$popup_page) {
                            $option['priority'] = 1;
                            $this->addPopupOptions($option);

                        } else {
                            if( in_array(get_the_ID(), (array)$popup_page))
                                $option['priority'] = 3;
                                $this->addPopupOptions($option);
                        }
                    }
                    break;
                case 'category':
                    if (is_single()) {
                        if (!$popup_category) {
                            return false;
                        } else {
                            global $post;
                            $categories = get_the_category($post->ID);
                            $category_ids = array_map(function ($cat) {
                                return $cat->cat_ID;
                            }, $categories);
                            $found = false;
                            foreach ($category_ids as $id) {
                                if (in_array($id, (array)$popup_category)) {
                                    $found = true;
                                    break;
                                }
                            }
                            $option['priority'] = 2;
                            if($found)
                                $this->addPopupOptions($option);
                        }
                    }
                    break;
            }
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

    /**
     * @param array $query
     * @param array $args
     * @return WP_Post[]
     */
    function getPopups($query=array(),$args=array()){
        if($this->popups){
            return $this->popups;
        }
        $query=wp_parse_args($query,array(
            'numberposts'=>-1,
            'post_type'=>$this->getVeManager()->getPostManager()->post_type_popup,
        ));
        $this->popups=get_posts($query);
        return $this->popups;
    }
    function popupScript(){
        if(!empty($this->loadedPopup)){
            $src=ve_resource_url(__DIR__.'/../../view/js/ve_popup.js');
            $src_cookie=ve_resource_url(__DIR__.'/../../view/libraries/jquery-cookie/jquery.cookie.js');
            $src_storage=ve_resource_url(__DIR__.'/../../view/libraries/jquery-storage/jquery.storage.js');
            wp_register_script('jquery.cookie',$src_cookie,array('jquery'),VE_VERSION,true);
            wp_register_script('jquery.storage',$src_storage,array('jquery.cookie'),VE_VERSION,true);
            wp_enqueue_script('ve_popup_js',$src,array('jquery.storage'),VE_VERSION,true);
        }
    }
    function setupMetaBox(){
        add_action('add_meta_boxes',function(){
            add_meta_box('ve-popup-setting','Popup',array($this,'metaBoxCallback'),'page','side');
        });
        add_action('save_post',array($this,'savePostData'));
    }
    function savePostData($post_ID){
        if(isset($_POST['ve_post_popup'])){
            update_post_meta($post_ID,'ve_popup_id',$_POST['ve_post_popup']);
            update_post_meta($post_ID,'ve_popup_position',$_POST['ve_popup_position']);
            update_post_meta($post_ID,'ve_popup_open',$_POST['ve_popup_open']);
            update_post_meta($post_ID,'ve_popup_open_delay',$_POST['ve_popup_open_delay']);
        }
    }
    function metaBoxCallback($post){
        $this->getVeManager()->getViewManager()->render('metabox-popup',array('post'=>$post,'popupManager'=>$this));
    }
}
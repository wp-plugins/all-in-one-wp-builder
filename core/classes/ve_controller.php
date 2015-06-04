<?php

/**
 * Handle various action for GET POST
 * Class VE_Controller
 */
class VE_Controller extends VE_Manager_Abstract{
    function _construct(){

    }
    function bootstrap(){
        add_action('wp_ajax_ve_get_element',array($this,'getElement'));
        add_action('wp_ajax_ve_get_form',array($this,'getElementForm'));
        add_action('wp_ajax_ve_save_setting',array($this,'saveSetting'));
        add_action('wp_ajax_ve_save_post_setting',array($this,'savePostSetting'));
        add_action('wp_ajax_ve_update_post',array($this,'updatePost'));
        add_action('wp_ajax_ve_update_post_meta',array($this,'updatePostMeta'));
        add_action('wp_ajax_ve_suggest',array($this,'suggest'));
    }
    function suggest(){
        $query=isset($_GET['q'])?$_GET['q']:'';
        $type=isset($_GET['type'])?$_GET['type']:'';
        if(strpos($type,',')){
            $type = preg_split('/[\s,]+/', $type);
        }
        if(empty($type)){
            $type='post';
        }
        $result=array();
        $posts=get_posts(array('s'=>$query,'numberposts'=>10,'post_type'=>$type));
        if($posts){
            foreach($posts as $post){
                $item=['text'=>$post->post_title,'id'=>$post->ID];
                $result[]=$item;
            }
        }

        echo json_encode($result);die;

    }
    function updatePostMeta(){
        $response=array();
        $post_id=$_POST['post_id'];
        if(!current_user_can('edit_post',$post_id)){
            return false;
        }
        $post=get_post($post_id);
        if(!$post){
            return false;
        }
        $meta=$_POST;
        unset($meta['post_id']);
        unset($meta['action']);
        unset($meta['ve_inline']);
        if(isset($meta['post_title']))
        {
            $post->post_title = $meta['post_title'];
            wp_update_post($post);
            unset($meta['post_title']);
        }
        if(!empty($meta)&&is_array($meta)) {
            foreach($meta as $meta_key=>$meta_value) {
                if($meta_key&&is_string($meta_key)) {
                    if(in_array($meta_key,array('top','left','right','bottom','width','height'))){
                        if($meta_value!==''){
                            $meta_value=$this->sanitize_size($meta_value);
                            $response[$meta_key]=$meta_value;
                        }
                    }
                    update_post_meta($post_id, $meta_key, $meta_value);
                }
            }
        }
        echo json_encode($response);
        die;
    }
    function sanitize_size($size){
        if(is_numeric($size)) {
            if($size!=='0') {
                $size = $size . 'px';
            }
        }else{
            if (!preg_match('#\d+[%px]#', $size)) {
                $size = '';
            }
        }
        return $size;
    }
    function updatePost(){
        $response=array();
        $post_id=$_POST['post_id'];
        if(!current_user_can('edit_post',$post_id)){
            return false;
        }
        $post=get_post($post_id);
        if(!$post){
            return false;
        }
        $postData=$post->to_array();
        $post_fields=array_keys($postData);
        foreach($post_fields as $field){
            if(isset($_POST[$field])){
                $postData[$field]=$_POST[$field];
            }
        }
        //$post->post_title=$_POST['post_title'];
        wp_update_post($postData);
        $post=get_post($post_id);
        $response['link']=get_permalink($post_id);
        $response['post_name']=$post->post_name;
        if(isset($_POST['metas']))
            update_post_meta($post_id,'_ve_metas',$_POST['metas']);
        if(isset($_POST['page_template'])){
            update_post_meta($post_id,'_wp_page_template',$_POST['page_template']);
        }
        echo json_encode($response);
        die;
    }
    function saveSetting(){
        $setting=$_POST['settings'];
        update_user_meta(get_current_user_id(),'ve_settings',$setting);
    }
    function savePostSetting(){
        $post_id=$_POST['post_id'];
        $setting=$_POST['settings'];
        update_post_meta($post_id,'ve_settings',$setting);
    }
    /**
     * get element for editor
     * @return bool
     */
    function getElement(){
        define('VE_EL_EDITING',true);
        $elements=$_POST['elements'];
        $output='';
        $editor=$this->getVeManager()->getEditor();
        $this->setPost();
        foreach($elements as $element){
            if(!empty($element['shortcode'])&&!empty($element['id'])&&!empty($element['id_base'])){
                $output.=$editor->do_element($element);
            }
        }
        echo $output;
        echo '<div data-type="files">';
        _print_styles();
        print_head_scripts();
        print_late_styles();
        print_footer_scripts();
        echo '</div>';
        die;
    }
    /**
     * get element form
     */
    function getElementForm(){
        $element=$_POST['element'];
        $shortcode=$_POST['shortcode'];
        $shortcode=stripslashes($shortcode);
        if($this->getVeManager()->getElementManager()->addFormShortCode($element)){
            echo do_shortcode($shortcode);
            die;
        }

    }
    function setPost(){
        if(isset($_POST['post_id'])){
            $GLOBALS['post']=get_post($_POST['post_id']);
        }
        return $this;
    }
}
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

        add_action('wp_ajax_ve_add_popup_option',array($this,'addPopupOptions'));
        add_action('wp_ajax_ve_get_popup_options',array($this,'getPopupOptions'));
        add_action('wp_ajax_ve_delete_popup_option',array($this,'deletePopupOptions'));
        add_action('wp_ajax_ve_list_popup_options',array($this,'loadPopupOptionsList'));

        add_action('wp_ajax_ve_suggest',array($this,'suggest'));
        add_action('wp_ajax_ve_save_as_template',array($this,'saveAsTemplate'));
        add_action('wp_ajax_ve_get_template',array($this,'getTemplate'));
        add_action('wp_ajax_ve_save_as_element',array($this,'saveAsElement'));
        add_action('wp_ajax_ve_delete_post',array($this,'deletePost'));
        add_action('wp_ajax_ve_clone_post',array($this,'clonePost'));
        add_action('wp_ajax_ve_list_posts',array($this,'listPosts'));
    }
    function deletePost(){
        $post_id=isset($_POST['post_id'])?$_POST['post_id']:'';
        $post_type=isset($_POST['post_type'])?$_POST['post_type']:'';
        if($post_id&&$post_type){
            wp_delete_post($post_id);
            $this->loadPostList($post_type);
        }
    }
    function clonePost(){
        $post_id=isset($_POST['post_id'])?$_POST['post_id']:'';
        $post_type=isset($_POST['post_type'])?$_POST['post_type']:'';
        if($post_id&&$post_type){
            $post=get_post($post_id);
            $post->post_title.=' (clone)';
            unset($post->ID);
            wp_insert_post($post);
            $this->loadPostList($post_type);
        }
    }
    function listPosts(){
        $post_type=isset($_POST['post_type'])?$_POST['post_type']:'';
        $paged=isset($_POST['paged'])?$_POST['paged']:'';
        $this->loadPostList($post_type);
    }
    function loadPostList($post_type,$args=array()){
        if(!$post_type){
            $post_type=isset($_POST['post_type'])?$_POST['post_type']:'page';
        }
        $args=wp_parse_args($args,array(
            'post_type'=>$post_type,
            'screen'=>'ve-list-posts',
        ));
        $list_table=$this->getVeManager()->getListTableManager()->getTable('VE_Post_List_Table',$args);
        $list_table->ajax_response();
        die;
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
                $item=array('text'=>$post->post_title,'id'=>$post->ID);
                $result[]=$item;
            }
        }

        echo json_encode($result);die;

    }
    function saveAsTemplate(){
        $post_id=absint($_POST['from_page']);
        $post_title=$_POST['post_title'];
        $post_content=$_POST['post_content'];
        $page=get_post($post_id);
        $response=array();
        if($post_id&&$page&&$page->post_content&&$page->_use_ve){
            $page->post_type=$this->getVeManager()->getPostManager()->post_type_template;
            $page->post_title=$post_title;
            $page->post_content=$post_content;
            unset($page->ID,$page->post_date,$page->post_date_gmt,$page->post_name);
            $template_id=wp_insert_post($page);
            $response['template_id']=$template_id;
            echo json_encode($response);die;
        }
    }
    function saveAsElement(){
        $post_id=absint($_POST['from_page']);
        $post_title=$_POST['post_title'];
        $post_content=$_POST['post_content'];
        $icon_class=$_POST['icon_class'];
        $page=get_post($post_id);
        $response=array();
        if($post_id&&$page&&$page->post_content&&$page->_use_ve){
            $page->post_type=$this->getVeManager()->getPostManager()->post_type_element;
            $page->post_title=$post_title;
            $page->post_content=$post_content;
            unset($page->ID,$page->post_date,$page->post_date_gmt,$page->post_name);
            $new_post_id=wp_insert_post($page);
            update_post_meta($new_post_id,'icon_class',$icon_class);
            $response['element_id']=$new_post_id;
            echo json_encode($response);die;
        }
    }
    function getTemplate(){
        $template=absint($_POST['template']);
        if(!$template) {
            return false;
        }
        $template = get_post($template);
        if(!$this->getVeManager()->getPostManager()->isTemplate($template)){
            return false;
        }
        $editor=$this->getVeManager()->getEditor();
        $html=$editor->getContentToEdit($template);
        $html.=$this->getVeManager()->getViewManager()->getHtml('no-content-helper');
        $elements=$editor->post_elements;
        echo json_encode(compact('html','elements'));die;


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
                    if(in_array($meta_key,array('top','left','right','bottom',
                        'close_btn_top','close_btn_left','close_btn_right','close_btn_bottom',
                        'width','height'))){
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
    function loadPopupOptionsList(){
        $popupOptionList=$this->getVeManager()->getListTableManager()->getTable('VE_PopupOption_List_Table',array('screen'=>'ve-list-popup-options'));
        $popupOptionList->ajax_response();
    }
    function deletePopupOptions(){
        $option_id =$_POST['option_id'];
        $parts = explode('-',$option_id);
        $idx = intval($parts[1]);
        $ve_options = get_post_meta($parts[0],'_ve_poptions',true);
        //$ve_options = array_values($ve_options);
        if(isset($ve_options[$idx])){
            unset($ve_options[$idx]);
        }
        update_post_meta($parts[0],'_ve_poptions',$ve_options);
        $this->loadPopupOptionsList();
        die;
    }

    function addPopupOptions(){
        $response=array();
        $post_id=$_POST['popup_id'];
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
        unset($meta['option_id']);
        if(!empty($_POST['option_id'])){
            $option_id =$_POST['option_id'];
            $parts = explode('-',$option_id);
            if(isset($parts[1])) {
                $post_id=$parts[0];
                $option_id = intval($parts[1]);
            }
        }
        $poptions = get_post_meta($post_id,'_ve_poptions',true);
        if(!is_array($poptions))
        {
            $poptions = array();
        }
        if(isset($option_id)) {
            $poptions[$option_id] = $meta;
        }else{
            $poptions[]=$meta;
        }

        update_post_meta($post_id, '_ve_poptions', $poptions);

        if(!empty($meta)&&is_array($meta)) {
            foreach($meta as $meta_key=>$meta_value) {
                if($meta_key&&is_string($meta_key)) {
                    if(in_array($meta_key,array('top','left','right','bottom',
                        'close_btn_top','close_btn_left','close_btn_right','close_btn_bottom',
                        'width','height'))){
                        if($meta_value!==''){
                            $meta_value=$this->sanitize_size($meta_value);
                            $response[$meta_key]=$meta_value;
                        }
                    }
                    //update_post_meta($post_id, $meta_key, $meta_value);
                }
            }
        }
        $this->loadPopupOptionsList();
        die;
    }
    function getPopupOptions(){
        $query=new WP_Query();
        $args=array(
            'post_type' => $this->getVeManager()->getPostManager()->post_type_popup,
            'posts_per_page' => -1,
            'meta_query' => array(
                array(

                )
            ),
            'post_status' => array('publish','draft')
        );


        $posts = $query->query($args);
        $popupOptions=array();
        foreach($posts as $p){
            $draf = "";
            if ($p->post_status == "draft")
            {
                $draf = " <b>(Draft)</b>";
            }
            $poptions = get_post_meta($p->ID,'_ve_poptions',array());
            if(!is_array($poptions)) {
                $poptions = array();
            }
            $popupOptions[$p->ID] = $p->post_title . $draf . ' (' . count($poptions) .' options)';
        }
        $results=array();
        if(isset($_POST['post_id'])) {
            $current_popup_id = absint($_POST['post_id']);
            $poptions = get_post_meta($current_popup_id, '_ve_poptions', array());
            if(!is_array($poptions)){
                $poptions=array();
            }
            $positionOptions = array(
                'center' => 'Center',
                'top-left' => 'Top left',
                'top-right' => 'Top Right',
                'bottom-left' => 'Bottom left',
                'bottom-right' => 'Bottom Right'
            );
            $placementOptions = array(
                '' => 'None',
                'all' => 'Whole site',
                'post' => 'All posts',
                'page' => 'All pages',
                'category' => 'By Category',

            );
            $openOptions = array(
                '' => 'Not open automatically',
                'open_on_mouse_out' => 'Open when mouse out of page',
                'open_with_delay' => 'Open after page loaded',
            );
            $list=array();
            foreach ($poptions as $option) {
                $str = $positionOptions[$option['position']] . "_";
                $str .= $placementOptions[$option['placement']] . "_";
                $str .= $openOptions[$option['open']];
                $list[]='<li>'.$str.'</li>';
            }
            $list=join('',$list);
            $results['list']=$list;
        }

        $results['options']=$popupOptions;
        echo json_encode($results);
        die;
    }
    function sanitize_size($size){
        if(is_numeric($size)) {
            if($size!=='0') {
                $size = $size . 'px';
            }
        }else{
            if (!preg_match('#(\d+)[%px]#', $size,$matches)) {
                $size = '';
            }else{
                if($matches[1]==='0'){
                    $size='0';
                }
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
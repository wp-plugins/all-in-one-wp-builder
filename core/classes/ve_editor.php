<?php
class VE_Editor extends VE_Manager_Abstract{
    /**
     * @var VE_View_Manager
     */
    var $viewManager;
    /**
     * @var VE_Element_Manager
     */
    var $elementManager;
    /**
     * current post
     * @var WP_Post
     */
    var $post;
    var $post_id;
    var $post_url;
    var $post_type;
    var $edit_iframe_url;
    var $e_index=0;
    var $element_scripts=array();

    /*
     * @var string current Elements on post
     */
    var $post_elements=array();
    /**
     * @var array list of base elements: container row, container column, and text
     */
    var $base_elements=array();
    function _construct(){
        $this->base_elements=array(
            'row'=>'ve_row',
            'col'=>'ve_col',
            'text'=>'ve_text',
            'anchor'=>'ve_container_anchor',
        );
        $this->set('page_template','iframe.phtml');
    }
    function bootstrap(){
        $ve=$this->getVeManager();
        $this->viewManager=$ve->getViewManager();
        $this->elementManager=$ve->getElementManager();

    }
    function configure(){
        add_action('init',array($this,'init'));
    }
    function init(){
        $this->addHooks();
        $this->run();
    }
    function run(){
        /**
         * If current mode is Editor, load it
         */
        if(ve_is_editor()) {
            $this->buildEditor();
        } elseif(ve_is_iframe()) {
            /**
             * if page loaded inside frontend editor iframe it has page_editable mode.
             * It required to some some js/css elements and add few helpers for editor to be used.
             */
            $this->buildEditablePage();
        } else {
            // Is it is simple page just enable buttons and controls
            //$this->buildPage();
        }
    }
    function addHooks(){
        add_shortcode($this->base_elements['anchor'],array($this,'containerAnchor'));
        add_action('wp_insert_post',array($this,'updatePost'));

        add_filter( 'get_edit_post_link', array( &$this, 'editPostLink' ) ,10,2);
    }




    function updatePost($post_id){
        if(!empty($_POST['ve_inline'])){
            update_post_meta($post_id,'_use_ve','1');
            $this->updatePostCustomCss($post_id);
        }
    }
    function updatePostCustomCss($post_id){
        $post=get_post($post_id);
        if($post){
            //echo 'post';
            //echo $post->post_content;
            $css=$this->parseElementsCustomCss($post->post_content);
            if($css){
                update_post_meta($post_id,'_ve_element_custom_css',$css);
            }else{
                delete_post_meta($post_id,'_ve_element_custom_css');
            }
        }
    }
    protected function parseElementsCustomCss( $content ) {
        $css = '';
        preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $shortcodes );

        foreach ( $shortcodes[2] as $index => $tag ) {
            $element=$this->elementManager->getElement($tag);
            if($element){
                $atts=shortcode_parse_atts($shortcodes[3][$index]);
                if(!empty($atts['custom_css'])&&!empty($atts['custom_css_class'])){
                    $custom_css=trim($atts['custom_css']);
                    //$custom_css=trim($custom_css,'{}');
                    //echo $custom_css;
                    $custom_css=ve_fix_custom_css($custom_css);
                    $custom_css_class=$atts['custom_css_class'];
                    if($custom_css) {
                        $custom_css = sprintf('.%s{%s}', $custom_css_class, $custom_css);
                        if($scss=ve_scssc()){
                            try{
                                $compiledCss=$scss->compile($custom_css);
                            }catch (Exception $e){
                                $compiledCss='';
                            }
                            if($compiledCss){
                                $custom_css=$compiledCss;
                            }
                        }
                        $css.=$custom_css;
                    }

                }
            }
        }
        foreach ( $shortcodes[5] as $shortcode_content ) {
            $css .= $this->parseElementsCustomCss( $shortcode_content );
        }
        return $css;
    }



    function renderRowAction( $actions ) {
        $post = get_post();
        if ( $this->canUseEditor( $post->ID ) ) {
            $actions['edit_ve'] = '<a
		href="' . $this->getEditUrl( $post->ID ) . '">' . __( 'Edit with Visual Editor', 'visual_editor' ) . '</a>';
        }

        return $actions;
    }
    function canUseEditor( $post_id = null ) {
        get_currentuserinfo();
        if(!$post_id){
            $post_id=get_the_ID();
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) return false;
        $use_ve = false;
        if(!empty($post_id)) {
            $use_ve = get_post_meta($post_id,'_use_ve',true);
        }
        $post=get_post($post_id);

        return in_array( $post->post_type, $this->getVeManager()->getPostManager()->getPostTypes(true) ) ||( $use_ve=='1' && in_array( $post->post_type, $this->getVeManager()->getPostManager()->getPostTypes() ));
    }
    function getEditUrl($post_id=null,$url=''){
        if(!$post_id){
            $post_id=get_the_ID();
        }
        return apply_filters( 've_get_edit_url', admin_url() .
            'edit.php?ve_action=ve_inline&post_id=' .
            $post_id . '&post_type=' . get_post_type( $post_id ) .
            ( strlen( $url ) > 0 ? '&url=' . rawurlencode( $url ) : '' ) );

    }

    function getPostType($id=''){
        $the_ID = ( strlen( $id ) > 0 ? $id : get_the_ID() );
        $type = get_post_type( $the_ID );
        $text = "";
        switch($type){
            case 'page': $text = "Page";break;
            case 've-popup': $text = "Popup";break;
            case 've-widget': $text = "Widget";break;
            default:$text = "Page";break;
        }
        return $text;
    }

    public function buildEditablePage() {
        //if(!$this->getPost()&&ve_get_param('post_id')){
            //$GLOBALS['post']=get_post(ve_get_param('post_id'));
        //}
        ! defined( 'CONCATENATE_SCRIPTS' ) && define( 'CONCATENATE_SCRIPTS', false );
        add_filter( 'the_content', array( &$this, 'addContentAnchor' ) );
        do_action( 've_iframe' );
        show_admin_bar(false);
        add_action( 'wp_enqueue_scripts', array( $this, 'iframeEnqueueScripts' ) );
        if($this->has('page_template')){
            $template=$this->get('page_template');
            add_action('template_redirect',function() use($template){
                $this->viewManager->render($template);
                exit();
            });
        }
    }
    function iframeEnqueueScripts(){
        //wp_enqueue_script('');
        do_action('iframe_enqueue_scripts');
    }
    public function addContentAnchor( $content = '' ) {
        do_shortcode($content);
        return '<span id="ve-editor-holder" style="display:none !important;"></span>'; // . $content;
    }

    function buildEditor(){
        add_action( 'current_screen', array( &$this, 'editorInit' ) );
    }
    function editorInit(){
        do_action('ve_editor_init');
        $this->setupPostToEdit();
        $this->renderEditor();
        die;
    }
    function renderEditor(){

        remove_all_actions( 'admin_notices', 3 );
        remove_all_actions( 'network_admin_notices', 3 );
        add_filter( 'admin_title', array( &$this, 'setEditorTitle' ) );
        if ( ! defined( 'IFRAME_REQUEST' ) ) define( 'IFRAME_REQUEST', true );
        do_action('editor_enqueue_scripts');
        $this->viewManager->render('editor',array('editor'=>$this));

    }
    function setupPostToEdit() {
        $this->post_id = ve_get_param( 'post_id' );
        if($this->post_id == "new")
        {
            $post_type = ve_get_param( 'post_type' ) != null ?ve_get_param( 'post_type' ):'page';
            $this->post_id = wp_insert_post(array(
                'post_title' => 'New '.$post_type,
                'post_type' => $post_type,
                'post_status' => 'draft'
            ));
            update_post_meta($this->post_id,'_use_ve','1');
            wp_redirect(admin_url('edit.php?ve_action=ve_inline&post_id=' . $this->post_id .'&post_type='.$post_type));
            die;
        }
        if(!$this->post_id){
            wp_redirect(admin_url('edit.php'));
            die;
        }

        $this->post = get_post( $this->post_id );
        if(!$this->post){
            wp_redirect(admin_url('edit.php'));
            die;
        }
        $this->post->metas = get_post_meta($this->post_id,'_ve_metas',true);
        $GLOBALS['post'] = $this->post;
        $this->post_type = get_post_type_object( $this->post->post_type );
        $this->post_url = get_permalink( $this->post_id );
        $this->edit_iframe_url = add_query_arg('ve_editable',true,$this->post_url);
        if ( ! current_user_can( 'edit_post', $this->post_id ) ) {header( 'Location: ' . $this->post_url );die;}
    }
    function getPost() {
        !isset($this->post) && $this->setupPostToEdit();
        return $this->post;
    }

    /**
     * a lite info about current post
     */
    function getPostInfo(){
        $post=$this->getPost();
        unset($post->content);
        return $post;
    }
    function getContentToEdit($post=0){
        define('VE_GET_CONTENT_TO_EDIT',true);
        if(!$post) {
            $post = $this->getPost();
        }
        $content = apply_filters( 've_get_content_to_edit', $post->post_content );
        $not_shortcodes = preg_split( '/' . self::getShortCodeRegex() . '/', $content );
        $non_sc_replace=vsprintf('[%1$s][%2$s width="1/1"][%3$s]$1[/%3$s][/%2$s][/%1$s]',$this->base_elements);
        foreach ( $not_shortcodes as $string ) {
            if ( strlen( trim( $string ) ) > 0 ) {
                $content = preg_replace( "/(" . preg_quote( $string, '/' ) . "(?!\[\/))/", $non_sc_replace, $content );
            }
        }
        return $this->do_shortcode($content);
    }
    function get_element_scripts(){
        if($this->element_scripts){
            return $this->element_scripts;
        }
        $this->element_scripts=array();
        foreach($this->post_elements as $element){
            $id_base=$element['id_base'];
            $elementObj=$this->elementManager->getElement($id_base);
            if($elementObj)
                $this->element_scripts[$id_base]=$elementObj->get_inline_script();
        }
        return $this->element_scripts;
    }
    /**
     * Retrieve the shortcode regular expression for searching.
     *
     * The regular expression combines the shortcode tags in the regular expression
     * in a regex class.
     *
     * The regular expression contains 6 different sub matches to help with parsing.
     *
     * 1 - An extra [ to allow for escaping shortcodes with double [[]]
     * 2 - The shortcode name
     * 3 - The shortcode argument list
     * 4 - The self closing /
     * 5 - The content of a shortcode when it wraps some content.
     * 6 - An extra ] to allow for escaping shortcodes with double [[]]
     *
     * @since 2.5.0
     *
     * @uses $shortcode_tags
     *
     * @return string The shortcode search regular expression
     */

    function getShortCodeRegex(){
        $tagnames = $this->elementManager->getElementBaseIds();
        $tagregexp = join( '|', array_map('preg_quote', $tagnames) );

        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return
            '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag ...
            .     '\\]'                          // ... and closing bracket
            . '|'
            .     '\\]'                          // Closing bracket
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .             '[^\\[]*+'             // Not an opening bracket
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .                 '[^\\[]*+'         // Not an opening bracket
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }
    function do_shortcode($content, $is_container = false, $parent_id = false){
        $string = '';
        preg_match_all( '/' . self::getShortCodeRegex() . '/', $content, $found );
        /**
         * @var array $found
         * 1 - An extra [ to allow for escaping shortcodes with double [[]]
         * 2 - The shortcode name
         * 3 - The shortcode argument list
         * 4 - The self closing /
         * 5 - The content of a shortcode when it wraps some content.
         * 6 - An extra ] to allow for escaping shortcodes with double [[]]
         */
        if ( count( $found[2] ) == 0 ) {
            return $is_container && strlen( $content ) > 0 ?
                $this->do_shortcode( "[{$this->base_elements['text']}]" . $content . "[/{$this->base_elements['text']}]", false, $parent_id ) :
                $content;
        }
        foreach ( $found[2] as $index => $s ) {
            $id = md5( time() . '-' . $this->e_index ++ );
            $content = $found[5][$index];
            $elementObj=$this->elementManager->getElement($s);
            $element = array(
                'id_base' => $s,
                'attrs_query' => $found[3][$index],
                'attrs' => shortcode_parse_atts( $found[3][$index] ),
                'id' => $id,
                'parent_id' => $parent_id,
                'is_container' => $elementObj->is_container(),
                'field_key'=>$elementObj->get_field_key(),
            );
            if($elementObj->has_content())
                $element['attrs']['content'] = $content;
            $this->post_elements[] = $element;
            $string .= $this->do_element( $element, $content );
        }
        return $string;
    }
    function do_element($element,$content=''){
        if(!empty($element['shortcode'])){
            $elementObj=$this->elementManager->getElement($element['id_base']);
            $is_container=$elementObj->is_container();
            if ( $is_container ) {
                $element['shortcode'] = preg_replace( '/\]/', "][{$this->base_elements['anchor']}]", $element['shortcode'], 1 );
            }
            $element['shortcode']=stripslashes($element['shortcode']);
            return do_shortcode($this->element_wrap_start($element).$element['shortcode'].$this->element_wrap_end($element));
        }else{
            $is_container = $element['is_container'];
        }
        return do_shortcode($this->element_wrap_start($element)
            . '[' . $element['id_base'] . ' ' . $element['attrs_query'] . ']' .
            ( $is_container ? "[{$this->base_elements['anchor']}]" : '' ) .
            $this->do_shortcode( $content, $is_container, $element['id'] ) .
            '[/' . $element['id_base'] . ']'  . $this->element_wrap_end($element) );

    }
    function element_wrap_start($element){
        return '<div class="ve_element" data-id-base="' . $element['id_base'] . '" data-element-id="' . $element['id'] . '"'  . '>';
    }
    function element_wrap_end(){
        return '</div>';
    }


    function setEditorTitle( $admin_title ) {
        return sprintf( __( 'Edit %s with Visual Editor', 'visual_editor' ), $this->post_type->labels->singular_name );
    }
    function editPostLink( $link,$id=0 ) {
        if ( $this->canUseEditor($id) ) {
            return $this->getEditUrl($id);
        }
        return $link;
    }
    function containerAnchor(){
        return '<span class="ve_container_anchor"></span>';
    }

    function getAllElements($with_custom=true){
        $elements=$this->elementManager->getElements();
        $elements_settings=array();
        foreach($elements as $e){
            $elements_settings[$e->id_base]=$e->get_settings();
        }
        if($with_custom) {
            $custom_element='ve_custom';
            $config=$this->getVeManager()->get('config');
            if(!empty($config['custom_element'])){
                $custom_element=$config['custom_element'];
            }
            $the_custom_element = $elements[$custom_element];
            unset($elements_settings[$custom_element]);
            $custom_elements = $this->getVeManager()->getPostManager()->getElements(array('posts_per_page' => -1));

            if ($the_custom_element instanceof Ve_Element) {
                foreach ($custom_elements as $id => $custom_element) {
                    $settings = $the_custom_element->get_settings();
                    if ($custom_element->post_title) {
                        $settings['name'] = $custom_element->post_title;
                    }
                    if ($custom_element->icon_class) {
                        $settings['icon_class'] = $custom_element->icon_class;
                    }
                    $params = array('content' => base64_encode($custom_element->post_content));
                    $settings['params'] = json_encode($params);
                    $settings['group']='custom';
                    $id_base = $the_custom_element->id_base . '-' . $id;
                    $elements_settings[$id_base] = $settings;
                }
            }
        }
        return $elements_settings;
    }
    function render($tpl,$data=array()){
        $this->viewManager->render($tpl,$data);
    }
    function isPopup($post=''){
        return $this->isPostType('popup',$post);
    }
    function isWidget($post=''){
        return $this->isPostType('widget',$post);
    }
    function isTemplate($post=''){
        return $this->isPostType('template',$post);
    }
    function isPage($post=''){
        return $this->isPostType('page',$post);
    }
    function isElement($post=''){
        return $this->isPostType('element',$post);
    }
    function isPostType($type,$post=''){
        if(!$post){
            $post=$this->getPost();
        }
        return $this->getVeManager()->getPostManager()->isPostType($post,$type);
    }


    function adminFile( $path ) {
        return ABSPATH . 'wp-admin/' . $path;
    }
}
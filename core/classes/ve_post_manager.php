<?php
class VE_Post_Manager extends VE_Manager_Abstract{
    var $post_type_widget='ve-widget';
    var $post_type_popup='ve-popup';
    var $post_type_template='ve-template';
    var $post_type_element='ve-element';
    function bootstrap(){
        add_action('init',array($this,'registerPost'));
        add_action('current_screen',array($this,'screenSetup'));
        add_action('admin_print_scripts',array($this,'printJsVars'));
    }
    function printJsVars(){
        static $printed=false;
        if($printed){
            return ;
        }
        $printed=true;
        ?>
        <script type="text/javascript">
            var ve=ve||{};
            ve.postTypes=<?php echo json_encode($this->getPostTypes());?>
        </script>
        <?php
    }
    function screenSetup(WP_Screen $screen){
        $is_new_post=0===strpos(basename($_SERVER['REQUEST_URI']),'post-new.php');
        if($is_new_post&&$screen->base=='post'&&$screen->action=='add'&&in_array($screen->post_type,$this->getPostTypes(true))){
            $new_post=$this->get_new_post_uri($screen->post_type);
            wp_redirect($new_post);
            die;
        }
    }
    function get_new_post_uri($post_type='post'){
        $new_post=admin_url('edit.php?ve_action=ve_inline&post_id=new');
        $new_post=add_query_arg('post_type',$post_type,$new_post);
        return $new_post;
    }
    function registerPost(){
        $this->registerWidget();
        $this->registerPopup();
        $this->registerTemplate();
        $this->registerCustomElement();
    }
    function registerWidget(){
        $labels=array(
            'name'               => _x( 'Ve Widgets', 'post type general name', 'visual-editor' ),
            'singular_name'      => _x( 'Ve Widget', 'post type singular name', 'visual-editor' ),
            'add_new_item'      => _x( 'Add New Widget', '','visual-editor'),
        );
        $args=array(
            'labels'             => $labels,
            'show_ui'              => true,
            'show_in_menu'         => false,
            'show_in_nav_menus'    => null,
            'show_in_admin_bar'    => null,
            'rewrite'=>false,
            'supports'           => array( 'title', 'editor',),
        );
        if(ve_is_iframe()){
            $args['publicly_queryable']=true;
        }
        register_post_type($this->post_type_widget,$args);

        //add_submenu_page();
    }
    function registerPopup(){
        $labels=array(
            'name'               => _x( 'Ve Popups', 'post type general name', 'visual-editor' ),
            'singular_name'      => _x( 'Ve Popup', 'post type singular name', 'visual-editor' ),
            'add_new_item'      => _x( 'Add New Popup', '','visual-editor'),
        );
        $args=array(
            'labels'             => $labels,
            'show_ui'              => true,
            'show_in_menu'         => false,
            'show_in_nav_menus'    => null,
            'show_in_admin_bar'    => null,
            'rewrite'=>false,
            'supports'           => array( 'title', 'editor',),
        );
        if(ve_is_iframe()){
            $args['publicly_queryable']=true;
        }
        register_post_type($this->post_type_popup,$args);
    }
    function registerTemplate(){
        $labels=array(
            'name'               => _x( 'Ve Templates', 'post type general name', 'visual-editor' ),
            'singular_name'      => _x( 'Ve Template', 'post type singular name', 'visual-editor' ),
        );
        $args=array(
            'labels'             => $labels,
            'show_ui'              => true,
            'show_in_menu'         => false,
            'show_in_nav_menus'    => null,
            'show_in_admin_bar'    => null,
            'rewrite'=>false,
            'supports'           => array( 'title', 'editor',),
        );
        if(ve_is_iframe()){
            $args['publicly_queryable']=true;
        }
        register_post_type($this->post_type_template,$args);
    }
    function registerCustomElement(){
        $labels=array(
            'name'               => _x( 'Ve Custom Elements', 'post type general name', 'visual-editor' ),
            'singular_name'      => _x( 'Ve Custom Element', 'post type singular name', 'visual-editor' ),
        );
        $args=array(
            'labels'             => $labels,
            'show_ui'              => true,
            'show_in_menu'         => false,
            'show_in_nav_menus'    => null,
            'show_in_admin_bar'    => null,
            'rewrite'=>false,
            'supports'           => array( 'title', 'editor',),
        );
        if(ve_is_iframe()){
            $args['publicly_queryable']=true;
        }
        register_post_type($this->post_type_element,$args);
    }
    function postTypesMenu(){
        $postTypes=array($this->post_type_widget,$this->post_type_popup);
        $parent_menu='visual-editor-admin';
        foreach($postTypes as $ptype){
            $ptype_obj = get_post_type_object( $ptype );
            add_submenu_page( $parent_menu, $ptype_obj->labels->name, $ptype_obj->labels->all_items, $ptype_obj->cap->edit_posts, "edit.php?post_type=$ptype" );
            add_submenu_page( $parent_menu, $ptype_obj->labels->add_new, $ptype_obj->labels->add_new_item, $ptype_obj->cap->create_posts, "post-new.php?post_type=$ptype" );
        }

    }
    function getPostTypes($only_ve=false){
        $post_types=array(
            'widget'=>$this->post_type_widget,
            'popup'=>$this->post_type_popup,
            'template'=>$this->post_type_template,
            'element'=>$this->post_type_element,
            );
        if(!$only_ve){
            $post_types['post']='post';
            $post_types['page']='page';
        }
        return $post_types;
    }
    /**
     * @param $args
     * @return WP_Post[]
     */
    function getWidgets($args){
        $args=wp_parse_args($args);
        $args['post_type']=$this->post_type_widget;
        return get_posts($args);
    }
    /**
     * @param $args
     * @return WP_Post[]
     */
    function getPopups($args){
        $args=wp_parse_args($args);
        $args['post_type']=$this->post_type_popup;
        return get_posts($args);
    }
    /**
     * @param $args
     * @return WP_Post[]
     */
    function getTemplates($args){
        $args=wp_parse_args($args);
        $args['post_type']=$this->post_type_template;
        return get_posts($args);
    }

    /**
     * @param $args
     * @return WP_Post[]
     */
    function getElements($args){
        $args=wp_parse_args($args);
        $args['post_type']=$this->post_type_element;
        return get_posts($args);
    }

    function isPostType($post,$type){

        if($post&&$post->post_type){
            $post_type=$post->post_type;
            switch($type){
                case 'widget':
                    return $post_type==$this->post_type_widget;
                break;
                case 'popup':
                    return $post_type==$this->post_type_popup;
                break;
                case 'template':
                    return $post_type==$this->post_type_template;
                break;
                case 'element':
                    return $post_type==$this->post_type_element;
                    break;
                case 'page':
                    return $post_type=='page';
                    break;
            }
        }
        return false;
    }
    function isPopup($post){
        return $this->isPostType($post,'popup');
    }
    function isWidget($post){
        return $this->isPostType($post,'widget');
    }
    function isTemplate($post){
        return $this->isPostType($post,'template');
    }
}
<?php
class VE_Admin extends VE_Manager_Abstract{
    var $license;
    function bootstrap(){
        if(!is_ve()){
            add_action('admin_enqueue_scripts',array($this,'loadScriptsWpAdminOnly'));
        }
        if(is_admin()){
            $this->adminHocks();
        }
        $this->license=$this->getVeManager()->get('license');

    }
    function adminHocks(){
        add_action( 'admin_menu', array($this,'buildAdminMenus') );
        add_action('wp_loaded',array($this,'onWpLoaded'));
        add_action('admin_init',array($this,'update'));
    }
    function update(){
        if(isset($_GET['ve-action'])&&$_GET['ve-action']==='logout'){
            $this->license->clear();
            wp_redirect(remove_query_arg('ve-action'));
            die;
        }
        if(isset($_POST['ve-action'])&&$_POST['ve-action']==='login'){
            $email=isset($_POST['email'])?$_POST['email']:'';
            $receipt=isset($_POST['receipt'])?$_POST['receipt']:'';
            $this->license->check();
            wp_redirect(remove_query_arg('ve-action'));
            die;
        }
    }
    function onWpLoaded(){
        $ve_pages=array('ve-posts','ve-pages');
        if(isset($_GET['page'])){
            if(in_array($_GET['page'],$ve_pages)){
                unset($_GET['post_type']);
                unset($_REQUEST['post_type']);
                unset($_GET['_wp_http_referer']);
                add_filter( 'parse_query', array($this,'posts_filter') );
            }
        }
    }
    function posts_filter( $query ){
        if(is_admin()) {
            $query->query_vars['meta_key'] = '_use_ve';
            $query->query_vars['meta_value'] = '1';
        }

    }
    function loadScriptsWpAdminOnly(){
        wp_enqueue_script('ve-admin-admin',ve_resource_url(VE_VIEW.'/js/admin/admin.js'),array('jQuery'));
    }
    function buildAdminMenus(){
        add_menu_page( __( 'AIO WP Builder', 'visual_editor' ),  __( 'AIO WP Builder', 'visual_editor' ), 'manage_categories', 'visual-editor-admin', array($this,'adminDashboard'));
      
    }
    function adminListPages(){
        $this->_adminListPosts('page');
    }
    function adminListWidgets(){
        $this->_adminListPosts('ve-widget');
    }
    function _adminListPosts($typenow='post'){
        include VE_CORE.'/templates/list-posts.phtml';
    }
    function adminDashboard(){
        $license=$this->license;
        $this->license->check();
        echo '<h1>All In One WP Builder</h1>';
        include VE_CORE.'/templates/login.phtml';
    }
}
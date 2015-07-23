<?php
class VE_Admin extends VE_Manager_Abstract{
    function bootstrap(){
        if(!is_ve()){
            add_action('admin_enqueue_scripts',array($this,'loadScriptsWpAdminOnly'));
        }
        if(is_admin()){
            $this->adminHocks();
        }

    }
    function adminHocks(){
        add_action( 'admin_menu', array($this,'buildAdminMenus') );
        add_action('wp_loaded',array($this,'onWpLoaded'));
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
        wp_enqueue_script('ve-admin-admin',ve_resource_url(VE_VIEW.'/js/admin/admin.js',array('jQuery')));
    }
    function buildAdminMenus(){
        add_menu_page( __( 'AIO WP Builder', 'visual_editor' ),  __( 'AIO WP Builder', 'visual_editor' ), 'manage_categories', 'visual-editor-admin', array($this,'adminDashboard'));
        add_submenu_page( 'visual-editor-admin', __( 'Create Page', 'visual_editor' ), __( 'Create Page', 'visual_editor' ), 'manage_categories', 'edit.php?ve_action=ve_inline&post_type=page&post_id=new', null);

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
        ?>
        <h2>Thanks for using AIO WP Builder</h2>
        <p>We hope you enjoy the plugin.</p>
        <p>If you need assistance or have suggestions, please let us know at luisrojo.ai@gmail.com</p>

        <?php
    }
}
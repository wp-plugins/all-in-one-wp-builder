<?php
class VE_Layout_Manager extends VE_Manager_Abstract{
    var $useVeTemplate=false;
    function _construct(){

    }
    function bootstrap(){
        add_filter('page_template',array($this,'pageTemplate'));
        add_action('wp_head',array($this,'wpHead'),2);
        add_action('wp_head',array($this,'removeAutoP'));
        add_action('wp',array($this,'templateCheck'));
        if ( ! function_exists( '_wp_render_title_tag' ) ) {
            add_action('wp_head',array($this,'wpTitle'),2);

        }

    }
    function removeAutoP(){
        if($this->isUseVe())
            remove_filter('the_content', 'wpautop');
    }
    function isUseVe(){
        $post=get_post();
        if($post) {
            $post_id = $post->ID;
            $use_ve = get_post_meta($post_id, '_use_ve', true);
            if ($use_ve == '1') {
                return true;
            }
        }
        return false;
    }
    function templateCheck(){
        $this->pageTemplate('');
    }
    function pageTemplate($template){
        if($this->isUseVe()) {
            $_template = get_page_template_slug();
            if ($_template === "") {
                $_template = "full-width.php";
            }
            if ($_template != basename($template)) {
                $ve_template = VE_PAGE_TEMPLATE_DIR . '/' . $_template;
                if (file_exists($ve_template)) {
                    $this->useVeTemplate = true;
                    return $ve_template;
                }
            }
        }
        return $template;
    }
    function wpHead(){
        global $wp_styles,$wp_scripts;
        /**
         * @var $wp_styles WP_Styles
         * @var $wp_scripts WP_Scripts
         */

        if($this->useVeTemplate) {
            $theme_style = array();
            foreach ($wp_styles->registered as $handle => $style) {
                if (strpos($style->src, get_stylesheet_directory_uri()) !== false) {
                    $theme_style[] = $handle;
                }

            }
            $wp_styles->dequeue($theme_style);
            $theme_scripts = array();
            foreach ($wp_scripts->registered as $handle => $script) {
                if (strpos($script->src, get_stylesheet_directory_uri()) !== false) {
                    $theme_scripts[] = $handle;
                }
            }
            $wp_scripts->dequeue($theme_scripts);
        }

    }

    function wpTitle()
    {
       echo "<title>".wp_title()."</title>";

    }

}
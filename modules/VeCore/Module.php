<?php
class VeModule_VeCore{
    /**
     * @var VE_Module_Manager
     */
    var $moduleManager;
    /**
     * @param VE_Module_Manager $moduleManager
     */

    function bootstrap($moduleManager){//this will run after all module loaded
        //die('boot strap');
        $this->moduleManager=$moduleManager;
        add_action('init',array($this,'init'));
        $this->additionalField();
        add_action('ve_element_init',array($this,'element_init'));
        add_action('editor_enqueue_scripts',array($this,'localize_script'));
    }
    function additionalField(){
        require dirname(__FILE__).'/src/Fields/wp_widget_additional_fields.php';
        new Wp_Widget_Additional_Fields();
        require dirname(__FILE__).'/src/Fields/align.php';
        new VE_Core_Field_Align();
    }
    function localize_script(){
        wp_localize_script( 've_js-ve_define', 'i18nLocale', array(

            'add_image' => __( 'Add Image', 'visual_editor' ),
            'add_images' => __( 'Add Images', 'visual_editor' ),

        ) );
    }
    function init(){
        $this->registerCss();
        $this->registerJs();
        /*add_action('wp_enqueue_scripts',function(){
            $this->registerCss();
            $this->registerJs();
        });
        */



    }
    function registerCss(){
        wp_register_style( 'flexslider', ve_resource_url( dirname(__FILE__).'/view/lib/flexslider/flexslider.css' ), false, VE_VERSION, 'screen' );
        wp_register_style( 'nivo-slider-css', ve_resource_url( dirname(__FILE__).'/view/lib/nivoslider/nivo-slider.css' ), false, VE_VERSION, 'screen' );
        wp_register_style( 'nivo-slider-theme', ve_resource_url( dirname(__FILE__).'/view/lib/nivoslider/themes/default/default.css' ), array( 'nivo-slider-css' ), VE_VERSION, 'screen' );
        wp_register_style( 'prettyphoto', ve_resource_url( dirname(__FILE__).'/view/lib/prettyphoto/css/prettyPhoto.css' ), false, VE_VERSION, 'screen' );
        wp_register_style( 'isotope-css', ve_resource_url( dirname(__FILE__).'/view/css/lib/isotope.css' ), false, VE_VERSION, 'all' );

    }
    function registerJs(){
        wp_register_script( 'jquery_ui_tabs_rotate', ve_resource_url( dirname(__FILE__).'/view/lib/jquery-ui-tabs-rotate/jquery-ui-tabs-rotate.js' ), array( 'jquery', 'jquery-ui-tabs' ), VE_VERSION, true );

        wp_register_script( 'tweet', ve_resource_url( dirname(__FILE__).'/view/lib/jquery.tweet/jquery.tweet.js' ), array( 'jquery' ), VE_VERSION, true );
        wp_register_script( 'isotope', ve_resource_url( dirname(__FILE__).'/view/lib/isotope/dist/isotope.pkgd.min.js' ), array( 'jquery' ), VE_VERSION, true );
        wp_register_script( 'jcarousellite', ve_resource_url( dirname(__FILE__).'/view/lib/jcarousellite/jcarousellite_1.0.1.min.js' ), array( 'jquery' ), VE_VERSION, true );

        wp_register_script( 'nivo-slider', ve_resource_url( dirname(__FILE__).'/view/lib/nivoslider/jquery.nivo.slider.pack.js' ), array( 'jquery' ), VE_VERSION, true );
        wp_register_script( 'flexslider', ve_resource_url( dirname(__FILE__).'/view/lib/flexslider/jquery.flexslider-min.js' ), array( 'jquery' ), VE_VERSION, true );
        wp_register_script( 'prettyphoto', ve_resource_url( dirname(__FILE__).'/view/lib/prettyphoto/js/jquery.prettyPhoto.js' ), array( 'jquery' ), VE_VERSION, true );
        wp_register_script( 'waypoints', ve_resource_url( dirname(__FILE__).'/view/lib/jquery-waypoints/waypoints.min.js' ), array( 'jquery' ), VE_VERSION, true );
    }

    function element_init(Ve_Element $element){
        $element->enqueue_form_script('all',ve_resource_url(dirname(__FILE__).'/view/js/all.js'));
        $element->support('CssAdvanced');
    }
    function test(){

    }
}
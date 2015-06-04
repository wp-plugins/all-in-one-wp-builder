<?php
class VE_Resource_Manager extends VE_Manager_Abstract{
    /**
     * @var array js css for front and frame
     */
    var $css=array();
    var $js=array();
    /**
     * @var array for admin editor only
     */
    var $editorCss=array();
    var $editorJs=array();
    /**
     * @var array for iframe only
     */
    var $iframeCss=array();
    var $iframeJs=array();
    /**
     * @var array for front only
     */
    var $frontCss=array();
    var $frontJs=array();
    var $js_handles=array();
    var $css_handles=array();
    function bootstrap(){
        $this->loadResources();
    }
    function loadResources(){
        $config=$this->getVeManager()->get('config');
        $resources=$config['resources'];
        if($resources){
            foreach($resources as $resource){

                !empty($resource['css'])&&$this->css=array_merge($this->css,$resource['css']);
                !empty($resource['js'])&&$this->js=array_merge($this->js,$resource['js']);
                !empty($resource['eCss'])&&$this->editorCss=array_merge($this->editorCss,$resource['eCss']);
                !empty($resource['eJs'])&&$this->editorJs=array_merge($this->editorJs,$resource['eJs']);
                !empty($resource['fCss'])&&$this->iframeCss=array_merge($this->iframeCss,$resource['fCss']);
                !empty($resource['fJs'])&&$this->iframeJs=array_merge($this->iframeJs,$resource['fJs']);
                !empty($resource['frontCss'])&&$this->frontCss=array_merge($this->frontCss,$resource['frontCss']);
                !empty($resource['frontJs'])&&$this->frontJs=array_merge($this->frontJs,$resource['frontJs']);
            }
        }
        add_action('wp_enqueue_scripts',array($this,'enqueueCssJs'));
        if(!ve_is_iframe()) {
            //die('front js');
            add_action('wp_enqueue_scripts', array($this, 'enqueueFrontCssJs'));
        }
        add_action('editor_enqueue_scripts',array($this,'enqueueEditorCssJs'));
        add_action('iframe_enqueue_scripts',array($this,'enqueueFrameCssJs'));

    }
    function addResource($file){

        $file_name=basename($file);

        $file_extension=pathinfo($file_name,PATHINFO_EXTENSION);
        if($file_extension=='css'){
            $this->css[]=array($file_name,$file);
        }
        if($file_extension=='js'){
            $this->js[]=array($file_name,$file);
        }
        return $this;
    }
    function addCss($handle, $src = false, $deps = array(), $ver = false, $media = 'all'){
        $this->css[]=func_get_args();
        return $this;
    }
    function addJs($handle, $src = false, $deps = array(), $ver = false, $in_footer = false){
        $this->js[]=func_get_args();
        return $this;
    }
    function addEditorCss($handle, $src = false, $deps = array(), $ver = false, $media = 'all'){
        $this->editorCss[]=func_get_args();
        return $this;
    }
    function addEditorJs($handle, $src = false, $deps = array(), $ver = false, $in_footer = false){
        $this->editorJs[]=func_get_args();
        return $this;
    }

    function addFrameCss($handle, $src = false, $deps = array(), $ver = false, $media = 'all'){
        $this->iframeCss[]=func_get_args();
        return $this;
    }
    function addFrameJs($handle, $src = false, $deps = array(), $ver = false, $in_footer = false){
        $this->iframeJs[]=func_get_args();
        return $this;
    }
    function enqueueFrontCssJs(){
        $this->enqueue_css($this->frontCss);
        $this->enqueue_js($this->frontJs);
    }
    function enqueueEditorCssJs(){
        $this->enqueue_css($this->editorCss);
        $this->enqueue_js($this->editorJs);
    }
    function enqueueFrameCssJs(){
        $this->enqueue_css($this->iframeCss);
        $this->enqueue_js($this->iframeJs);
    }
    /**
     * @use WP_Scripts $wp_scripts
     */
    function enqueueCssJs(){
        wp_enqueue_script(false);
        wp_enqueue_style(false);
        $this->enqueue_css($this->css);
        $this->enqueue_js($this->js);


    }
    function enqueue_css($css_queue){
        global $wp_styles;
        !is_array($css_queue)&&$css_queue=array();
        foreach($css_queue as $css){
            @list($handle, $src , $deps , $ver , $media )=$css;
            $old_handle=$handle;
            if(!$wp_styles->query($handle)) {
                $handle = str_replace('ve_css-', '', $handle);
                $handle = 've_css-' . $handle;
            }
            $this->css_handles[$old_handle]=$handle;
            isset($src)||$src=false;
            isset($deps)||$deps=array();
            isset($ver)||$ver=false;
            isset($media)||$media='all';
            if(strpos($src,'://')===false){
                if(file_exists($src)){
                    $src=ve_resource_url($src);
                }else{
                    $src=false;
                }
            }
            if($deps&&!is_array($deps)){
                $deps=(array)$deps;
            }
            $deps=array_map(array($this,'css_dep_map'),$deps);
            wp_enqueue_style($handle, $src , $deps , $ver , $media);
        }
    }
    function enqueue_js($js_queue){
        global $wp_scripts;
        !is_array($js_queue)&&$js_queue=array();
        foreach($js_queue as $js){
            @list($handle, $src , $deps , $ver, $in_footer)=$js;
            $old_handle=$handle;
            if(!$wp_scripts->query($handle)) {
                $handle = str_replace('ve_js-', '', $handle);
                $handle = 've_js-' . $handle;
            }
            $this->js_handles[$old_handle]=$handle;
            isset($src)||$src=false;
            isset($deps)||$deps=array();
            isset($ver)||$ver=false;
            isset($in_footer)||$in_footer=false;
            if(strpos($src,'://')===false){
                if(file_exists($src)){
                    $src=ve_resource_url($src);
                }else{
                    $src=false;
                }
            }
            if($deps&&!is_array($deps)){
                $deps=(array)$deps;
            }
            if(!$deps){
                $deps=array();
            }
            $deps=array_map(array($this,'js_dep_map'),$deps);
            wp_enqueue_script($handle, $src , $deps , $ver, $in_footer);
        }
    }
    function js_dep_map($handle){
        if(isset($this->js_handles[$handle])){
            return $this->js_handles[$handle];
        }
        return $handle;
    }
    function css_dep_map($handle){
        if(isset($this->$this->css_handles[$handle])){
            return $this->css_handles[$handle];
        }
        return $handle;

    }
}
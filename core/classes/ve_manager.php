<?php
class VE_Manager extends VE_Manager_Abstract{
    /**
     * @var VE_Loader
     */
    var $loader;
    var $factories;
    function __construct($loader){
        parent::__construct();
        $this->loader=$loader;
    }
    function load(){
        $this->set('ModuleManager',new VE_Module_Manager($this));
        $this->set('ResourceManager',new VE_Resource_Manager($this));
        $this->set('LayoutManager',new VE_Layout_Manager($this));
        $this->set('ViewManager',new VE_View_Manager($this));
        $this->set('FeatureManager',new VE_Feature_Manager($this));
        $this->set('ElementManager',new VE_Element_Manager($this));
        $this->set('PostManager',new VE_Post_Manager($this));
        $this->set('FontManager',new VE_Font_Manager($this));
        $this->set('Editor',new VE_Editor($this));
        $this->set('Controller', new VE_Controller($this));
        $this->set('PopupManager', new VE_Popup_Manager($this));
        $this->set('WidgetManager', new VE_Widget_Manager($this));
        $this->set('ShortCode', new VE_ShortCode($this));
        $this->set('Admin', new VE_Admin($this));
        $this->set('ListTable', new VE_List_Table_Manager($this));
        foreach($this->factories as $name=>$class){
            $this->_load($class,$name);
        }
        do_action('ve_load',$this);
    }
    function bootstrap(){
        //load core modules and user module
        $this->getModuleManager()->bootstrap($this);
        $this->getResourceManager()->bootstrap($this);
        $this->getLayoutManager()->bootstrap($this);
        $this->getViewManager()->bootstrap($this);
        $this->get('FeatureManager')->bootstrap($this);
        $this->get('ElementManager')->bootstrap($this);
        $this->get('PostManager')->bootstrap($this);
        $this->get('FontManager')->bootstrap($this);
        $this->getEditor()->bootstrap($this);
        $this->get('Controller')->bootstrap($this);
        $this->get('PopupManager')->bootstrap($this);
        $this->get('WidgetManager')->bootstrap($this);
        $this->get('ShortCode')->bootstrap($this);
        $this->get('Admin')->bootstrap($this);
        $this->get('ListTable')->bootstrap($this);
        //Module bootstrap
        foreach($this->factories as $name=>$class){
            $this->_bootstrap($name);
        }
        do_action('ve_bootstrap',$this);
        //var_dump($this);
    }
    function _load($class,$name=''){
        if(!class_exists($class)){
            return false;
        }
        if(!$name){
            $name=$class;
        }
        $this->set($name,new $class($this));
    }
    function _bootstrap($name){
        if($this->has($name)){
            $this->get($name)->bootstrap($this);
        }
    }
    function factory($class,$name=''){
        if(!$name){
            $name=$class;
        }
        $this->factories[$name]=$class;
        return $this;
    }
    function run($config=array()){
        $this->setConfig($config);
        $this->setMode();
        //Fire factory action before load and bootstrap
        do_action('ve_factory',$this);
        $this->load();
        $this->bootstrap();

        /**
         * Everything done, fire init hook
         */
        do_action('ve_init',$this);
        /**
         * Let configure editor
         */
        $this->configure_editor();
        do_action('ve_run');
    }
    function configure_editor(){
        $this->get('Editor')->configure();
    }
    function setConfig($config){
        if($config&&!is_array($config)){
            if(file_exists($config)){
                $config=include($config);
            }
        }
        $this->set('config',$config);
        return $this;
    }
    protected function setMode() {
        if ( is_admin() ) {
            if ( ve_action() === 've_inline' ) {
                $this->set('mode', 'front_editor');
            }else{
                $this->set('mode','admin_page');
            }
        } else {
            if ( isset( $_GET['ve_editable'] ) && $_GET['ve_editable']) {
                $this->set('mode', 've_iframe');
            } else {
                $this->set('mode','page');
            }
        }
    }
    function getMode(){
        return $this->get('mode');
    }

    /**
     * @return VE_Loader
     */
    function getLoader(){
        return $this->loader;
    }

    /**
     * @return VE_Module_Manager
     *
     */
    function getModuleManager(){
        return $this->get('ModuleManager');
    }

    /**
     * @return VE_Element_Manager
     * @throws Exception
     */
    function getElementManager(){
        return $this->get('ElementManager');
    }
    /**
     * @return VE_Resource_Manager
     */
    function getResourceManager(){
        return $this->get('ResourceManager');
    }

    /**
     * @return VE_View_Manager
     *
     */
    function getViewManager(){
        return $this->get('ViewManager');
    }
    /**
     * @return VE_Layout_Manager
     */
    function getLayoutManager(){
        return $this->get('LayoutManager');
    }

    /**
     * @return VE_Editor
     */
    function getEditor(){
        return $this->get('Editor');
    }

    /**
     * @return VE_Post_Manager;
     */
    function getPostManager(){
        return $this->get('PostManager');
    }

    /**
     * @return VE_Popup_Manager;
     */
    function getPopupManager(){
        return $this->get('PopupManager');
    }

    /**
     * @return VE_List_Table_Manager
     */
    function getListTableManager(){
        return $this->get('ListTable');
    }

    /**
     * @return VE_License
     */
    function getLicenseManager(){
        return $this->get('license');
    }

}
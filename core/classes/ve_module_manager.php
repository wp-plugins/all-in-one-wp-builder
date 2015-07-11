<?php
class VE_Module_Manager extends VE_Manager_Abstract{
    var $loadedModules;
    var $module_config;
    /**
     * @var VE_Loader
     */
    var $loader;
    /**
     * @var string module config directory
     */
    CONST CONFIG_DIR='config';
    const SRC_DIR='src';
    const VIEW_DIR='view';
    const CONFIG_FILE='module.config.php';
    const MODULE_FILE='Module.php';
    const CLASS_PREFIX='VeModule_';
    const ELEMENTS_DIR='Elements';
    const FEATURES_DIR='Features';

    function _construct(){
        $this->module_config=array();
        $this->loadedModules=array();
        $this->loader=$this->getVeManager()->getLoader();
    }
    function bootstrap(){

        $this->loadModules();
        add_action('after_setup_theme',array($this,'loadModulesElements'),100);

    }
    /**
     * Load all modules
     * @throws Exception
     */
    function loadModules(){
        $config=$this->getVeManager()->get('config');
        $modules=$config['modules'];
        $this->loadModulesConfig($modules);
        foreach($modules as $module){
            $this->loadModule($module);
        }
        add_action('ve_bootstrap',array($this,'moduleBootstrap'));
        //$this->moduleBootstrap();
    }
    function loadModulesElements(){
        foreach(array_keys($this->loadedModules) as $module){
            $this->loadElements($module);
            $this->loadFeatures($module);
        }
        do_action('ve_after_load_elements');
    }

    /**
     * load modules configs and merge to global config
     * @param $modules array
     * @return boolean true
     */
    function loadModulesConfig($modules){
        $modulesConfig=array();
        foreach($modules as $module){
            $module_dir=VE_MODULE.'/'.$module;
            $config_dir=$module_dir.'/'.self::CONFIG_DIR;
            $config_file=$config_dir.'/'.self::CONFIG_FILE;
            if(file_exists($config_file)) {
                $config=include $config_file;
                $modulesConfig =array_replace_recursive($modulesConfig, $config);
            }
        }
        $config=$this->getVeManager()->get('config');
        $this->getVeManager()->set('config',array_replace_recursive($config,$modulesConfig));
        return true;
    }
    /**
     * Load one module with name
     * @param $name
     */
    function loadModule($name){
        $this->loadedModules[$name]=true;
        $module_dir=VE_MODULE.'/'.$name;
        $src_dir=$module_dir.'/'.self::SRC_DIR;
        $module_file=$module_dir.'/Module.php';
        $src_file=$src_dir.'/'.$name.'.php';
        if(file_exists($module_file)){
            include $module_file;
        }
        if(file_exists($src_file)) {
            require $src_file;
        }
    }
    function loadElements($module){
        $config=$this->getVeManager()->get('config');
        $elements=$config['elements'][$module];
        $elements_dir=VE_MODULE.'/'.$module.'/'.self::SRC_DIR.'/'.self::ELEMENTS_DIR;
        foreach($elements as $element){
            $element_file=$elements_dir.'/'.$element.'.php';
            $this->loader->load_file($element_file);
        }
    }
    function loadFeatures($module){
        $config=$this->getVeManager()->get('config');
        if(!empty($config['features'][$module])) {
            $features = $config['features'][$module];
            $features_dir = VE_MODULE . '/' . $module . '/' . self::SRC_DIR . '/' . self::FEATURES_DIR;
            foreach ($features as $element) {
                $feature_file = $features_dir . '/' . $element . '.php';
                $this->loader->load_file($feature_file);
            }
        }
    }
    function moduleBootstrap(){
        $allModules=array_keys($this->loadedModules);
        foreach($allModules as $module){
            $module_class=$this->getClassName($module);
            if(!$this->has($module_class)){
                if(class_exists($module_class)){
                    $this->set($module_class,new $module_class);
                }
            }
            if($this->has($module_class)){
                $this->get($module_class)->bootstrap($this,$module);
            }

        }

    }
    function getClassName($module){
        return self::CLASS_PREFIX.$module;
    }
    function getModule($name){
        $module_class=$this->getClassName($name);
        if($this->has($module_class)){
            return $this->get($module_class);
        }
        return false;
    }
}
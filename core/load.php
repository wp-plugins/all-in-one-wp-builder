<?php
class VE_Loader{
    var $loadedFiles;
    function __construct(){
        $this->loadedFiles=array();
    }
    function init(){
        $this->load_dir(VE_CORE.'/bases');
        $this->load_dir(VE_CORE.'/classes');
        $this->load_dir(VE_CORE.'/helpers');
        $this->load_dir(VE_VIEW.'/mce-plugins');
        do_action('ve_loader_init',$this);
        return $this;
    }

    /**
     * Get main application manager
     * @return VE_Manager
     */
    function ve_manager(){
        global $ve_manager;
        if(!isset($ve_manager)||!$ve_manager instanceof VE_Manager){
            $ve_manager=new VE_Manager($this);
        }
        return $ve_manager;
    }
    function load_file($file){
        if(is_array($file)){
            foreach($file as $f){
                $this->load_file($f);
            }
        }else {
            if (file_exists($file) && !$this->is_loaded($file)) {
                include $file;
            }else{
                die('failed to load'.$file);
            }
        }
    }
    function is_loaded($file){
        if(in_array($file,$this->loadedFiles)){
            return true;
        }
        return false;
    }
    function load_dir($dir,$file_mark='*.php'){
        $pattern=rtrim($dir,'\/').'/'.$file_mark;
        $files=glob($pattern);
        foreach($files as $file){
            $this->load_file($file);
        }
    }
}
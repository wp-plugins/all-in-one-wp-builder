<?php
class VE_Manager_Abstract{
    var $components;
    var $veManager;
    function __construct($the_manager=null){
        $this->components=array();
        $this->setVeManager($the_manager);
        $this->_construct();
    }
    function _construct(){

    }

    function bootstrap(){

    }
    function set($name,$com){
        if($name&&$com){
            $name=strtolower($name);
            $this->components[$name]=$com;
        }
    }

    /**
     * @param $name
     * @return VE_Manager_Abstract|mixed
     * @throws Exception
     */
    function get($name){
        if(!$this->has($name)){
            throw new Exception(sprintf(
                '%s was unable to fetch or create an instance for %s',
                get_class($this) . '::' . __FUNCTION__,
                $name
            ));
        }
        return $this->components[strtolower($name)];
    }
    function has($name){
        return isset($this->components[strtolower($name)]);
    }

    /**
     * @return VE_Manager
     *
     */
    function getVeManager(){
        if($this->veManager){
            return $this->veManager;
        }
        if($this instanceof VE_Manager){
            return $this;
        }
    }
    function setVeManager($veManager){
        $this->veManager=$veManager;
        return $this;
    }
}
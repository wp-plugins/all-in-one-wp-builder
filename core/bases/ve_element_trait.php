<?php
trait VE_Element_Trait{

    /**
     * @var VE_Manager
     */
    var $__Ve_Manager;
    /**
     * @var VE_Feature_Manager
     */
    var $__Ve_Feature_Manager;
    /**
     * @var VE_Element_Manager
     */

    var $__Ve_Element_Manager=null;
    /**
     * @var string module name
     */
    var $__Ve_Element_Module;

    /**
     * @param VE_Element_Manager $elementManager
     * @return $this
     */
    function setElementManager(VE_Element_Manager $elementManager){
        $this->__Ve_Element_Manager=$elementManager;
        $this->__Ve_Manager=$elementManager->getVeManager();
        $this->__Ve_Feature_Manager=$this->__Ve_Manager->get('FeatureManager');
        return $this;
    }

    /**
     * @return VE_Element_Manager
     */
    function getElementManager(){
        return $this->__Ve_Element_Manager;
    }

    /**
     * @return VE_Manager
     */
    function getVeManager(){
        return $this->__Ve_Manager;
    }

    /**
     * @return VE_Feature_Manager
     */
    function getFeatureManager(){
        return $this->__Ve_Feature_Manager;
    }
    function setVeModule($module){
        $this->__Ve_Element_Module=$module;
        return $this;
    }
    function getVeModule(){
        return $this->__Ve_Element_Module;
    }
}
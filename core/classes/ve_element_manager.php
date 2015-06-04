<?php
class VE_Element_Manager extends VE_Manager_Abstract{
    var $elements=array();
    function bootstrap(){
        //We will load elements at init so we can use more wp features eg Widgets
        add_action('init',array($this,'loadElements'),1);
    }
    function loadElements(){
        do_action('ve_load_elements');
        $elements=array();
        $config=$this->getVeManager()->get('config');
        if(isset($config['elements'])){
            $elements=$config['elements'];
        }
        foreach($elements as $module=>$_elements){
            foreach($_elements as $element){
                $ElementClassName=$module.'_'.$element;
                if(class_exists($ElementClassName)){
                    $this->addElement($ElementClassName,$module);
                }
            }
        }
        do_action('ve_elements_init',$this);
        $this->addShortCodes();
    }
    function addElement($elementClass,$module){
        $element=new $elementClass($this->getVeManager());
        if($element instanceof Ve_Element){
            $element->setElementManager($this);
            $element->setVeModule($module);
            if($element->id_base) {
                $this->elements[$element->id_base] = $element;
            }
        }
        return $this;
    }

    /**
     * @param $id_base
     * @return VE_Element|bool
     */
    function getElement($id_base){
        return $this->getElements($id_base);
    }

    /**
     * @param null $element_id_base
     * @return VE_Element[]|bool
     */
    function getElements($element_id_base=null){
        if($element_id_base===null){
            return $this->elements;
        }
        if(isset($this->elements[$element_id_base])){
            return $this->elements[$element_id_base];
        }
        return false;
    }
    function getElementBaseIds(){
        return array_keys($this->elements);
    }
    function addShortCodes(){
        foreach($this->elements as $element) {
            if($element instanceof Ve_Element) {
                add_shortcode($element->id_base, $element->_get_display_callback());
            }
        }
        do_action('ve_shortcodes_init',$this);
    }


    /**
     * add Form shortcode for element
     * @param $element
     * @return bool true if successful added form shortcode
     */
    function addFormShortCode($element){
        $element=$this->getElement($element);
        if($element){
            add_shortcode($element->id_base,$element->_get_form_callback());
            return true;
        }
        return false;

    }
}
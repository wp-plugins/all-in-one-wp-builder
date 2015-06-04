<?php
class VE_Feature_Manager extends VE_Manager_Abstract{
    var $features=array();
    function bootstrap(){
        /**
         * Load element features before element construct so it can use all features
         */
        add_action('ve_load_elements',array($this,'loadFeatures'));
    }
    function loadFeatures(){
        $features=array();
        $config=$this->getVeManager()->get('config');
        if(isset($config['features'])){
            $features=$config['features'];
        }

        foreach($features as $module=>$_features){
            foreach($_features as $feature){
                $FeatureClassName=$module.'_'.$feature;
                if(class_exists($FeatureClassName)){
                    $this->register($FeatureClassName,$feature,$module);
                }
            }
        }
        do_action('ve_features_init',$this);

    }

    /**
     * just register feature class name, instance will be init each special element
     * @param $featureClassName
     * @param $feature
     * @param $module
     */
    function register($featureClassName,$feature,$module){
        $this->features[$feature]=$featureClassName;
    }

    /**
     * @param $feature
     * @param Ve_Element $element
     * @return Ve_Feature_Abstract | boolean
     */
    function feature($feature,Ve_Element $element){
        if($feature=$this->getFeature($feature)){
            $feature=new $feature($element);
            if($feature instanceof Ve_Feature_Abstract){
                $feature->setElement($element);
                return $feature;
            }
        }
        return false;
    }
    function getFeature($feature){
        if(isset($this->features[$feature])){
            return $this->features[$feature];
        }
        return false;
    }

}
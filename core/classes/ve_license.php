<?php
class VE_License extends VE_Manager_Abstract{
    var $api='http://allinonewpbuilder.com/services/check.php';
    var $license_key='_ve_license';
    var $data=array();
    static function factory(VE_Manager $ve){
        $ve->factory('VE_License','license');
    }
    function check(){
        $license_info=array('email'=>"free@free.com",'key'=>"asdfasdf",'type'=>"free");
             $this->update($license_info);
    }

    /**
     * get license information
     */
    function get_license(){
        if(empty($this->data)) {
            $license = get_option($this->license_key);
            $license = $this->validate($license);
            $this->data = $license;
        }
        return $this->data;
    }
    function getEmail(){
        if($license=$this->get_license()){
            return $license['email'];
        }
        return false;
    }
    function getKey(){
        if($license=$this->get_license()){
            return $license['key'];
        }
        return false;
    }
    function getType(){
        if($license=$this->get_license()){
            return $license['type'];
        }
        return false;
    }
    function isPro(){
        return $this->getType()=='pro';
    }
    function isUltimate(){
        return $this->getType()=='ultimate';
    }
    function isLicensed(){
        return $this->isPro()||$this->isUltimate() || $this->isFree();
    }
    function isFree(){
        return !$this->isPro()||!$this->isUltimate();
    }
    function isInvalid(){
        return !$this->get_license();
    }
    function validate($license){
        if(!is_array($license)||empty($license)||empty($license['email'])||empty($license['key'])||empty($license['type'])){
            return false;
        }
        if($this->get_the_hash()===$this->generate_hash($license)){
            return $license;
        }
        return false;
    }
    function get_the_hash(){
        return get_option('_ve_key');
    }
    function update($license){
        update_option($this->license_key,$license);
        $hash=$this->generate_hash($license);
        update_option('_ve_key',$hash);
    }
    function clear(){
        update_option($this->license_key,'');
        update_option('_ve_key','');
    }
    function generate_hash($license,$host=''){
        if(empty($host)){
            $host=$_SERVER['HTTP_HOST'];
        }
        $license_str=join('|',$license).'|'.$host;
        $hash=md5($license_str);
        return $hash;
    }
    function sanitize($license){

    }
    function bootstrap(){
        //echo $this->check('walter.w@gmail.com','WDWMVEVL');
        //die;
    }
}
add_action('ve_factory','VE_License::factory');
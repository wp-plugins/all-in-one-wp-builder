<?php
class VE_View_Manager extends VE_Manager_Abstract{

    var $templateMap=array();
    var $templateBaseDir;
    var $templateExt;
    var $allowExt=array('phtml','tpl');
    var $data=array();
    function bootstrap(){
        $config=$this->getVeManager()->get('config');
        $viewManagerConfig=$config['view_manager'];
        $this->templateMap=$viewManagerConfig['template_map'];
        $this->templateBaseDir=rtrim($viewManagerConfig['template_base_dir'],'/');
        $this->templateExt=$viewManagerConfig['template_ext'];

    }
    function setTemplate($template){
        $this->set('template',$template);
        return $this;
    }
    function getTemplate(){
        if($this->has('template'))
            return $this->get('template');
        return '';
    }
    function resolve($template=null){
        if($template){
            $this->setTemplate($template);
        }
        $template=$this->getTemplate();
        if(!$template){
            return false;
        }
        if(isset($this->templateMap[$template])){
            return realpath($this->templateMap[$template]);
        }
        $templateExt=$this->getDefaultTemplateExt();
        $fileExt=pathinfo($template,PATHINFO_EXTENSION);
        if($fileExt) {
            if (!in_array($fileExt, $this->allowExt)) {
                return false;
            }
            //Template already with ext, clear default ext
            $templateExt='';
        }

        $template.=$templateExt;
        $template_file=$this->templateBaseDir.'/'.ltrim($template,'/');
        $template_file=realpath($template_file);
        return $template_file;
    }
    function getDefaultTemplateExt(){
        $templateExt=$this->templateExt;
        if($templateExt&&substr($templateExt,0,1)!=='.'){
            $templateExt='.'.$templateExt;
        }
        return $templateExt;
    }
    function setData($name,$val=''){
        if(is_array($name)){
            $this->data=$name;
        }else{
            $this->data[$name]=$val;
        }
        return $this;
    }
    function getData($var=null){
        if($var===null) {
            return $this->data;
        }
        return $this->data[$var];
    }
    function render($template=null,$data=array(),$echo=true){
        $content='';
        if($template){
            $this->setTemplate($template);
        }
        $___template_file=$this->resolve($template);
        if($___template_file) {
            if ($data&&!$this->getData()) {
                $this->setData($data);
            }
            $content = $this->loadFile($___template_file,$data);
        }
        if($echo){
            echo $content;
        }
        return $content;
    }
    function getHtml($template=null,$data=array()){
        return $this->render($template,$data,false);
    }
    function loadFile($___file,$data=array()){
        if(!$data) {
            $data = $this->getData();
        }
        unset($data['this']);
        extract($data,EXTR_OVERWRITE);
        if($___file){
            ob_start();
            require $___file;
            return ob_get_clean();
        }
        return '';
    }
}
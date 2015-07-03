<?php
class Ve_Feature_Abstract{
    /**
     * @var Ve_Element
     */
    var $_element;

    /**
     * @var string
     */
    var $id;
    /**
     * @var string
     */
    var $title;
    /**
     * @var WP_Scripts
     */
    static $scripts;
    static $init=array();
    var $instance=array();
    var $content='';
    var $args=array();

    /**
     * @param Ve_Element $element
     */
    function setElement(Ve_Element $element){
        $this->_element=$element;
    }

    /**
     * @return Ve_Element
     */
    function getElement(){
        return $this->_element;
    }
    function _get_callback(){
        return array($this,'form_callback');
    }
    function enqueue_script( $handle, $src = false, $deps = array(), $ver = false){
        if(! is_a( self::$scripts, 'WP_Scripts' )){
            self::$scripts= new WP_Scripts();
        }
        if ( $src ) {
            $_handle = explode('?', $handle);
            self::$scripts->add( $_handle[0], $src, $deps, $ver );
        }
        self::$scripts->enqueue( $handle );
        return $this;
    }
    function print_scripts(){
        if(!self::$scripts){
            self::$scripts=new WP_Scripts();
        }
        return self::$scripts->do_items();
    }
    function form_callback($instance,$content='',$args=array()){
        $this->setInstance($instance,$content,$args);
        ob_start();
        $this->form($instance,$content,$args);
        $this->print_scripts();
        return ob_get_clean();
    }
    function update($instance){

    }

    /**
     * output feature form backend
     * @param $instance array of atts;
     */
    function form($instance){

    }
    function setInstance($instance,$content,$args){
        $this->instance=$instance;
        $this->content=$content;
        $this->args=$args;
    }

    function getTitle(){
        return $this->title;
    }
    function setTitle($title){
        $this->title=$title;
        return $this;
    }
    function setId($id){
        $id=sanitize_key($id);
        $this->id=$id;
        return $this;
    }
    function getId(){
        if(empty($this->id)){
            $this->id=sanitize_key($this->title);
        }
        return $this->id;
    }
    public function get_field_name($field_name) {
        return $this->getElement()->get_field_name($field_name);
    }
    public function get_field_id( $field_name ) {
        return $this->getElement()->get_field_id($field_name);
    }
    function get_field_value($field_name){
        return isset($this->instance[$field_name])?esc_attr($this->instance[$field_name]):'';
    }
    public function __construct(){
        $this->_construct();
    }
    function _construct(){

    }
    function _init($element){
        $class=get_class($this);
        if(!isset(self::$init[$class])){
            self::$init[$class]=true;
            $this->init_once();
        }
        $this->init($element);
    }
    function init(){

    }
    function init_once(){

    }

}
<?php
class Ve_Element implements VE_Element_Interface{
    use VE_Element_Trait;

    var $name;
    var $lv;

    var $icon_class;
    var $default_icon_class = 'fa fa-cubes';
    /**
     * @var string base id for shortcode
     */
    var $id_base;
    /**
     * @var array element options: title, descriptions,...
     */

    var $options;

    var $wp_widget;
    var $wp_widget_args;
    /**
     * @var Ve_Feature_Abstract[]
     */
    var $features;
    var $primary_feature_title='Setting';

    /**
     * Element inline style
     * @var array
     */
    var $css=array();
    /**
     * Element html id
     * @var array
     */
    var $id=array();
    /**
     * Element html class
     * @var array
     */

    var $classes=array();

    /**
     * Element Default class
     */
    var $default_class=array();
    /**
     * Element html attributes
     * @var array()
     */
    var $attributes=array();
    var $before=array();
    var $after=array();
    /**
     * Element form scripts
     * @var array
     */
    var $scripts;

    var $instance=0;
    var $inlineScript='';
    var $previewEnable=false;

    var $updateStack=array();


    function __construct($id_base='',$name='',$options=array()){
        $this->id_base = empty($id_base) ? str_replace('ve_core_','',strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", get_class($this))))
            : strtolower($id_base);
        $this->id_base = trim($this->id_base,'_');
        $class_name=''.$this->id_base;
        if(empty($name)){
            $name=ucwords(str_replace('_',' ',$this->id_base));
        }
        $this->name=$name;
        $this->options=wp_parse_args( $options);
        if(!empty($this->options['container_element'])){
            $this->options['container']=1;
        }
        $this->default_class[]=$class_name;
        if(isset($this->options['classname'])){
            $this->default_class[]=$this->options['classname'];
            unset($this->options['classname']);
        }
        if(isset($this->options['lv'])){
            $this->_setLv($this->options['lv']);
            unset($this->options['lv']);
        }

        $this->icon_class = $this->default_icon_class;
        if(isset($this->options['icon_class'])){
            $this->icon_class = $this->options['icon_class'];
            unset($this->options['icon_class']);
        }

        if(!isset($this->lv)){
            $this->lv=10;
        }
        add_action('ve_elements_init',array($this,'_init'));
        $this->instance=0;


    }
    function _init(){
        $this->init();
        if(!ve_is_iframe()) {
            add_action('wp_footer', array($this,'add_front_js'));
            add_action('wp_footer', 've_print_front_inline_js',1000);
        }

        do_action('ve_element_init',$this);
    }
    function init(){
        $this->support('CssEditor');
    }
    function support($feature,$title=''){
        if(!$this->supported($feature)) {
            $featureInstance = $this->getFeatureManager()->feature($feature, $this);
            $featureInstance->_init($this);
            $this->addFeature($featureInstance, $feature, $title);
        }
        return $this;
    }
    function supported($feature){
        if($feature instanceof Ve_Feature_Abstract){
            $feature=$feature->getId();
        }
        $feature=sanitize_key($feature);
        return isset($this->features[$feature]);
    }


    /**
     * @param $class
     * @use $wp_widget_factory
     * @return $this
     */
    function setWpWidget($class){
        global $wp_widget_factory;
        if($wp_widget_factory instanceof WP_Widget_Factory){
            if(isset($wp_widget_factory->widgets[$class])){
                $this->wp_widget=$wp_widget_factory->widgets[$class];
                return $this;
            }
        }
        if(is_string($class)){
            $class=new $class();
        }
        if(is_object($class)&&$class instanceof WP_Widget){
            $this->wp_widget=$class;
        }
        return $this;
    }

    /**
     * @return WP_Widget
     */
    function getWpWidget(){
        return $this->wp_widget;
    }
    protected function _setLv($lv){
        $this->lv=intval($lv);
    }

    /**
     * update element before it's display
     * @param $atts
     * @param $content
     */
    public function update( $atts,$content ) {
        $this->_updated=true;
        if($this->features){
            foreach($this->features as $f){
                $f->update($atts,$content);
            }
        }
        do_action('ve_update_element',$this,$atts,$content);
    }



    /**
     * display element
     * @param $instance
     * @param $content
     *
     */
    function element($instance,$content){

        if($widget=$this->getWpWidget()){
            $widget->_set(-1);
            $widget->widget($this->get_widget_args(),$instance);
        }
    }

    function form($instance){
        if($widget=$this->getWpWidget()){
            $widget->_set(-1);
            $widget->form($instance);
        }else {
            echo '<p class="no-options-widget">' . __('There are no options for this element.') . '</p>';
        }
    }
    function addFeature(Ve_Feature_Abstract $instance,$id='',$title=''){
        if(!$id){
            $id=$title;
        }
        if($id){
            $instance->setId($id);
        }
        if($title){
            $instance->setTitle($title);
        }
        $id=$instance->getId();
        if(isset($this->features[$id])){
            return $this;
        }
        if($id) {
            $this->features[$id] = $instance;
        }
        return $this;
    }
    function removeFeature($feature){
        $id=$feature;
        $id=sanitize_key($id);
        if(isset($this->features[$id])){
            unset($this->features[$id]);
        }
        return $this;
    }

    public function display_callback($atts, $content='' ) {

        return $this->display_element($atts,$content);
    }
    function default_settings(){
        $this->addClass($this->default_class);
    }

    function display_element($atts, $content='' ){
        $this->next_instance();
        $this->update($atts,$content);
        ob_start();
        if(ve_element_editing()&&method_exists($this,'preview')){
            $this->preview($atts,$content);
        }else {
            $this->element($atts, $content);
        }
        if(ve_element_editing()){
            $this->print_inline_js();
        }
        $output= ob_get_clean();
        $this->the_instance();
        $output= $this->get_before() .$this->element_wrapper_start($atts,$content).$output.$this->element_wrapper_end($atts,$content).$this->get_after();
        return $output;
    }
    /**
     * Set js code to call when element ready
     * @param String $jsCode
     */
    function ready($jsCode){
        $this->inlineScript=$jsCode;
    }
    function add_front_js(){
        ve_add_front_js_inline($this->inlineScript);
    }
    /**
     * print element inline js
     * @param string $jsCode code to print
     *
     */
    function print_inline_js($jsCode=''){
        if(empty($jsCode)){
            $jsCode=$this->inlineScript;
        }
        if(empty($jsCode)){
            return ;
        }
        ?>
        <script type="text/javascript">
            ve.ready(function($){
                <?php echo $jsCode;?>
            });
        </script>
        <?php
    }

    function get_inline_script(){
        return $this->inlineScript;
    }
    function element_wrapper_start($atts,$content=''){
        return sprintf('<div %s>',$this->get_attributes());
    }
    function element_wrapper_end(){
        return '</div>';
    }

    /**###########################
     * Instance related functions
     *****************************/

    /**
     * Next instance
     */
    function next_instance(){
        static $id=-1;
        $id++;
        $this->instance=$id;
        array_push($this->updateStack,$this->instance);
        $this->default_settings();
    }
    function the_instance(){
        $this->instance=array_pop($this->updateStack);
    }
    function get_attributes($att=null){
        $attributes=array();
        if(!empty($this->attributes[$this->instance])){
            foreach($this->attributes[$this->instance] as $k=>$v){
                $attributes[$k]=$v;
            }
        }

        $output=array();
        if(isset($this->id[$this->instance])){
            $attributes['id']=$this->id;
        }
        if(!isset($attributes['style'])&&isset($this->css[$this->instance])){
            $attributes['style']=$this->css[$this->instance];
        }
        if(!isset($attributes['class'])&&isset($this->classes[$this->instance])){
            $attributes['class']=$this->classes[$this->instance];
        }
        if(isset($attributes['style'])&&is_array($attributes['style'])){
            $attributes['style']=ve_style_string($attributes['style']);
        }
        if(isset($attributes['class'])&&is_array($attributes['class'])){
            $attributes['class']=ve_class_string($attributes['class']);
        }
        $remove_empty_attributes=array('id','class','style');
        if($attributes) {
            foreach ($attributes as $k => $v) {
                if (is_array($v)) {
                    $v = ve_attr_string($v);
                }
                if(in_array($k,$remove_empty_attributes)&&empty($v)){
                    continue;
                }
                if($att){
                    if(is_array($att)){
                        if(in_array($k,$att)){
                            $output[] = sprintf('%s="%s"', $k, esc_attr($v));
                        }
                    }else if($att==$k){
                        $output[] = sprintf('%s="%s"', $k, esc_attr($v));
                        break;
                    }
                }else {
                    $output[] = sprintf('%s="%s"', $k, esc_attr($v));
                }
            }
        }

        $output=join(' ',$output);
        return $output;
    }
    function setId($id){
        $this->id[$this->instance]=$id;
        return $this;
    }
    function addClass($class){
        if(empty($class)){
            return $this;
        }
        if(is_string($class)&&($class=trim($class))&&strpos($class,' ')){
            $class=explode(' ',$class);
        }
        if(!isset($this->classes[$this->instance])||!is_array($this->classes[$this->instance])){
            $this->classes[$this->instance]=array();
        }
        if(is_array($class)){
            $class=array_map('sanitize_html_class',$class);
            $class=array_filter($class);
            $this->classes[$this->instance]=array_merge($this->classes[$this->instance],$class);
            $this->classes[$this->instance]=array_unique($this->classes[$this->instance]);
            return $this;
        }
        $class=sanitize_html_class($class);

        if($class&&!in_array($class,$this->classes[$this->instance])){
            $this->classes[$this->instance][]=$class;
        }
        $this->attributes[$this->instance]['class']=$this->classes[$this->instance];
        return $this;

    }
    function removeClass($class){
        if(empty($class)){
            return $this;
        }
        if(is_string($class)&&strpos($class,' ')){
            $class=explode(' ',$class);
        }

        if(is_array($class)){
            $class=array_map('sanitize_html_class',$class);
            $this->classes[$this->instance]=array_diff($this->classes[$this->instance],$class);
            return $this;
        }
        $class=sanitize_html_class($class);
        if($class) {
            $this->classes[$this->instance] = array_diff($this->classes[$this->instance], array($class));
        }
        return $this;
    }
    function attr($k,$v=null){
        $k=sanitize_key($k);
        if($k){
            unset($this->attributes[$this->instance][$k]);//unset to remove link to previous value if any
            $this->attributes[$this->instance][$k]=$v;
        }
        return $this;
    }
    function removeAttr($k){
        $k=sanitize_key($k);
        if(isset($this->attributes[$this->instance][$k])){
            unset($this->attributes[$this->instance][$k]);
        }
        return $this;
    }
    function css($name,$val='__ve_get_value'){
        if($val==='__ve_get_value'){
            return isset($this->css[$this->instance][$name])?$this->css[$this->instance][$name]:'';
        }
        if($val) {
            $this->css[$this->instance][$name] = $val;
        }else{
            unset($this->css[$this->instance][$name]);
        }
        if(isset($this->css[$this->instance]))
        $this->attributes[$this->instance]['style']=$this->css[$this->instance];
        return $this;
    }
    function get_before(){
        return isset($this->before[$this->instance])?$this->before[$this->instance]:'';
    }
    function get_after(){
        return isset($this->after[$this->instance])?$this->after[$this->instance]:'';
    }
    function before($before=null){
        $this->before[$this->instance]=$before;
        return $this;
    }
    function after($after=null){
        $this->after[$this->instance]=$after;
        return $this;
    }

    function enqueue_js($handle, $src = false, $deps = array('ve_front'), $ver = false, $footer=true){
        $this->getVeManager()->getResourceManager()->addJs($handle,$src,$deps,$ver,$footer);
    }
    function enqueue_form_script( $handle, $src = false, $deps = array(), $ver = false){
        if(! is_a( $this->scripts, 'WP_Scripts' )){
            $this->scripts= new WP_Scripts();
        }
        if ( $src ) {
            $_handle = explode('?', $handle);
            $this->scripts->add( $_handle[0], $src, $deps, $ver );
        }
        $this->scripts->enqueue( $handle );
        return $this;
    }
    function print_form_scripts(){
        if(!$this->scripts){
            $this->scripts=new WP_Scripts();
        }
        return $this->scripts->do_items();
    }
    function _form($atts,$content,$args){
        $this->form($atts,$content,$args);
        do_action('ve_element_form',$this,$atts,$content,$args);
    }
    public function form_callback($atts, $content='') {
        $args=$this->get_widget_args();
        ob_start();
        if(!empty($this->features)){
            include VE_CORE.'/templates/element-form.phtml';
        }else{
            $this->_form($atts,$content,$args);
        }
        $this->print_form_scripts();
        return ob_get_clean();
    }


    function get_shortcode($instance,$content=''){
        $close="[/$this->id_base]";
        $attr=array($this->id_base);
        foreach($instance as $k=>$v){
            $k=sanitize_key($k);
            if(is_array($v)){
                $v=json_encode($v);
            }else {
                $v = esc_attr($v);
            }
            $attr[]=sprintf('%s="%s"',$k,$v);
        }
        $atts=join(' ',$attr);
        $short_code='['.$atts.']';
        if(!empty($content)){
            $short_code.=$content.$close;
        }
        return $short_code;
    }
    function get_shortcode_array(){
        $shortcode=array(
            'tag'=>$this->id_base,
        );
        return $shortcode;
    }
    public function _get_display_callback() {
        return array($this, 'display_callback');
    }

    public function _get_form_callback() {
        return array($this, 'form_callback');
    }

    function element_title($title){
        if(!empty($title)) {
            printf('<h3 class="element-title">%s</h3>', $title);
        }
    }
    function element_content($content){
        printf('<div class="element-content">%s</div>',$content);
    }



    /**
     * Get all element configuration
     * @return array
     */
    function get_settings(){
        $settings=array_merge($this->options,array(
            'id_base'=>$this->id_base,
            'name'=>$this->name,
            'lv'=>intval($this->lv),
            'icon_class' => $this->icon_class
        ));
        if(empty($settings['defaults'])){
            $settings['defaults']=new stdClass();
        }
        return apply_filters('element_settings',$settings);
    }
    function get_field_key(){
        return 've-' . $this->id_base;
    }
    public function get_field_name($field_name) {
        return $this->get_field_key() . '[' . $field_name . ']';
    }
    function field_name($field_name){
        echo $this->get_field_name($field_name);
    }
    public function get_field_id( $field_name ) {
        return $this->get_field_key() . '-' . $field_name;
    }
    function field_id($field_name){
        echo $this->get_field_id($field_name);
    }
    function field_value($instance_or_value,$key=''){
        if(is_array($instance_or_value)){
            $instance_or_value=isset($instance_or_value[$key])?$instance_or_value[$key]:'';
        }
        echo esc_attr($instance_or_value);
    }
    function option($name){
        return isset($this->options[$name])?$this->options[$name]:null;
    }
    function is_container(){
        return (bool)$this->option('container');
    }
    function has_content(){
        return !$this->is_container()&&$this->option('has_content');
    }
    function get_widget_args(){
        $args=array();
        $args['before_widget']=sprintf('<div class="ve-wp-widget">');
        $args['before_title']='<h3>';
        $args['after_title']='</h3>';
        $args['after_widget']='</div>';
        return wp_parse_args($this->wp_widget_args,$args);
    }
    function enablePreview($preview=true){
        $this->previewEnable=$preview;
        return $this;
    }


}
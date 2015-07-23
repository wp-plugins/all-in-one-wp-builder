<?php
class VE_List_Table_Manager extends VE_Manager_Abstract{
    public $classmap;
    function _construct(){
        $this->classmap=array(
            'VE_Post_List_Table'=>'post',
            'VE_PopupOption_List_Table'=>'popup-option',
        );
    }
    function bootstrap(){

    }

    /**
     * @param string $class The type of the list table, which is the class name.
     * @param array $args Optional. Arguments to pass to the class. Accepts 'screen'.
     * @return WP_List_Table
     */
    function getTable($class,$args=array()){
        $core_classes=$this->classmap;
        $key=$class.md5(json_encode($args));
        if($this->has($key)){
            return $this->get($key);
        }
        if ( isset( $core_classes[ $class ] ) ) {
            foreach ( (array) $core_classes[ $class ] as $required )
                require_once( dirname(__FILE__) . '/list-tables/ve-' . $required . '-list-table.php' );

            if ( isset( $args['screen'] ) )
                $args['screen'] = convert_to_screen( $args['screen'] );
            elseif ( isset( $GLOBALS['hook_suffix'] ) )
                $args['screen'] = get_current_screen();
            else
                $args['screen'] = null;

            $this->set($key,new $class( $args ));
            $table = $this->get($key);
            if($table instanceof VE_List_Table){
                $table->setVeManager($this->getVeManager());
            }
            $table->bootstrap();
            return $table;
        }
        return false;
    }
}
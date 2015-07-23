<?php
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
class VE_List_Table extends WP_List_Table{
    protected $_veManager;
    function setVeManager($ve){
        $this->_veManager=$ve;
    }

    /**
     * @return VE_Manager
     */
    function getVeManager(){
        return $this->_veManager;
    }
    function bootstrap(){

    }
}
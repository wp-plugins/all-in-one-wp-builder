<?php
/**
 * WP_Widget_Archives
 */

class VeCore_VeWpArchives extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_wp_archives';
        $name='Archives';
        $options=array(
            'title'=>'Archives',
            'description'=>'Archives description',
            'icon'=>'ve-row.png',
            'container'=>false,
            'has_content'=>false,
            'defaults'=>array(),

        );
        parent::__construct($id_base,$name,$options);
        $this->setWpWidget('WP_Widget_Archives');

    }

}
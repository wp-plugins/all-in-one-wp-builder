<?php
class VeCore_VeWpMeta extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_wp_meta';
        $name='Meta';
        $options=array(
            'title'=>'Meta',
            'description'=>'Meta description',
            'icon'=>'ve-row.png',
            'container'=>false,
            'has_content'=>false,
            'group'=>'wp',
            'defaults'=>array(),

        );
        parent::__construct($id_base,$name,$options);
        $this->setWpWidget('WP_Widget_Meta');

    }

}
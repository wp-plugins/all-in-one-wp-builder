<?php
class VeCore_VeWpPages extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_wp_pages';
        $name='Pages list';
        $options=array(
            'title'=>'WP Page list',
            'description'=>'Button description',
            'icon_class'=>'fa fa-desktop',
            'container'=>false,
            'has_content'=>false,
            'group'=>'wp',
            'defaults'=>array(),

        );
        parent::__construct($id_base,$name,$options);
        $this->setWpWidget('WP_Widget_Pages');

    }

}
<?php
/**
 * WP_Widget_Tag_Cloud
 */
class VeCore_VeWpTagCloud extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_wp_tags';
        $name='Tag Cloud';
        $options=array(
            'title'=>'Tag Cloud',
            'description'=>'Tag Cloud description',
            'icon_class'=>'fa fa-tags',
            'container'=>false,
            'has_content'=>false,
            'defaults'=>array(),

        );
        parent::__construct($id_base,$name,$options);
        $this->setWpWidget('WP_Widget_Tag_Cloud');

    }

}
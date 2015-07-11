<?php
/**
 * WP_Widget_RSS
 */
class VeCore_VeWpRss extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_wp_rss';
        $name='Rss';
        $options=array(
            'title'=>'Rss',
            'description'=>'Rss description',
            'icon_class'=>'fa fa-rss',
            'container'=>false,
            'has_content'=>false,
            'group'=>'wp',
            'defaults'=>array(),

        );
        parent::__construct($id_base,$name,$options);
        $this->setWpWidget('WP_Widget_RSS');

    }

}
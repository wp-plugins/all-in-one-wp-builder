<?php
/**
 * Created by PhpStorm.
 * User: Alt
 * Date: 4/7/2015
 * Time: 8:23 PM
 */
class VeCore_VeWpLinks extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_wp_links';
        $name='Link';
        $options=array(
            'title'=>'Link',
            'description'=>'Link description',
            'icon'=>'ve-row.png',
            'container'=>false,
            'has_content'=>false,
            'group'=>'wp',
            'defaults'=>array(),

        );
        parent::__construct($id_base,$name,$options);
        $this->setWpWidget('WP_Widget_Links');

    }

}
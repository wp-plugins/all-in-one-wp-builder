<?php
/**
 * WP_Widget_Recent_Posts
 */
class VeCore_VeWpRecentPosts extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_wp_recent_posts';
        $name='Recent Posts';
        $options=array(
            'title'=>'Recent Posts',
            'description'=>'Recent Posts description',
            'icon'=>'ve-row.png',
            'container'=>false,
            'has_content'=>false,
            'group'=>'wp',
            'defaults'=>array(),

        );
        parent::__construct($id_base,$name,$options);
        $this->setWpWidget('WP_Widget_Recent_Posts');

    }

}
<?php
/**
 * WP_Widget_Recent_Comments
 */
class VeCore_VeWpRecentComments extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_wp_recent_comments';
        $name='Recent Comments';
        $options=array(
            'title'=>'Recent Comments',
            'description'=>'Recent Comments description',
            'icon'=>'ve-row.png',
            'container'=>false,
            'has_content'=>false,
            'group'=>'wp',
            'defaults'=>array(),

        );
        parent::__construct($id_base,$name,$options);
        $this->setWpWidget('WP_Widget_Recent_Comments');

    }

}
<?php
class VeCore_VeWpCalendar extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_wp_calendar';
        $name='Calendar';
        $options=array(
            'title'=>'Calendar',
            'description'=>'Calendar description',
            'icon_class'=>'fa fa-calendar',
            'container'=>false,
            'has_content'=>false,
            'group'=>'wp',
            'defaults'=>array(),

        );
        parent::__construct($id_base,$name,$options);
        $this->setWpWidget('WP_Widget_Calendar');

    }

}
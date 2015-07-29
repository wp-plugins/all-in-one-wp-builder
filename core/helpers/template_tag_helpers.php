<?php
function ve_get_post_meta($key,$post_id=0){
    if(!$post_id){
        $post_id=get_the_ID();
    }
    $settings=get_post_meta($post_id,'ve_settings',true);
    if(isset($settings[$key])){
        return $settings[$key];
    }
    return false;
}
function ve_animation_select($selected=''){
    $options=array (
        'Attention Seekers' =>
            array (
                'bounce' => 'bounce',
                'flash' => 'flash',
                'pulse' => 'pulse',
                'rubberBand' => 'rubberBand',
                'shake' => 'shake',
                'swing' => 'swing',
                'tada' => 'tada',
                'wobble' => 'wobble',
                'jello' => 'jello',
            ),
        'Bouncing Entrances' =>
            array (
                'bounceIn' => 'bounceIn',
                'bounceInDown' => 'bounceInDown',
                'bounceInLeft' => 'bounceInLeft',
                'bounceInRight' => 'bounceInRight',
                'bounceInUp' => 'bounceInUp',
            ),
        'Bouncing Exits' =>
            array (
                'bounceOut' => 'bounceOut',
                'bounceOutDown' => 'bounceOutDown',
                'bounceOutLeft' => 'bounceOutLeft',
                'bounceOutRight' => 'bounceOutRight',
                'bounceOutUp' => 'bounceOutUp',
            ),
        'Fading Entrances' =>
            array (
                'fadeIn' => 'fadeIn',
                'fadeInDown' => 'fadeInDown',
                'fadeInDownBig' => 'fadeInDownBig',
                'fadeInLeft' => 'fadeInLeft',
                'fadeInLeftBig' => 'fadeInLeftBig',
                'fadeInRight' => 'fadeInRight',
                'fadeInRightBig' => 'fadeInRightBig',
                'fadeInUp' => 'fadeInUp',
                'fadeInUpBig' => 'fadeInUpBig',
            ),
        'Fading Exits' =>
            array (
                'fadeOut' => 'fadeOut',
                'fadeOutDown' => 'fadeOutDown',
                'fadeOutDownBig' => 'fadeOutDownBig',
                'fadeOutLeft' => 'fadeOutLeft',
                'fadeOutLeftBig' => 'fadeOutLeftBig',
                'fadeOutRight' => 'fadeOutRight',
                'fadeOutRightBig' => 'fadeOutRightBig',
                'fadeOutUp' => 'fadeOutUp',
                'fadeOutUpBig' => 'fadeOutUpBig',
            ),
        'Flippers' =>
            array (
                'flip' => 'flip',
                'flipInX' => 'flipInX',
                'flipInY' => 'flipInY',
                'flipOutX' => 'flipOutX',
                'flipOutY' => 'flipOutY',
            ),
        'Lightspeed' =>
            array (
                'lightSpeedIn' => 'lightSpeedIn',
                'lightSpeedOut' => 'lightSpeedOut',
            ),
        'Rotating Entrances' =>
            array (
                'rotateIn' => 'rotateIn',
                'rotateInDownLeft' => 'rotateInDownLeft',
                'rotateInDownRight' => 'rotateInDownRight',
                'rotateInUpLeft' => 'rotateInUpLeft',
                'rotateInUpRight' => 'rotateInUpRight',
            ),
        'Rotating Exits' =>
            array (
                'rotateOut' => 'rotateOut',
                'rotateOutDownLeft' => 'rotateOutDownLeft',
                'rotateOutDownRight' => 'rotateOutDownRight',
                'rotateOutUpLeft' => 'rotateOutUpLeft',
                'rotateOutUpRight' => 'rotateOutUpRight',
            ),
        'Sliding Entrances' =>
            array (
                'slideInUp' => 'slideInUp',
                'slideInDown' => 'slideInDown',
                'slideInLeft' => 'slideInLeft',
                'slideInRight' => 'slideInRight',
            ),
        'Sliding Exits' =>
            array (
                'slideOutUp' => 'slideOutUp',
                'slideOutDown' => 'slideOutDown',
                'slideOutLeft' => 'slideOutLeft',
                'slideOutRight' => 'slideOutRight',
            ),
        'Zoom Entrances' =>
            array (
                'zoomIn' => 'zoomIn',
                'zoomInDown' => 'zoomInDown',
                'zoomInLeft' => 'zoomInLeft',
                'zoomInRight' => 'zoomInRight',
                'zoomInUp' => 'zoomInUp',
            ),
        'Zoom Exits' =>
            array (
                'zoomOut' => 'zoomOut',
                'zoomOutDown' => 'zoomOutDown',
                'zoomOutLeft' => 'zoomOutLeft',
                'zoomOutRight' => 'zoomOutRight',
                'zoomOutUp' => 'zoomOutUp',
            ),
        'Specials' =>
            array (
                'hinge' => 'hinge',
                'rollIn' => 'rollIn',
                'rollOut' => 'rollOut',
            ),
    );
    foreach($options as $optGroup=>$option){
        printf('<optgroup label="%s">',$optGroup);
        foreach($option as $value=>$text) {
            printf('<option value="%s"%s>%s</option>', $value, selected($selected, $value, false), $text);
        }
        echo '</optgroup>';
    }
}
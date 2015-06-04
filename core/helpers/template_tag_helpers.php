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
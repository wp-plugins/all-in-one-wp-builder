<?php
class VE_Widget_Manager extends VE_Manager_Abstract{
    var $post_type_widget;
    var $allWidgets;
    var $injected=array();
    function bootstrap(VE_Manager $ve=null){
        $this->post_type_widget=$ve->getPostManager()->post_type_widget;
        add_filter('the_content',array($this,'injectToContent'));
    }
    function injectToContent($content){
        if((!is_page() && !is_single()) ||is_admin()){
            return $content;
        }
        $post=get_post();
        if($this->injected($post)){
            return $content;
        }
        $widgets=$this->get_widgets($post);

        foreach($widgets as $widget){
            $this->add_wrapper($widget);
            switch($widget->position){
                case 'top':
                    $content=$widget->post_content.$content;
                    break;
                case 'bottom':
                    $content=$content.$widget->post_content;
                    break;
                case 'after':
                    $index=$widget->widget_after;
                    $content=$this->insert_after_paragraph($content,$widget->post_content,$index);


            }
        }
        return $content;
    }
    function add_wrapper($widget){
        $width=ve_get_post_meta('screen_size',$widget->ID);
        if($widget->width){
            $width=$widget->width;
        }
        $height=0;
        if($widget->height){
            $height=$widget->height;
        }
        $alignment=$widget->alignment;
        $styles=array();
        if($width){
            $width = intval($width) - 82;
            $styles[]=sprintf('max-width: %dpx;',$width);
        }
        if($height){
            $styles[]=sprintf('max-height: %dpx;',$height);
        }
        $classes=array();
        if($alignment){
            $classes[]='ve_widget_'.$alignment;
        }
        $style=implode('',$styles);
        $class=implode(' ',$classes);
        if($style){
            $style=sprintf(' style="%s"',$style);
        }
        if($class){
            $class=sprintf(' class="%s"',$class);
        }
        $widget->post_content=sprintf('<div%s%s>%s</div>',$class,$style,$widget->post_content);
        return $widget;
    }
    function insert_after_paragraph($content,$content_to_insert,$index=1){
        $closing_p = '</p>';
        $paragraphs = explode( $closing_p, $content );
        foreach ($paragraphs as $x => $paragraph) {
            if ( trim( $paragraph ) ) {
                $paragraphs[$x] .= $closing_p;
            }
            if ( $index == $x + 1 ) {
                $paragraphs[$x] .= $content_to_insert;
            }
        }
        return implode( '', $paragraphs );
    }
    function injected($post){
        if(empty($post)){
            return true;
        }
        $injected=in_array($post->ID,$this->injected);
        $this->injected[]=$post->ID;
        return $injected;
    }

    /**
     * @param WP_Post $post
     * @return WP_Post[]
     */
    function get_widgets($post){
        if(!$this->allWidgets) {
            $this->allWidgets = get_posts(array(
                'post_type' => $this->post_type_widget,
                'posts_per_page' => -1,
            ));
        }
        $widgets=array();
        $post_type=$post->post_type;
        if(!in_array($post_type,array('post','page'))){
            return $widgets;
        }

        $categories=get_the_category($post->ID);
        $category_ids=array_map(function($cat){return $cat->cat_ID;},$categories);
        foreach ($this->allWidgets as $widget) {
            if($widget->placement==$post_type){//widget for all post
                $widgets[]=$widget;
            }
            if($widget->placement=='category'){//widget for post category
                if(array_intersect($category_ids,$widget->widget_category)){
                    $widgets[]=$widget;
                }
            }
        }

        return $widgets;

    }
}
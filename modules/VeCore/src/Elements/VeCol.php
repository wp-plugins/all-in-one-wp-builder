<?php
class VeCore_VeCol extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_col';
        $name='Column';
        $options=array(
            'title'=>'VE Column',
            'description'=>'Row description',
            'icon_class'=>"fa fa-columns",
            'container'=>true,
            'container_element'=>true,//can contain element directly
            'lv'=>2,
        );
        parent::__construct($id_base,$name,$options);
    }
    function init(){
        $this->support('CssEditor');
    }
    function element($instance,$content=''){
        $width=isset($instance['width'])?$instance['width']:'1/1';
        $offset = isset($instance['offset'])?$instance['offset']:'0/12';
        $font_color=isset($instance['font_color'])?$instance['font_color']:'';
        $this->css('color',$font_color);
        $this->addClass($this->get_width_class($width));
        if($offset) {
            if($this->get_width_class($offset,'')){
                $this->addClass($this->get_width_class($offset, 've-col-md-offset-'));
            }
        }
        if(isset($instance['class'])){
            $this->addClass($instance['class']);
        }
        echo do_shortcode($content);
    }
    function get_width_class($width,$prefix = 've-col-sm-'){
        if ( preg_match( '/^(\d{1,2})\/12$/', $width, $match ) ) {
            $w = $prefix.$match[1];
        } else {
            $w = $prefix;
            switch ( $width ) {
                case "1/6" :
                    $w .= '2';
                    break;
                case "1/4" :
                    $w .= '3';
                    break;
                case "1/3" :
                    $w .= '4';
                    break;
                case "1/2" :
                    $w .= '6';
                    break;
                case "2/3" :
                    $w .= '8';
                    break;
                case "3/4" :
                    $w .= '9';
                    break;
                case "5/6" :
                    $w .= '10';
                    break;
                case "1/1" :
                    $w .= '12';
                    break;

                default :
                    $w = $width;
            }
        }
        return $w;
    }
    function form($instance,$content=''){
        $instance=shortcode_atts(array(
            'font_color'=>'',
            'class'=>'',
            'width'=>'12/12',
            'offset' => '0/12'
        ),$instance);
        $font_color=$instance['font_color'];
        $class=esc_attr($instance['class']);

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('font_color');?>">Font Color</label><br/>
            <input class="ve_color-control" name="<?php echo $this->get_field_name('font_color');?>" id="<?php echo $this->get_field_id('font_color');?>" value="<?php echo $font_color;?>"/>
        </p>
        <p><label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('Extra class:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo $class; ?>" /></p>
        <p>
            <label>Width:</label>
            <select name="<?php echo $this->get_field_name('width');?>">
                <?php for($i=1;$i<=12;$i++){
                    $selected = "";
                    if(($instance['width']) == "$i/12")
                    {
                        $selected = "selected='selected'";
                    }
                    printf('<option value="%s/12" %s>%s/12</option>',$i,$selected,$i);
                }?>
            </select>
        </p>
        <p>
            <label>Offset:</label>
            <select name="<?php echo $this->get_field_name('offset');?>">
                <?php for($i=0;$i<=12;$i++){
                    $selected = "";
                    if(($instance['offset']) == "$i/12")
                    {
                        $selected = "selected='selected'";
                    }
                    printf('<option value="%s/12" %s>%s/12</option>',$i,$selected,$i);
                }?>
            </select>
        </p>
        <?php
    }
}
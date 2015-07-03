<?php
class VeCore_VeTextWithHeader extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_twh';
        $name='Text With Header';
        $options=array(
            'title'=>'Text With Header',
            'description'=>'Row description',
            'icon'=>'ve-row.png',
            'container'=>false,
            'has_content'=>true,
            'defaults'=>array(
                'header'=>'Header',
                'content'=>'This is a text block'
            ),

        );
        parent::__construct($id_base,$name,$options);
    }
    function element($instance,$content=''){
        $heading=$instance['header'];
        $this->element_title($heading);
        $this->element_content(do_shortcode($content));

    }
    function form($instance,$content=''){
        $header=$instance['header'];
        $instance=wp_parse_args($instance,array('class'=>''));

        ?>

        <p>
            <label for="<?php echo $this->get_field_id('header');?>">Header</label>
            <input type="text" name="<?php echo $this->get_field_name('header');?>" id="<?php echo $this->get_field_id('header');?>" value="<?php echo $header;?>"/>
        </p>
        <?php wp_editor($content,$this->get_field_id('content'),array(
            'textarea_name'=>$this->get_field_name('content'),
            'editor_class'=>'ve-html-editor',
            'textarea_rows'=>5,
        ));
        ?>
    <?php
    }
}
<?php
class VeCore_VeCustom extends Ve_Element implements VE_Element_Interface{
    function __construct(){
        $id_base='ve_custom';
        $name='Custom';
        $options=array(
            'title'=>'Text Block',
            'description'=>'Row description',
            'icon_class'=>'fa fa-text-width',
            'container'=>false,
            'has_content'=>true,
            'defaults'=>array('content'=>'this is a text block'),

        );
        parent::__construct($id_base,$name,$options);
        $this->primary_feature_title=false;
    }

    function element($instance,$content=''){
        $content=base64_decode($content);
        //echo $content;
        echo do_shortcode($content);
    }
    function form($instance,$content=''){
        ?>
        <input type="hidden" name="<?php $this->field_name('content');?>" value="<?php echo $content;?>"/>
    <?php
    }
}
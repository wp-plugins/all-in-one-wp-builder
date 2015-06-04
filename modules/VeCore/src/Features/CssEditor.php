<?php
class VeCore_CssEditor extends Ve_Feature_Abstract{
    protected $js_script_appended = false;
    protected $layers = array( 'margin', 'border', 'padding', 'content' );
    protected $positions = array( 'top', 'right', 'bottom', 'left' );


    function _construct(){
        $this->setTitle('Style');
        //die('hehe');
    }
    function update($instance){
        //$this->getElement()->css('background','red');
        if($instance) {
            //print_r($instance);
            foreach ($instance as $style_name => $val) {
                if(!$this->is_valid_style($style_name)){
                    continue;
                }
                $style_name = str_replace('_', '-', $style_name);
                if($style_name=='background-image'){
                    if(is_numeric($val)){
                        list($val,$width,$height)=wp_get_attachment_image_src($val);
                    }
                    if($val) {
                        $val = sprintf('url(%s)', $val);
                    }
                }
                if($style_name=='background-style'){
                    switch($val) {
                        case 'cover':
                        $style_name = 'background-size';
                        //$val = 'coverage';
                        break;
                        case 'contain':
                            $style_name = 'background-size';
                            break;
                        case 'no-repeat':
                            $style_name='background-repeat';

                            break;
                        case 'repeat':
                            $style_name='background-repeat';
                            break;
                    }

                }
                if(is_numeric($val)){
                    $val.='px';
                }
                $this->getElement()->css($style_name, $val);
            }
        }
    }
    function is_valid_style($name){
        foreach(array('margin','padding','border','background') as $style){
            if(strpos($name,$style)!==false){
                return true;
            }
        }
        return false;
    }

    function init_once(){
        $resource=$this->getElement()->getVeManager()->getResourceManager();
        $resource->addEditorCss('wp-color-picker')
            ->addEditorJs('wp-color-picker');
        $this->enqueue_script('css-editor',ve_resource_url( __DIR__.'/../../view/js/css_editor.js' ));
    }


    // }}
    function form($instance) {
        $output = '<div class="ve_css-editor ve_row" data-css-editor="true">';
        $output .= $this->onionLayout();
        $output .= '<div class="ve_col-xs-5 ve_settings">'
            . '    <label>' . __( 'Border', 'visual_editor' ) . '</label> '
            . '    <div class="color-group"><input type="text" name="border_color" value="'.$this->get_field_value('border_color').'" class="ve_color-control"></div>'
            . '    <div class="ve_border-style"><select name="border_style" class="ve_border-style">' . $this->getBorderStyleOptions() . '</select></div>'
            . '    <label>' . __( 'Background', 'visual_editor' ) . '</label>'
            . '    <div class="color-group"><input type="text" name="background_color" value="'.$this->get_field_value('background_color').'" class="ve_color-control"></div>'
            . '    <div class="ve_background-image">' . $this->getBackgroundImageControl() . '<div class="ve_clearfix"></div></div>'
            . '    <div class="ve_background-style"><select name="background_style" class="ve_background-style">' . $this->getBackgroundStyleOptions() . '</select></div>'
            . '    <label>' . __( 'Box controls', 'visual_editor' ) . '</label>'
            . '    <label class="ve_checkbox" style="display:none;"><input type="checkbox" name="simply" class="ve_simplify" value=""> ' . __( 'Simplify controls', 'visual_editor' ) . '</label>'
            . '</div>';
        $output .= '';
        $output .= '</div><div class="ve_clearfix"></div>';
        $output .= '';
        
        echo apply_filters( 've_css_editor', $output );
    }

    function getBackgroundImageControl() {
        $img_ids=$this->get_field_value('background_image');
        ob_start();
        ?>
        <div class="edit_form_line">
            <input type="hidden" class="gallery_widget_attached_images_ids" name="<?php echo $this->get_field_name('background_image');?>" value="<?php echo $img_ids;?>"/>
            <div class="gallery_widget_attached_images">
                <ul class="gallery_widget_attached_images_list">
                    <?php echo fieldAttachedImages(explode(',',$img_ids));?>
                </ul>
            </div>
            <a class="gallery_widget_add_images" href="#" title="Add image">Add image</a>
        </div>
        <?php
        return ob_get_clean();
    }

    function getBorderStyleOptions() {
        $output = '<option value="">' . __( 'Theme defaults', 'visual_editor' ) . '</option>';
        $styles = array( 'solid', 'dotted', 'dashed', 'none', 'hidden', 'double', 'groove', 'ridge', 'inset', 'outset', 'initial', 'inherit' );
        foreach ( $styles as $style ) {
            $output .= '<option value="' . $style . '"'.selected($style,$this->get_field_value('border_style'),false).'>' . __( ucfirst( $style ), 'visual_editor' ) . '</option>';
        }
        return $output;
    }

    function getBackgroundStyleOptions() {
        $output = '<option value="">' . __( 'Theme defaults', 'visual_editor' ) . '</option>';
        $styles = array(
            __( "Cover", 'visual_editor' ) => 'cover',
            __( 'Contain', 'visual_editor' ) => 'contain',
            __( 'No Repeat', 'visual_editor' ) => 'no-repeat',
            __( 'Repeat', 'visual_editor' ) => 'repeat'
        );
        foreach ( $styles as $name => $style ) {
            $output .= '<option value="' . $style . '"'.selected($style,$this->get_field_value('background_style')).'>' . $name . '</option>';
        }
        return $output;
    }

    function onionLayout() {
        $output = '<div class="ve_layout-onion ve_col-xs-7">'
            . '    <div class="ve_margin">' . $this->layerControls( 'margin' )
            . '      <div class="ve_border">' . $this->layerControls( 'border', 'width' )
            . '          <div class="ve_padding">' . $this->layerControls( 'padding' )
            . '              <div class="ve_content"><i></i></div>'
            . '          </div>'
            . '      </div>'
            . '    </div>'
            . '</div>';
        return $output;
    }

    protected function layerControls( $name, $prefix = '' ) {
        $output = '<label>' . __( $name, 'visual_editor' ) . '</label>';

        foreach ( $this->positions as $pos ) {
            $field_name=$name . '_' . $pos . ( $prefix != '' ? '_' . $prefix : '' );
            $output .= '<input type="text" name="' . $this->get_field_name($field_name) . '" data-name="' . $name . ( $prefix != '' ? '-' . $prefix : '' ) . '-' . $pos . '" class="ve_' . $pos . '" placeholder="-" data-attribute="' . $name . '" value="'.$this->get_field_value($field_name).'">';
        }
        return $output;
    }
}
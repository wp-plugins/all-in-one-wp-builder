<?php
class VE_Font_Manager extends VE_Manager_Abstract{
    public $fontDataFile;
    function _construct(){
        $this->fontDataFile=VE_CORE.'/data/allfonts.json';
    }

    function bootstrap(){
        $this->handleAdminUpdate();
        $this->init();
        $this->setup();
    }
    function init(){
        if(false!==$fonts=get_option('ve_selected_fonts')){
            $this->addFonts($fonts);
        }else{
            $this->addFonts(array(
                'Open Sans',
                'Josefin Slab',
                'Arvo',
                'Lato',
                'Vollkorn',
                'Abril Fatface',
                'Ubuntu',
                'Old Standard TT',
                'Droid Sans',
                'Anivers',
                'Junction',
                'Fertigo',
                'Aller',
                'Audimat',
                'Delicious',
                'Prociono',
                'Fontin',
                'Fontin-Sans',
                'Chunkfive',
            ));
        }
    }
    function handleAdminUpdate(){
        if(isset($_POST['ve-action'])&&$_POST['ve-action']=='update-fonts'&&isset($_POST['ve-fonts'])){
            $fonts=$_POST['ve-fonts'];
            update_option('ve_selected_fonts',$fonts);
        }
    }
    function adminPage(){
        $availableFonts=$this->getAvailableFonts();
        ?>
        <div class="wrap">
        <h2>Fonts</h2>
        <form method="post" action="">
            <input type="hidden" name="ve-action" value="update-fonts"/>
            <p>Select fonts:</p>
            <?php foreach($availableFonts as $font){?>
                <label style="width: 19%;display: inline-block"><input type="checkbox" name="ve-fonts[]" value="<?php echo $font?>"<?php checked(in_array($font,$this->getFonts()));?>/> <?php echo $font;?></label>
            <?php }?>
            <p class="submit">
                <input id="submit" class="button button-primary" type="submit" value="Save Changes" name="submit">
            </p>
        </form>
        </div>

        <?php
    }


    function setup(){
        add_action('wp_enqueue_scripts', array($this,'enqueueFonts'));
        add_action('admin_enqueue_scripts', array($this,'enqueueFonts'));
        $this->setupEditor();
    }
    /**
     * Enqueue style for font
     */
    function enqueueFonts(){
        wp_enqueue_style('ve-google-fonts',$this->getFontStyleLink(),false,VE_VERSION);
    }
    function setupEditor(){
        add_action('init',array($this,'enqueueEditorFonts'));
        add_action('tiny_mce_before_init',array($this,'configureFontEditor'));
        add_filter( 'mce_buttons_2', array($this,'editorFontButtons') );
    }
    function editorFontButtons($buttons){
        array_unshift( $buttons, 'fontselect' ); // Add Font Select
        array_unshift( $buttons, 'fontsizeselect' ); // Add Font Size Select
        return $buttons;
    }
    function configureFontEditor($init){
        //get option settings
        $customFont='';
        $saveWebFonts=true;

        $fonts=$this->getFonts();
        //get the google font list
        if($fonts) {
            //load fonts for use in plugin - modified by Richard Bonk
            foreach($fonts as $value) {
                $customFont .=sprintf('%s=%s;', $value, $value);
            }
        }
        //check if websafe fonts are to be loaded
        if($saveWebFonts) {
            //get websafefont list
            $safeFontList = 'Arial=Arial,Helvetica,sans-serif;Arial Black=Arial Black,Gadget,sans-serif;Comic Sans=Comic Sans MS,Comic Sans MS,cursive;Courier New=Courier New,Courier New,Courier,monospace;Georgia=Georgia,Georgia,serif;';
            $safeFontList .= 'Impact=Impact,Charcoal,sans-serif;Lucida Console=Lucida Console,Monaco,monospace;Lucida Sans Unicode=Lucida Sans Unicode,Lucida Grande,sans-serif;Palatino Linotype=Palatino Linotype,Book Antiqua,Palatino,serif;';
            $safeFontList .= 'Tahoma=Tahoma,Geneva,sans-serif;Times New Roman=Times New Roman,Times,serif;Trebuchet MS=Trebuchet MS,Helvetica,sans-serif;Verdana=Verdana,Geneva,sans-serif;Gill Sans=Gill Sans,Geneva,sans-serif;';
            $customFont=$customFont.$safeFontList;
        }
        // modified by Richard Bonk
        $init['font_formats'] = rtrim($customFont,';');
        return $init;

    }
    function enqueueEditorFonts(){
        add_editor_style($this->getFontStyleLink());
    }
    function getFontStyleLink(){
        return '//fonts.googleapis.com/css?family='.$this->getFontList();
    }
    function getFontList(){
        $fonts=$this->getFonts();
        $font=implode('|',$fonts);
        $font=str_replace(' ','+',$font);
        return $font;
    }
    function getFonts(){
        if($this->has('fonts')){
            return $this->get('fonts');
        }
        return array();
    }
    function addFonts($font){
        $fonts=$this->getFonts();
        if(is_array($font)){
            foreach($font as $the_font){
                if(!in_array($the_font,$fonts)){
                    $fonts[]=$the_font;
                }
            }
        }elseif(is_string($font)&&$font){
            if(!in_array($font,$fonts)){
                $fonts[]=$font;
            }
        }
        $this->set('fonts',$fonts);
        return $this;
    }
    function getAvailableFonts(){
        $fonts=$this->getFontsData();
        $fontNames=array();
        if($fonts&&!empty($fonts->items)){
            foreach($fonts->items as $font){
                if($font&&$font->family){
                    $fontNames[]=$font->family;
                }
            }
        }
        return $fontNames;
    }
    function getFontsData(){
        if($this->fontDataFile){
            return json_decode(file_get_contents($this->fontDataFile));
        }
        return false;
    }
}
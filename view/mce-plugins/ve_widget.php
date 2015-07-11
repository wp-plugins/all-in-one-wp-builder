<?php

class VE_Widget_ShortCode{
	/**
	 * $shortcode_tag 
	 * holds the name of the shortcode tag
	 * @var string
	 */
	public $shortcode_tag = 've_widget';
    /**
     * @var Ve_Manager
     */
    public $veManager;

	/**
	 * __construct 
	 * class constructor will set the needed filter and action hooks
	 * 
	 * @param Ve_Manager $ve
	 */
	function __construct($ve){

		$this->veManager=$ve;
		if ( is_admin() ){
			add_action('admin_head', array( $this, 'admin_head') );
			add_action( 'admin_enqueue_scripts', array($this , 'admin_enqueue_scripts' ) );
		}
	}



	/**
	 * admin_head
	 * calls your functions into the correct filters
	 * @return void
	 */
	function admin_head() {
		// check user permissions
		if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
			return;
		}
		
		// check if WYSIWYG is enabled
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', array( $this ,'mce_external_plugins' ) );
			add_filter( 'mce_buttons', array($this, 'mce_buttons' ) );
            add_action('in_admin_footer', array($this,'mce_add_editor_values'));
		}
	}

	/**
	 * mce_external_plugins 
	 * Adds our tinymce plugin
	 * @param  array $plugin_array 
	 * @return array
	 */
	function mce_external_plugins( $plugin_array ) {
		$plugin_array[$this->shortcode_tag] = plugins_url( 'js/ve-widget-button.js' , __FILE__ );
		return $plugin_array;
	}

	/**
	 * mce_buttons 
	 * Adds our tinymce button
	 * @param  array $buttons 
	 * @return array
	 */
	function mce_buttons( $buttons ) {
		array_push( $buttons, $this->shortcode_tag );
		return $buttons;
	}

	/**
	 * admin_enqueue_scripts 
	 * Used to enqueue custom styles
	 * @return void
	 */
	function admin_enqueue_scripts(){
		 wp_enqueue_style('ve_widget_shortcode', plugins_url( 'css/ve-widget-button.css' , __FILE__ ) );
	}

    function mce_add_editor_values()
    {
        $ve_widgets = get_posts(array(
            'post_type' => 've-widget',
            'posts_per_page' => -1,
            'meta_key' => '_use_ve',
            'meta_value' => '1',
            'post_status' => array('publish')));
        $data = array();
        foreach($ve_widgets as $w)
        {
            $data[] = array('text' => $w->post_title, 'value' => $w->ID);
        }
        ?>
        <script>
            var ve_widgets = <?php echo json_encode($data);?>;
        </script>
        <?php
    }
}//end class
function _init_ve_widget($veManager){
    new VE_Widget_ShortCode($veManager);
}
add_action('ve_init','_init_ve_widget');

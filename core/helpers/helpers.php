<?php
function ve_resource_url($file){
    $file=realpath($file);
    return plugins_url(basename($file),$file);
}
function ve_action(){
    return isset($_GET['ve_action'])?$_GET['ve_action']:'';
}
if ( ! function_exists( 've_post_param' ) ) {
    /**
     * Get param value from $_POST if exists.
     *
     * @param $param
     * @param $default
     * @return null|string - null for undefined param.
     */
    function ve_post_param( $param, $default = null ) {
        return isset( $_POST[$param] ) ? $_POST[$param] : $default;
    }
}
if ( ! function_exists( 've_get_param' ) ) {
    /**
     * Get param value from $_GET if exists.
     *
     * @param $param
     * @param $default
     * @return null|string - null for undefined param.
     */
    function ve_get_param( $param , $default = null) {
        return isset( $_GET[$param] ) ? $_GET[$param] : $default;
    }
}
/**
 * @return VE_Manager
 */
function ve_manager(){
    global $ve_manager;
    return $ve_manager;
}
function ve_mode(){
    return ve_manager()->getMode();
}
function ve_is_editor(){
    return ve_mode()=='admin_frontend_editor';
}
function ve_is_iframe(){
    return ve_mode()=='ve_iframe';
}
function ve_scandir( $path, $extensions = null, $depth = 0, $relative_path = '' ) {
    if ( ! is_dir( $path ) )
        return false;

    if ( $extensions ) {
        $extensions = (array) $extensions;
        $_extensions = implode( '|', $extensions );
    }

    $relative_path = trailingslashit( $relative_path );
    if ( '/' == $relative_path )
        $relative_path = '';

    $results = scandir( $path );
    $files = array();

    foreach ( $results as $result ) {
        if ( '.' == $result[0] )
            continue;
        if ( is_dir( $path . '/' . $result ) ) {
            if ( ! $depth || 'CVS' == $result )
                continue;
            $found = ve_scandir( $path . '/' . $result, $extensions, $depth - 1 , $relative_path . $result );
            $files = array_merge_recursive( $files, $found );
        } elseif ( ! $extensions || preg_match( '~\.(' . $_extensions . ')$~', $result ) ) {
            $files[ $relative_path . $result ] = $path . '/' . $result;
        }
    }

    return $files;
}
function ve_get_page_templates($post=null){
    if(wp_cache_get('page_templates','ve')){
        return wp_cache_get('page_templates','ve');
    }
    $templates=wp_get_theme()->get_page_templates( $post );
    $files = (array) ve_scandir(VE_PAGE_TEMPLATE_DIR, 'php' );
    $page_templates = array();

    foreach ( $files as $file => $full_path ) {
        if ( ! preg_match( '|Template Name:(.*)$|mi', file_get_contents( $full_path ), $header ) )
            continue;
        $page_templates[ $file ] = _cleanup_header_comment( $header[1] );
    }
    $page_templates=array_merge($page_templates,$templates);
    $page_templates = array_flip($page_templates);
    wp_cache_add('page_templates','ve');
    return $page_templates;
}
function ve_page_template_dropdown( $default = '' ) {
    $templates = ve_get_page_templates( get_post() );
    ksort( $templates );
    foreach ( array_keys( $templates ) as $template ) {
        $selected = selected( $default, $templates[ $template ], false );
        echo "\n\t<option value='" . $templates[ $template ] . "' $selected>$template</option>";
    }
}

function ve_add_front_js_inline($line){
    global $ve_front_js_inline;
    if(empty($ve_front_js_inline)){
        $ve_front_js_inline=array();
    }
    $line=trim($line);
    if($line){
        $ve_front_js_inline[]=$line;
    }
}
function ve_print_front_inline_js(){
    global $ve_front_js_inline;
    $jsCode=join("\n",$ve_front_js_inline);
    $jsCode=trim($jsCode);
    ?><script type="text/javascript">
        ve_front.ready(function($){
            <?php echo $jsCode;?>
        });
    </script><?php
}

/**
 * Convert array css attributes to string
 * @param $cssAttributes
 * @return String
 */
function ve_style_string($cssAttributes){
    if(!is_array($cssAttributes)){
        return '';
    }
    $lines=array();
    foreach ($cssAttributes as $k=>$v) {
        if($k&&$v){
            $lines[]=sprintf('%s:%s;',$k,$v);
        }
    }
    return join(' ',$lines);


}
function ve_attr_string($atts){
    return join(' ',$atts);
}
function ve_class_string($classes){
    if(!$classes){
        return '';
    }
    if(is_array($classes)){
        $classes=join(' ',$classes);
    }
    return $classes;
}
<?php
if (!function_exists('array_replace_recursive'))
{
    function ___recurse($array, $array1)
    {
        foreach ($array1 as $key => $value)
        {
            // create new key in $array, if it is empty or not an array
            if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key])))
            {
                $array[$key] = array();
            }

            // overwrite the value in the base array
            if (is_array($value))
            {
                $value = ___recurse($array[$key], $value);
            }
            $array[$key] = $value;
        }
        return $array;
    }
    function array_replace_recursive($array, $array1)
    {


        // handle the arguments, merge one by one
        $args = func_get_args();
        $array = $args[0];
        if (!is_array($array))
        {
            return $array;
        }
        for ($i = 1; $i < count($args); $i++)
        {
            if (is_array($args[$i]))
            {
                $array = ___recurse($array, $args[$i]);
            }
        }
        return $array;
    }
}
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
    return ve_mode()=='front_editor';
}
function ve_is_iframe(){
    return ve_mode()=='ve_iframe';
}
function is_ve(){
    return ve_is_editor() || ve_is_iframe();
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
function ve_hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);

    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    $rgb = array($r, $g, $b);
    //return implode(",", $rgb); // returns the rgb values separated by commas
    return $rgb; // returns an array with the rgb values
}
function get_awesome_icon_list(){
    return array (
        'sections' =>
            array (
                'title' => 'Medical Icons',
                'icons' =>
                    array (
                        0 =>
                            array (
                                'name' => 'ambulance',
                                'alias' => ' ambulance',
                            ),
                        1 =>
                            array (
                                'name' => 'h-square',
                                'alias' => ' h-square',
                            ),
                        2 =>
                            array (
                                'name' => 'heart',
                                'alias' => ' heart',
                            ),
                        3 =>
                            array (
                                'name' => 'heart-o',
                                'alias' => ' heart-o',
                            ),
                        4 =>
                            array (
                                'name' => 'heartbeat',
                                'alias' => ' heartbeat',
                            ),
                        5 =>
                            array (
                                'name' => 'hospital-o',
                                'alias' => ' hospital-o',
                            ),
                        6 =>
                            array (
                                'name' => 'medkit',
                                'alias' => ' medkit',
                            ),
                        7 =>
                            array (
                                'name' => 'plus-square',
                                'alias' => ' plus-square',
                            ),
                        8 =>
                            array (
                                'name' => 'stethoscope',
                                'alias' => ' stethoscope',
                            ),
                        9 =>
                            array (
                                'name' => 'user-md',
                                'alias' => ' user-md',
                            ),
                        10 =>
                            array (
                                'name' => 'wheelchair',
                                'alias' => ' wheelchair',
                            ),
                    ),
            ),
        'filters' =>
            array (
                'glass' => 'glass|martini|drink|bar|alcohol|liquor',
                'music' => 'music|note|sound',
                'search' => 'search|magnify|zoom|enlarge|bigger',
                'envelope-o' => 'envelope-o|email|support|e-mail|letter|mail|notification',
                'heart' => 'heart|love|like|favorite',
                'star' => 'star|award|achievement|night|rating|score',
                'star-o' => 'star-o|award|achievement|night|rating|score',
                'user' => 'user|person|man|head|profile',
                'film' => 'film|movie',
                'th-large' => 'th-large|blocks|squares|boxes',
                'th' => 'th|blocks|squares|boxes',
                'th-list' => 'th-list|ul|ol|checklist|finished|completed|done|todo',
                'check' => 'check|checkmark|done|todo|agree|accept|confirm',
                'times' => 'times|remove|close|close|exit|x',
                'search-plus' => 'search-plus|magnify|zoom|enlarge|bigger',
                'search-minus' => 'search-minus|magnify|minify|zoom|smaller',
                'power-off' => 'power-off|on',
                'signal' => 'signal',
                'cog' => 'cog|gear|settings',
                'trash-o' => 'trash-o|garbage|delete|remove|trash|hide',
                'home' => 'home|main|house',
                'file-o' => 'file-o|new|page|pdf|document',
                'clock-o' => 'clock-o|watch|timer|late|timestamp',
                'road' => 'road|street',
                'download' => 'download|import',
                'arrow-circle-o-down' => 'arrow-circle-o-down|download',
                'arrow-circle-o-up' => 'arrow-circle-o-up',
                'inbox' => 'inbox',
                'play-circle-o' => 'play-circle-o',
                'repeat' => 'repeat|rotate-right|redo|forward',
                'refresh' => 'refresh|reload',
                'list-alt' => 'list-alt|ul|ol|checklist|finished|completed|done|todo',
                'lock' => 'lock|protect|admin',
                'flag' => 'flag|report|notification|notify',
                'headphones' => 'headphones|sound|listen|music',
                'volume-off' => 'volume-off|mute|sound|music',
                'volume-down' => 'volume-down|lower|quieter|sound|music',
                'volume-up' => 'volume-up|higher|louder|sound|music',
                'qrcode' => 'qrcode|scan',
                'barcode' => 'barcode|scan',
                'tag' => 'tag|label',
                'tags' => 'tags|labels',
                'book' => 'book|read|documentation',
                'bookmark' => 'bookmark|save',
                'print' => 'print',
                'camera' => 'camera|photo|picture|record',
                'font' => 'font|text',
                'bold' => 'bold',
                'italic' => 'italic|italics',
                'text-height' => 'text-height',
                'text-width' => 'text-width',
                'align-left' => 'align-left|text',
                'align-center' => 'align-center|middle|text',
                'align-right' => 'align-right|text',
                'align-justify' => 'align-justify|text',
                'list' => 'list|ul|ol|checklist|finished|completed|done|todo',
                'outdent' => 'outdent|dedent',
                'indent' => 'indent',
                'video-camera' => 'video-camera|film|movie|record',
                'picture-o' => 'picture-o|photo|image',
                'pencil' => 'pencil|write|edit|update',
                'map-marker' => 'map-marker|map|pin|location|coordinates|localize|address|travel|where|place',
                'adjust' => 'adjust|contrast',
                'tint' => 'tint|raindrop',
                'pencil-square-o' => 'pencil-square-o|edit|write|edit|update',
                'share-square-o' => 'share-square-o|social|send',
                'check-square-o' => 'check-square-o|todo|done|agree|accept|confirm',
                'arrows' => 'arrows|move|reorder|resize',
                'step-backward' => 'step-backward|rewind|previous|beginning|start|first',
                'fast-backward' => 'fast-backward|rewind|previous|beginning|start|first',
                'backward' => 'backward|rewind|previous',
                'play' => 'play|start|playing|music|sound',
                'pause' => 'pause|wait',
                'stop' => 'stop|block|box|square',
                'forward' => 'forward|forward|next',
                'fast-forward' => 'fast-forward|next|end|last',
                'step-forward' => 'step-forward|next|end|last',
                'eject' => 'eject',
                'chevron-left' => 'chevron-left|bracket|previous|back',
                'chevron-right' => 'chevron-right|bracket|next|forward',
                'plus-circle' => 'plus-circle|add|new|create|expand',
                'minus-circle' => 'minus-circle|delete|remove|trash|hide',
                'times-circle' => 'times-circle|close|exit|x',
                'check-circle' => 'check-circle|todo|done|agree|accept|confirm',
                'question-circle' => 'question-circle|help|information|unknown|support',
                'info-circle' => 'info-circle|help|information|more|details',
                'crosshairs' => 'crosshairs|picker',
                'times-circle-o' => 'times-circle-o|close|exit|x',
                'check-circle-o' => 'check-circle-o|todo|done|agree|accept|confirm',
                'ban' => 'ban|block|abort',
                'arrow-left' => 'arrow-left|previous|back',
                'arrow-right' => 'arrow-right|next|forward',
                'arrow-up' => 'arrow-up',
                'arrow-down' => 'arrow-down|download',
                'share' => 'share|mail-forward',
                'expand' => 'expand|enlarge|bigger|resize',
                'compress' => 'compress|combine|merge|smaller',
                'plus' => 'plus|add|new|create|expand',
                'minus' => 'minus|hide|minify|delete|remove|trash|hide|collapse',
                'asterisk' => 'asterisk|details',
                'exclamation-circle' => 'exclamation-circle|warning|error|problem|notification|alert',
                'gift' => 'gift|present',
                'leaf' => 'leaf|eco|nature',
                'fire' => 'fire|flame|hot|popular',
                'eye' => 'eye|show|visible|views',
                'eye-slash' => 'eye-slash|toggle|show|hide|visible|visiblity|views',
                'exclamation-triangle' => 'exclamation-triangle|warning|warning|error|problem|notification|alert',
                'plane' => 'plane|travel|trip|location|destination|airplane|fly|mode',
                'calendar' => 'calendar|date|time|when',
                'random' => 'random|sort',
                'comment' => 'comment|speech|notification|note',
                'magnet' => 'magnet',
                'chevron-up' => 'chevron-up',
                'chevron-down' => 'chevron-down',
                'retweet' => 'retweet|refresh|reload|share',
                'shopping-cart' => 'shopping-cart|checkout|buy|purchase|payment',
                'folder' => 'folder',
                'folder-open' => 'folder-open',
                'arrows-v' => 'arrows-v|resize',
                'arrows-h' => 'arrows-h|resize',
                'bar-chart' => 'bar-chart|bar-chart-o|graph',
                'twitter-square' => 'twitter-square|tweet|social network',
                'facebook-square' => 'facebook-square|social network',
                'camera-retro' => 'camera-retro|photo|picture|record',
                'key' => 'key|unlock|password',
                'cogs' => 'cogs|gears|settings',
                'comments' => 'comments|conversation|notification|notes',
                'thumbs-o-up' => 'thumbs-o-up|like|approve|favorite|agree',
                'thumbs-o-down' => 'thumbs-o-down|dislike|disapprove|disagree',
                'star-half' => 'star-half|award|achievement|rating|score',
                'heart-o' => 'heart-o|love|like|favorite',
                'sign-out' => 'sign-out|log out|logout|leave|exit|arrow',
                'linkedin-square' => 'linkedin-square',
                'thumb-tack' => 'thumb-tack|marker|pin|location|coordinates',
                'external-link' => 'external-link|open|new',
                'sign-in' => 'sign-in|enter|join|sign up|sign in|signin|signup|arrow',
                'trophy' => 'trophy|award|achievement|winner|game',
                'github-square' => 'github-square|octocat',
                'upload' => 'upload|import',
                'lemon-o' => 'lemon-o',
                'phone' => 'phone|call|voice|number|support',
                'square-o' => 'square-o|block|square|box',
                'bookmark-o' => 'bookmark-o|save',
                'phone-square' => 'phone-square|call|voice|number|support',
                'twitter' => 'twitter|tweet|social network',
                'facebook' => 'facebook|facebook-f|social network',
                'github' => 'github|octocat',
                'unlock' => 'unlock|protect|admin|password',
                'credit-card' => 'credit-card|money|buy|debit|checkout|purchase|payment',
                'rss' => 'rss|feed|blog',
                'hdd-o' => 'hdd-o|harddrive|hard drive|storage|save',
                'bullhorn' => 'bullhorn|announcement|share|broadcast|louder',
                'bell' => 'bell|alert|reminder|notification',
                'certificate' => 'certificate|badge|star',
                'hand-o-right' => 'hand-o-right|point|right|next|forward',
                'hand-o-left' => 'hand-o-left|point|left|previous|back',
                'hand-o-up' => 'hand-o-up|point',
                'hand-o-down' => 'hand-o-down|point',
                'arrow-circle-left' => 'arrow-circle-left|previous|back',
                'arrow-circle-right' => 'arrow-circle-right|next|forward',
                'arrow-circle-up' => 'arrow-circle-up',
                'arrow-circle-down' => 'arrow-circle-down|download',
                'globe' => 'globe|world|planet|map|place|travel|earth|global|translate|all|language|localize|location|coordinates|country',
                'wrench' => 'wrench|settings|fix|update',
                'tasks' => 'tasks|progress|loading|downloading|downloads|settings',
                'filter' => 'filter|funnel|options',
                'briefcase' => 'briefcase|work|business|office|luggage|bag',
                'arrows-alt' => 'arrows-alt|expand|enlarge|bigger|move|reorder|resize',
                'users' => 'users|group|people|profiles|persons',
                'link' => 'link|chain|chain',
                'cloud' => 'cloud|save',
                'flask' => 'flask|science|beaker|experimental|labs',
                'scissors' => 'scissors|cut',
                'files-o' => 'files-o|copy|duplicate',
                'paperclip' => 'paperclip|attachment',
                'floppy-o' => 'floppy-o|save',
                'square' => 'square|block|box',
                'bars' => 'bars|navicon|reorder|menu|drag|reorder|settings|list|ul|ol|checklist|todo|list',
                'list-ul' => 'list-ul|ul|ol|checklist|todo|list',
                'list-ol' => 'list-ol|ul|ol|checklist|list|todo|list|numbers',
                'strikethrough' => 'strikethrough',
                'underline' => 'underline',
                'table' => 'table|data|excel|spreadsheet',
                'magic' => 'magic|wizard|automatic|autocomplete',
                'truck' => 'truck|shipping',
                'pinterest' => 'pinterest',
                'pinterest-square' => 'pinterest-square',
                'google-plus-square' => 'google-plus-square|social network',
                'google-plus' => 'google-plus|social network',
                'money' => 'money|cash|money|buy|checkout|purchase|payment',
                'caret-down' => 'caret-down|more|dropdown|menu',
                'caret-up' => 'caret-up',
                'caret-left' => 'caret-left|previous|back',
                'caret-right' => 'caret-right|next|forward',
                'columns' => 'columns|split|panes',
                'sort' => 'sort|unsorted|order',
                'sort-desc' => 'sort-desc|sort-down|dropdown|more|menu',
                'sort-asc' => 'sort-asc|sort-up',
                'envelope' => 'envelope',
                'linkedin' => 'linkedin',
                'undo' => 'undo|rotate-left|back',
                'gavel' => 'gavel|legal',
                'tachometer' => 'tachometer|dashboard',
                'comment-o' => 'comment-o|notification|note',
                'comments-o' => 'comments-o|conversation|notification|notes',
                'bolt' => 'bolt|flash|lightning|weather',
                'sitemap' => 'sitemap|directory|hierarchy|organization',
                'umbrella' => 'umbrella',
                'clipboard' => 'clipboard|paste|copy',
                'lightbulb-o' => 'lightbulb-o|idea|inspiration',
                'exchange' => 'exchange',
                'cloud-download' => 'cloud-download|import',
                'cloud-upload' => 'cloud-upload|import',
                'user-md' => 'user-md|doctor|profile|medical|nurse',
                'stethoscope' => 'stethoscope',
                'suitcase' => 'suitcase|trip|luggage|travel|move|baggage',
                'bell-o' => 'bell-o|alert|reminder|notification',
                'coffee' => 'coffee|morning|mug|breakfast|tea|drink|cafe',
                'cutlery' => 'cutlery|food|restaurant|spoon|knife|dinner|eat',
                'file-text-o' => 'file-text-o|new|page|pdf|document',
                'building-o' => 'building-o|work|business|apartment|office',
                'hospital-o' => 'hospital-o|building',
                'ambulance' => 'ambulance|support|help',
                'medkit' => 'medkit|first aid|firstaid|help|support',
                'fighter-jet' => 'fighter-jet|fly|plane|airplane|quick|fast|travel',
                'beer' => 'beer|alcohol|stein|drink|mug|bar|liquor',
                'h-square' => 'h-square|hospital|hotel',
                'plus-square' => 'plus-square|add|new|create|expand',
                'angle-double-left' => 'angle-double-left|laquo|quote|previous|back',
                'angle-double-right' => 'angle-double-right|raquo|quote|next|forward',
                'angle-double-up' => 'angle-double-up',
                'angle-double-down' => 'angle-double-down',
                'angle-left' => 'angle-left|previous|back',
                'angle-right' => 'angle-right|next|forward',
                'angle-up' => 'angle-up',
                'angle-down' => 'angle-down',
                'desktop' => 'desktop|monitor|screen|desktop|computer|demo|device',
                'laptop' => 'laptop|demo|computer|device',
                'tablet' => 'tablet|ipad|device',
                'mobile' => 'mobile|mobile-phone|cell phone|cellphone|text|call|iphone|number',
                'circle-o' => 'circle-o',
                'quote-left' => 'quote-left',
                'quote-right' => 'quote-right',
                'spinner' => 'spinner|loading|progress',
                'circle' => 'circle|dot|notification',
                'reply' => 'reply|mail-reply',
                'github-alt' => 'github-alt|octocat',
                'folder-o' => 'folder-o',
                'folder-open-o' => 'folder-open-o',
                'smile-o' => 'smile-o|emoticon|happy|approve|satisfied|rating',
                'frown-o' => 'frown-o|emoticon|sad|disapprove|rating',
                'meh-o' => 'meh-o|emoticon|rating|neutral',
                'gamepad' => 'gamepad|controller',
                'keyboard-o' => 'keyboard-o|type|input',
                'flag-o' => 'flag-o|report|notification',
                'flag-checkered' => 'flag-checkered|report|notification|notify',
                'terminal' => 'terminal|command|prompt|code',
                'code' => 'code|html|brackets',
                'reply-all' => 'reply-all|mail-reply-all',
                'star-half-o' => 'star-half-o|star-half-empty|star-half-full|award|achievement|rating|score',
                'location-arrow' => 'location-arrow|map|coordinates|location|address|place|where',
                'crop' => 'crop',
                'code-fork' => 'code-fork|git|fork|vcs|svn|github|rebase|version|merge',
                'chain-broken' => 'chain-broken|unlink|remove',
                'question' => 'question|help|information|unknown|support',
                'info' => 'info|help|information|more|details',
                'exclamation' => 'exclamation|warning|error|problem|notification|notify|alert',
                'superscript' => 'superscript|exponential',
                'subscript' => 'subscript',
                'eraser' => 'eraser',
                'puzzle-piece' => 'puzzle-piece|addon|add-on|section',
                'microphone' => 'microphone|record|voice|sound',
                'microphone-slash' => 'microphone-slash|record|voice|sound|mute',
                'shield' => 'shield|award|achievement|winner',
                'calendar-o' => 'calendar-o|date|time|when',
                'fire-extinguisher' => 'fire-extinguisher',
                'rocket' => 'rocket|app',
                'maxcdn' => 'maxcdn',
                'chevron-circle-left' => 'chevron-circle-left|previous|back',
                'chevron-circle-right' => 'chevron-circle-right|next|forward',
                'chevron-circle-up' => 'chevron-circle-up',
                'chevron-circle-down' => 'chevron-circle-down|more|dropdown|menu',
                'html5' => 'html5',
                'css3' => 'css3|code',
                'anchor' => 'anchor|link',
                'unlock-alt' => 'unlock-alt|protect|admin|password',
                'bullseye' => 'bullseye|target',
                'ellipsis-h' => 'ellipsis-h|dots',
                'ellipsis-v' => 'ellipsis-v|dots',
                'rss-square' => 'rss-square|feed|blog',
                'play-circle' => 'play-circle|start|playing',
                'ticket' => 'ticket|movie|pass|support',
                'minus-square' => 'minus-square|hide|minify|delete|remove|trash|hide|collapse',
                'minus-square-o' => 'minus-square-o|hide|minify|delete|remove|trash|hide|collapse',
                'level-up' => 'level-up',
                'level-down' => 'level-down',
                'check-square' => 'check-square|checkmark|done|todo|agree|accept|confirm',
                'pencil-square' => 'pencil-square|write|edit|update',
                'external-link-square' => 'external-link-square|open|new',
                'share-square' => 'share-square|social|send',
                'compass' => 'compass|safari|directory|menu|location',
                'caret-square-o-down' => 'caret-square-o-down|toggle-down|more|dropdown|menu',
                'caret-square-o-up' => 'caret-square-o-up|toggle-up',
                'caret-square-o-right' => 'caret-square-o-right|toggle-right|next|forward',
                'eur' => 'eur|euro',
                'gbp' => 'gbp',
                'usd' => 'usd|dollar',
                'inr' => 'inr|rupee',
                'jpy' => 'jpy|cny|rmb|yen',
                'rub' => 'rub|ruble|rouble',
                'krw' => 'krw|won',
                'btc' => 'btc|bitcoin',
                'file' => 'file|new|page|pdf|document',
                'file-text' => 'file-text|new|page|pdf|document',
                'sort-alpha-asc' => 'sort-alpha-asc',
                'sort-alpha-desc' => 'sort-alpha-desc',
                'sort-amount-asc' => 'sort-amount-asc',
                'sort-amount-desc' => 'sort-amount-desc',
                'sort-numeric-asc' => 'sort-numeric-asc|numbers',
                'sort-numeric-desc' => 'sort-numeric-desc|numbers',
                'thumbs-up' => 'thumbs-up|like|favorite|approve|agree',
                'thumbs-down' => 'thumbs-down|dislike|disapprove|disagree',
                'youtube-square' => 'youtube-square|video|film',
                'youtube' => 'youtube|video|film',
                'xing' => 'xing',
                'xing-square' => 'xing-square',
                'youtube-play' => 'youtube-play|start|playing',
                'dropbox' => 'dropbox',
                'stack-overflow' => 'stack-overflow',
                'instagram' => 'instagram',
                'flickr' => 'flickr',
                'adn' => 'adn',
                'bitbucket' => 'bitbucket|git',
                'bitbucket-square' => 'bitbucket-square|git',
                'tumblr' => 'tumblr',
                'tumblr-square' => 'tumblr-square',
                'long-arrow-down' => 'long-arrow-down',
                'long-arrow-up' => 'long-arrow-up',
                'long-arrow-left' => 'long-arrow-left|previous|back',
                'long-arrow-right' => 'long-arrow-right',
                'apple' => 'apple|osx',
                'windows' => 'windows',
                'android' => 'android',
                'linux' => 'linux|tux',
                'dribbble' => 'dribbble',
                'skype' => 'skype',
                'foursquare' => 'foursquare',
                'trello' => 'trello',
                'female' => 'female|woman|user|person|profile',
                'male' => 'male|man|user|person|profile',
                'gratipay' => 'gratipay|gittip|heart|like|favorite|love',
                'sun-o' => 'sun-o|weather|contrast|lighter|brighten|day',
                'moon-o' => 'moon-o|night|darker|contrast',
                'archive' => 'archive|box|storage',
                'bug' => 'bug|report',
                'vk' => 'vk',
                'weibo' => 'weibo',
                'renren' => 'renren',
                'pagelines' => 'pagelines|leaf|leaves|tree|plant|eco|nature',
                'stack-exchange' => 'stack-exchange',
                'arrow-circle-o-right' => 'arrow-circle-o-right|next|forward',
                'arrow-circle-o-left' => 'arrow-circle-o-left|previous|back',
                'caret-square-o-left' => 'caret-square-o-left|toggle-left|previous|back',
                'dot-circle-o' => 'dot-circle-o|target|bullseye|notification',
                'wheelchair' => 'wheelchair|handicap|person|accessibility|accessibile',
                'vimeo-square' => 'vimeo-square',
                'try' => 'try|turkish-lira',
                'plus-square-o' => 'plus-square-o|add|new|create|expand',
                'space-shuttle' => 'space-shuttle',
                'slack' => 'slack',
                'envelope-square' => 'envelope-square',
                'wordpress' => 'wordpress',
                'openid' => 'openid',
                'university' => 'university|institution|bank',
                'graduation-cap' => 'graduation-cap|mortar-board',
                'yahoo' => 'yahoo',
                'google' => 'google',
                'reddit' => 'reddit',
                'reddit-square' => 'reddit-square',
                'stumbleupon-circle' => 'stumbleupon-circle',
                'stumbleupon' => 'stumbleupon',
                'delicious' => 'delicious',
                'digg' => 'digg',
                'pied-piper' => 'pied-piper',
                'pied-piper-alt' => 'pied-piper-alt',
                'drupal' => 'drupal',
                'joomla' => 'joomla',
                'language' => 'language',
                'fax' => 'fax',
                'building' => 'building',
                'child' => 'child',
                'paw' => 'paw',
                'spoon' => 'spoon',
                'cube' => 'cube',
                'cubes' => 'cubes',
                'behance' => 'behance',
                'behance-square' => 'behance-square',
                'steam' => 'steam',
                'steam-square' => 'steam-square',
                'recycle' => 'recycle',
                'car' => 'car|automobile|vehicle',
                'taxi' => 'taxi|cab|vehicle',
                'tree' => 'tree',
                'spotify' => 'spotify',
                'deviantart' => 'deviantart',
                'soundcloud' => 'soundcloud',
                'database' => 'database',
                'file-pdf-o' => 'file-pdf-o',
                'file-word-o' => 'file-word-o',
                'file-excel-o' => 'file-excel-o',
                'file-powerpoint-o' => 'file-powerpoint-o',
                'file-image-o' => 'file-image-o|file-photo-o|file-picture-o',
                'file-archive-o' => 'file-archive-o|file-zip-o',
                'file-audio-o' => 'file-audio-o|file-sound-o',
                'file-video-o' => 'file-video-o|file-movie-o',
                'file-code-o' => 'file-code-o',
                'vine' => 'vine',
                'codepen' => 'codepen',
                'jsfiddle' => 'jsfiddle',
                'life-ring' => 'life-ring|life-bouy|life-buoy|life-saver|support',
                'circle-o-notch' => 'circle-o-notch',
                'rebel' => 'rebel|ra',
                'empire' => 'empire|ge',
                'git-square' => 'git-square',
                'git' => 'git',
                'hacker-news' => 'hacker-news',
                'tencent-weibo' => 'tencent-weibo',
                'qq' => 'qq',
                'weixin' => 'weixin|wechat',
                'paper-plane' => 'paper-plane|send',
                'paper-plane-o' => 'paper-plane-o|send-o',
                'history' => 'history',
                'circle-thin' => 'circle-thin|genderless',
                'header' => 'header',
                'paragraph' => 'paragraph',
                'sliders' => 'sliders',
                'share-alt' => 'share-alt',
                'share-alt-square' => 'share-alt-square',
                'bomb' => 'bomb',
                'futbol-o' => 'futbol-o|soccer-ball-o',
                'tty' => 'tty',
                'binoculars' => 'binoculars',
                'plug' => 'plug',
                'slideshare' => 'slideshare',
                'twitch' => 'twitch',
                'yelp' => 'yelp',
                'newspaper-o' => 'newspaper-o',
                'wifi' => 'wifi',
                'calculator' => 'calculator',
                'paypal' => 'paypal',
                'google-wallet' => 'google-wallet',
                'cc-visa' => 'cc-visa',
                'cc-mastercard' => 'cc-mastercard',
                'cc-discover' => 'cc-discover',
                'cc-amex' => 'cc-amex',
                'cc-paypal' => 'cc-paypal',
                'cc-stripe' => 'cc-stripe',
                'bell-slash' => 'bell-slash',
                'bell-slash-o' => 'bell-slash-o',
                'trash' => 'trash',
                'copyright' => 'copyright',
                'at' => 'at',
                'eyedropper' => 'eyedropper',
                'paint-brush' => 'paint-brush',
                'birthday-cake' => 'birthday-cake',
                'area-chart' => 'area-chart',
                'pie-chart' => 'pie-chart',
                'line-chart' => 'line-chart',
                'lastfm' => 'lastfm',
                'lastfm-square' => 'lastfm-square',
                'toggle-off' => 'toggle-off',
                'toggle-on' => 'toggle-on',
                'bicycle' => 'bicycle|vehicle|bike',
                'bus' => 'bus|vehicle',
                'ioxhost' => 'ioxhost',
                'angellist' => 'angellist',
                'cc' => 'cc',
                'ils' => 'ils|shekel|sheqel',
                'meanpath' => 'meanpath',
                'buysellads' => 'buysellads',
                'connectdevelop' => 'connectdevelop',
                'dashcube' => 'dashcube',
                'forumbee' => 'forumbee',
                'leanpub' => 'leanpub',
                'sellsy' => 'sellsy',
                'shirtsinbulk' => 'shirtsinbulk',
                'simplybuilt' => 'simplybuilt',
                'skyatlas' => 'skyatlas',
                'cart-plus' => 'cart-plus|add|shopping',
                'cart-arrow-down' => 'cart-arrow-down|shopping',
                'diamond' => 'diamond|gem|gemstone',
                'ship' => 'ship|boat|sea',
                'user-secret' => 'user-secret|whisper|spy|incognito',
                'motorcycle' => 'motorcycle|vehicle|bike',
                'street-view' => 'street-view|map',
                'heartbeat' => 'heartbeat|ekg',
                'venus' => 'venus|female',
                'mars' => 'mars|male',
                'mercury' => 'mercury|transgender',
                'transgender' => 'transgender',
                'transgender-alt' => 'transgender-alt',
                'venus-double' => 'venus-double',
                'mars-double' => 'mars-double',
                'venus-mars' => 'venus-mars',
                'mars-stroke' => 'mars-stroke',
                'mars-stroke-v' => 'mars-stroke-v',
                'mars-stroke-h' => 'mars-stroke-h',
                'neuter' => 'neuter',
                'facebook-official' => 'facebook-official',
                'pinterest-p' => 'pinterest-p',
                'whatsapp' => 'whatsapp',
                'server' => 'server',
                'user-plus' => 'user-plus',
                'user-times' => 'user-times',
                'bed' => 'bed|hotel|travel',
                'viacoin' => 'viacoin',
                'train' => 'train',
                'subway' => 'subway',
                'medium' => 'medium',
            ),
    );
}
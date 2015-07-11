<?php
if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    if (!class_exists('ve_scssc')) {
        include dirname(__FILE__) . '/scssphp/scss.inc.php';
    }

}
/**
 * @return ve_scssc
 */
function ve_scssc()
{
    if(!class_exists('ve_scssc')){
        return false;
    }
    static $ve_scssc;
    if (!$ve_scssc) {
        $ve_scssc = new ve_scssc();
    }
    return $ve_scssc;
}
/**
 * try to fix some error on custom css, eg missing ;
 * @param $css
 * @return string fixed css
 *
 */
function ve_fix_custom_css($css){
    $cssLines=explode(PHP_EOL,$css);
    foreach($cssLines as $index=>$line){
        if(trim($line)){
            $line=rtrim($line);
            if(substr($line,strlen($line))!=';'){
                $line.=';';
            }
        }
        $cssLines[$index]=$line;
    }
    return implode(PHP_EOL,$cssLines);
}
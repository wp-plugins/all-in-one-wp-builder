<?php
/*
Plugin Name: Visual Editor
Plugin URI: http://wordpress.org/
Description: Visual front-end editor
Author: VE Page builder
Version: 1.1
Author URI: http://wordpress.org/
*/
define('VE_DIR',__DIR__);
define('VE_URL',plugins_url('',__FILE__));
define('VE_CONFIG',VE_DIR.'/config');
define('VE_CORE',VE_DIR.'/core');
define('VE_MODULE',VE_DIR.'/modules');
define('VE_VIEW',VE_DIR.'/view');
define('VE_PAGE_TEMPLATE_DIR',VE_VIEW.'/page-templates');
define('VE_VERSION','1.0.0');
require_once VE_CORE.'/load.php';
$ve_loader=new VE_Loader();
$ve_loader->init()->ve_manager()->run(require VE_CONFIG.'/ve-config.php');
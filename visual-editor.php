<?php
/*
Plugin Name: AIO WP Builder
Plugin URI: http://wordpress.org/
Description: Visually create your sale pages, landing pages... in WordPress with simple yet powerful drag & drop builder.
Author: AIO WP Builder
Version: 1.1
Author URI: http://allinonewpbuilder.com/
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
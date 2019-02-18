<?php

/**
 * Plugin Name: Quform
 * Plugin URI: https://www.quform.com
 * Description: The Quform form builder makes it easy to build forms in WordPress.
 * Version: 2.0.1
 * Author: ThemeCatcher
 * Author URI: https://www.themecatcher.net
 * Text Domain: quform
 */

// Prevent direct script access
if ( ! defined('ABSPATH')) {
    exit;
}

define('QUFORM_VERSION', '2.0.1');
define('QUFORM_PATH', dirname(__FILE__));
define('QUFORM_NAME', basename(QUFORM_PATH));
define('QUFORM_BASENAME', QUFORM_NAME . '/' . basename(__FILE__));
define('QUFORM_LIBRARY_PATH', QUFORM_PATH . '/library');
define('QUFORM_TEMPLATE_PATH', QUFORM_PATH . '/library/templates');
define('QUFORM_ADMIN_PATH', QUFORM_PATH . '/admin');

if ( ! class_exists('JuiceContainer')) {
    require_once QUFORM_LIBRARY_PATH . '/JuiceContainer.php';
}

// Class auto-loader
require_once QUFORM_LIBRARY_PATH . '/Quform/ClassLoader.php';
Quform_ClassLoader::register();

// Bootstrap
$GLOBALS['quform'] = new Quform_Dispatcher(new Quform_Container());
$GLOBALS['quform']->bootstrap();

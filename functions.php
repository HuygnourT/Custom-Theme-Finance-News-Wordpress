<?php
if (!defined('ABSPATH')) exit;
define('FXT_VERSION', '2.0.0');
define('FXT_DIR', get_template_directory());
define('FXT_URI', get_template_directory_uri());

require_once FXT_DIR . '/inc/theme-setup.php';
require_once FXT_DIR . '/inc/enqueue.php';
require_once FXT_DIR . '/inc/custom-post-types.php';
require_once FXT_DIR . '/inc/meta-boxes.php';
require_once FXT_DIR . '/inc/customizer.php';
require_once FXT_DIR . '/inc/seo-helpers.php';
require_once FXT_DIR . '/inc/template-functions.php';
require_once FXT_DIR . '/inc/demo-import.php';

<?php
if (!defined('ABSPATH')) exit;
define('FXT_VERSION', '2.1.0');
define('FXT_DIR', get_template_directory());
define('FXT_URI', get_template_directory_uri());

require_once FXT_DIR . '/inc/theme-setup.php';
require_once FXT_DIR . '/inc/enqueue.php';
require_once FXT_DIR . '/inc/custom-post-types.php';
require_once FXT_DIR . '/inc/generic-post-types.php';     // NEW: Generic sub-posts CPT
require_once FXT_DIR . '/inc/meta-boxes.php';
require_once FXT_DIR . '/inc/meta-boxes-sub-posts.php';    // NEW: CTA, Pros/Cons, Sections cho sub-posts
require_once FXT_DIR . '/inc/mega-menu.php';               // NEW: Category bar / mega menu
require_once FXT_DIR . '/inc/customizer.php';
require_once FXT_DIR . '/inc/seo-helpers.php';
require_once FXT_DIR . '/inc/template-functions.php';
require_once FXT_DIR . '/inc/demo-import.php';

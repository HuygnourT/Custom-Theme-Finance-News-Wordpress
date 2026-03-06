<?php
/**
 * Enqueue v2.1 - Load CSS & JS + tắt WP mặc định
 * UPDATED: Thêm broker-sections.js cho single broker page
 */
if (!defined('ABSPATH')) exit;

// === LOAD CSS & JS ===
add_action('wp_enqueue_scripts', function () {
    // Google Fonts
    wp_enqueue_style('fxt-fonts', 'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap', [], null);
    // Main CSS từ style.css root
    wp_enqueue_style('fxt-style', get_stylesheet_uri(), ['fxt-fonts'], FXT_VERSION);
    // JS
    wp_enqueue_script('fxt-main', FXT_URI . '/assets/js/main.js', [], FXT_VERSION, true);

    if (is_page_template('page-templates/template-brokers.php') || is_post_type_archive('broker')) {
        wp_enqueue_script('fxt-broker-filter', FXT_URI . '/assets/js/broker-filter.js', [], FXT_VERSION, true);
    }

    // Broker sections: tabs + collapsible (chỉ load trên single broker)
    if (is_singular('broker')) {
        wp_enqueue_script('fxt-broker-sections', FXT_URI . '/assets/js/broker-sections.js', [], FXT_VERSION, true);
    }
}, 10);

// === TẮT TẤT CẢ WP DEFAULT STYLES (priority 200 = chạy sau cùng) ===
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_deregister_style('wp-block-library');
    wp_deregister_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
    wp_deregister_style('global-styles');
    wp_dequeue_style('classic-theme-styles');
    wp_deregister_style('classic-theme-styles');
    wp_dequeue_style('core-block-supports');
    wp_dequeue_style('wc-blocks-style');
}, 200);

// === TẮT GLOBAL STYLES INLINE CSS ===
remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
remove_action('wp_footer', 'wp_enqueue_global_styles', 1);

// === TẮT BLOCK EDITOR TRONG FRONTEND ===
add_action('init', function () {
    remove_action('wp_head', 'wp_enqueue_block_support_styles', 1);
}, 100);

add_action('after_setup_theme', function () {
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
});

// === TẮT jQuery ===
add_action('wp_enqueue_scripts', function () {
    if (!is_admin()) {
        wp_deregister_script('jquery');
    }
}, 20);

// === PRECONNECT FONTS ===
add_action('wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);

// === DEFER SCRIPTS ===
add_filter('script_loader_tag', function ($tag, $handle) {
    if (in_array($handle, ['fxt-main', 'fxt-broker-filter', 'fxt-broker-sections'])) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}, 10, 2);

<?php
/**
 * Theme Setup - Đăng ký tất cả features của theme
 * 
 * Hooks trong WP giống Event Emitters trong Node.js:
 *   add_action('event_name', callback)  ≈  emitter.on('event', callback)
 *   add_filter('data_name', callback)   ≈  pipe/transform data
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

/**
 * Đăng ký theme features
 * Hook 'after_setup_theme' chạy sau khi WP load theme
 */
add_action('after_setup_theme', function () {

    // Cho phép WP tự tạo <title> tag (không hardcode trong header.php)
    add_theme_support('title-tag');

    // Cho phép Featured Image (ảnh đại diện bài viết)
    add_theme_support('post-thumbnails');

    // Kích thước ảnh custom - WP sẽ tự resize khi upload
    add_image_size('fxt-card', 400, 250, true);        // Card bài viết trên homepage
    add_image_size('fxt-card-small', 120, 80, true);   // Card nhỏ sidebar
    add_image_size('fxt-hero', 1200, 500, true);       // Ảnh hero bài viết
    add_image_size('fxt-broker-logo', 200, 80, false);  // Logo broker (không crop)

    // Đăng ký vị trí menu
    register_nav_menus([
        'primary'   => 'Menu chính (Header)',
        'footer'    => 'Menu Footer',
        'mobile'    => 'Menu Mobile',
    ]);

    // HTML5 markup cho các elements
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    // Custom logo trong Customizer
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    // Tắt block editor styles mặc định (ta tự viết CSS)
    remove_theme_support('wp-block-styles');
});

/**
 * Đăng ký Widget Areas (Sidebar)
 * Widget = các block kéo thả trong WP Admin > Appearance > Widgets
 */
add_action('widgets_init', function () {

    // Sidebar chính - hiển thị bên phải bài viết
    register_sidebar([
        'name'          => 'Sidebar Chính',
        'id'            => 'main-sidebar',
        'description'   => 'Hiển thị bên phải bài viết và trang',
        'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    // Sidebar broker - dành riêng cho trang review broker
    register_sidebar([
        'name'          => 'Sidebar Broker',
        'id'            => 'broker-sidebar',
        'description'   => 'Widget area cho trang review/so sánh broker',
        'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    // Footer widgets - 3 cột
    for ($i = 1; $i <= 3; $i++) {
        register_sidebar([
            'name'          => "Footer Cột {$i}",
            'id'            => "footer-col-{$i}",
            'description'   => "Widget footer cột {$i}",
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-widget-title">',
            'after_title'   => '</h4>',
        ]);
    }
});

/**
 * Thay đổi excerpt length (số từ tóm tắt bài viết)
 * Mặc định WP là 55 từ, ta giảm xuống 25 cho card
 */
add_filter('excerpt_length', function () {
    return 25;
});

/**
 * Thay đổi ký tự kết thúc excerpt
 */
add_filter('excerpt_more', function () {
    return '...';
});

/**
 * Tối ưu: Tắt emoji script mặc định của WP (tiết kiệm ~50KB)
 */
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
});

/**
 * Tối ưu: Xóa các meta tags không cần thiết trong <head>
 */
remove_action('wp_head', 'wp_generator');                // Ẩn version WP
remove_action('wp_head', 'wlwmanifest_link');            // Windows Live Writer
remove_action('wp_head', 'rsd_link');                    // Really Simple Discovery
remove_action('wp_head', 'wp_shortlink_wp_head');        // Shortlink

<?php
/**
 * Custom Post Types - Đăng ký loại bài viết tùy chỉnh
 * 
 * Giống việc tạo Model/Schema trong Node.js:
 *   const BrokerSchema = new Schema({ name, spread, leverage... })
 * 
 * WP mặc định có: post (Bài viết), page (Trang)
 * Ta thêm: broker (Sàn giao dịch) - có fields riêng
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

/**
 * Đăng ký Custom Post Type: Broker
 */
add_action('init', function () {

    // Lấy slug từ Customizer (có thể thay đổi từ WP Admin)
    $broker_slug = get_theme_mod('fxt_broker_slug', 'broker-review');

    $labels = [
        'name'               => 'Brokers',
        'singular_name'      => 'Broker',
        'menu_name'          => 'Brokers',
        'add_new'            => 'Thêm Broker',
        'add_new_item'       => 'Thêm Broker Mới',
        'edit_item'          => 'Sửa Broker',
        'new_item'           => 'Broker Mới',
        'view_item'          => 'Xem Broker',
        'search_items'       => 'Tìm Broker',
        'not_found'          => 'Không tìm thấy broker',
        'not_found_in_trash' => 'Không có broker trong thùng rác',
        'all_items'          => 'Tất cả Brokers',
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,                    // Hiển thị trên frontend
        'publicly_queryable' => true,                    // Có thể truy vấn URL
        'show_ui'            => true,                    // Hiển thị trong WP Admin
        'show_in_menu'       => true,                    // Hiển thị trong menu admin
        'show_in_rest'       => true,                    // Hỗ trợ REST API + Gutenberg
        'menu_position'      => 5,                       // Vị trí trong sidebar admin
        'menu_icon'          => 'dashicons-chart-area',  // Icon trong admin
        'capability_type'    => 'post',
        'has_archive'        => 'brokers',               // URL archive: /brokers/
        'rewrite'            => [
            'slug'       => sanitize_title($broker_slug), // Customizable slug
            'with_front' => false,
        ],
        'supports'           => [
            'title',          // Tên broker
            'editor',         // Nội dung review chi tiết
            'thumbnail',      // Logo broker
            'excerpt',        // Tóm tắt ngắn
            'custom-fields',  // Meta fields
            'revisions',      // Lịch sử chỉnh sửa
        ],
    ];

    register_post_type('broker', $args);
});

/**
 * Đăng ký Taxonomy: Broker Category (Loại broker)
 * Giống categories cho posts, nhưng riêng cho broker
 */
add_action('init', function () {

    // Lấy slug từ Customizer
    $broker_type_slug = get_theme_mod('fxt_broker_type_slug', 'broker-type');

    $labels = [
        'name'          => 'Loại Broker',
        'singular_name' => 'Loại Broker',
        'search_items'  => 'Tìm loại broker',
        'all_items'     => 'Tất cả loại',
        'edit_item'     => 'Sửa loại broker',
        'add_new_item'  => 'Thêm loại broker',
        'menu_name'     => 'Loại Broker',
    ];

    register_taxonomy('broker_type', 'broker', [
        'labels'            => $labels,
        'hierarchical'      => true,          // Giống category (có parent/child)
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,          // Hiển thị cột trong admin list
        'rewrite'           => [
            'slug' => sanitize_title($broker_type_slug),
        ],
    ]);
});

/**
 * Flush rewrite rules khi activate theme
 * (Cần thiết để URL /broker-review/xxx hoạt động)
 */
add_action('after_switch_theme', function () {
    flush_rewrite_rules();
});

/**
 * Auto flush rewrite rules khi slug thay đổi trong Customizer
 */
add_action('customize_save_after', function () {
    flush_rewrite_rules();
});

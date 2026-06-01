<?php
/**
 * inc/performance.php — Tối ưu tốc độ load (đặc biệt mobile)
 *
 * Bổ sung cho phần đã có trong theme-setup.php (tắt emoji, xóa meta thừa)
 * và enqueue.php (tắt block CSS, tắt jQuery, defer JS).
 *
 * Require trong functions.php, đặt sau enqueue.php.
 *
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

/**
 * 1. Bỏ script/markup thừa ở frontend (giảm số request + dọn <head>)
 */
add_action('init', function () {
    if (is_admin()) return;
    wp_deregister_script('wp-embed');   // wp-embed.min.js — hiếm khi dùng
    wp_deregister_script('heartbeat');  // Heartbeat API — không cần ở frontend
});

// Dọn link thừa trong <head> (không trùng với những gì theme-setup.php đã xóa)
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('template_redirect', 'rest_output_link_header', 11);

/**
 * 2. LCP image: tự thêm fetchpriority="high" cho ảnh đã đặt loading="eager"
 *    + decoding="async" cho tất cả ảnh đính kèm.
 *    (Featured image ở single.php / single-broker_post.php / single-generic_post.php
 *     đã được render với loading="eager".)
 */
add_filter('wp_get_attachment_image_attributes', function ($attr) {
    if (empty($attr['decoding'])) {
        $attr['decoding'] = 'async';
    }
    if (!empty($attr['loading']) && $attr['loading'] === 'eager' && empty($attr['fetchpriority'])) {
        $attr['fetchpriority'] = 'high';
    }
    return $attr;
}, 10, 1);

/**
 * 3. Thêm decoding="async" cho ảnh trong nội dung bài (the_content)
 */
add_filter('wp_content_img_tag', function ($html) {
    if (strpos($html, 'decoding=') === false) {
        $html = str_replace('<img ', '<img decoding="async" ', $html);
    }
    return $html;
}, 10, 1);

/**
 * 4. Preload Google Fonts stylesheet (kéo font sớm hơn → giảm chờ render text)
 *    Hoạt động cùng preconnect đã có trong enqueue.php.
 */
add_action('wp_head', function () {
    echo '<link rel="preload" as="style" '
        . 'href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap">' . "\n";
}, 2);

/**
 * 5. Tắt prefetch DNS tự động của WP (tránh prefetch s.w.org không cần thiết)
 */
add_filter('wp_resource_hints', function ($hints, $relation_type) {
    if ($relation_type === 'dns-prefetch') {
        $hints = array_filter($hints, function ($h) {
            return strpos($h, 's.w.org') === false;
        });
    }
    return $hints;
}, 10, 2);

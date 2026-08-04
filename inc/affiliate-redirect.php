<?php
if (!defined('ABSPATH')) exit;

/**
 * ═══════════════════════════════════════════════════════════════
 *  AFFILIATE LINK REDIRECT — /go/{broker-slug}/
 * ═══════════════════════════════════════════════════════════════
 * Ẩn link affiliate gốc (dài, lộ mã tracking) đằng sau 1 link nội bộ
 * dạng /go/exness/ — khi click, site tự tra "Affiliate Link" đã lưu
 * của broker đó rồi chuyển hướng (302 redirect) sang link thật.
 *
 * Lợi ích:
 * - Đổi link affiliate chỉ cần sửa 1 chỗ (ô Affiliate Link trong
 *   wp-admin của broker) — không cần dò sửa từng bài viết.
 * - Đếm được số lượt click cho từng broker (cột "🔗 Clicks" trong
 *   Admin → Brokers).
 */

// ── Rewrite rule: /go/{broker-slug}/ ──
add_action('init', function () {
    add_rewrite_rule('^go/([^/]+)/?$', 'index.php?fxt_go_broker=$matches[1]', 'top');
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'fxt_go_broker';
    return $vars;
});

/**
 * Tự flush rewrite rules đúng 1 lần sau khi thêm rule mới, để bạn
 * không cần vào Settings → Permalinks → Save Changes bằng tay.
 */
add_action('init', function () {
    if (get_option('fxt_go_rewrite_flushed') !== FXT_VERSION) {
        flush_rewrite_rules();
        update_option('fxt_go_rewrite_flushed', FXT_VERSION);
    }
}, 999);

// ── Xử lý redirect ──
add_action('template_redirect', function () {
    $slug = get_query_var('fxt_go_broker');
    if (empty($slug)) return;

    // /go/default/ -> dùng "Default Affiliate Link" chung (không gắn broker cụ thể)
    if ($slug === 'default') {
        $url = get_theme_mod('fxt_default_affiliate_link', '');
        if ($url) {
            wp_redirect(esc_url_raw($url), 302);
        } else {
            wp_safe_redirect(home_url('/'));
        }
        exit;
    }

    $broker = get_page_by_path($slug, OBJECT, 'broker');
    if (!$broker || $broker->post_status !== 'publish') {
        wp_safe_redirect(home_url('/'));
        exit;
    }

    $url = get_post_meta($broker->ID, '_fxt_affiliate_link', true);
    if (!$url) {
        $url = get_theme_mod('fxt_default_affiliate_link', '');
    }

    if (!$url) {
        // Broker chưa có affiliate link nào -> quay lại trang review của broker đó
        wp_safe_redirect(get_permalink($broker->ID));
        exit;
    }

    fxt_increment_broker_click($broker->ID);

    wp_redirect(esc_url_raw($url), 302);
    exit;
}, 5);

/**
 * Tăng số click cho 1 broker — dùng UPDATE ... SET x = x+1 để tăng an
 * toàn (atomic) khi có nhiều người bấm cùng lúc; chỉ tạo mới record
 * nếu đây là click đầu tiên.
 */
function fxt_increment_broker_click($broker_id) {
    global $wpdb;
    $updated = $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = '_fxt_click_count'",
        $broker_id
    ));
    if (!$updated) {
        add_post_meta($broker_id, '_fxt_click_count', 1, true);
    }
}

/**
 * Helper: Lấy URL /go/ cho 1 broker — dùng trong template thay cho
 * link affiliate trực tiếp.
 */
function fxt_get_broker_go_url($broker_id) {
    $broker = get_post($broker_id);
    if (!$broker) return home_url('/');
    return home_url('/go/' . $broker->post_name . '/');
}

/**
 * URL /go/default/ — dùng cho CTA chung không gắn broker cụ thể
 * (ví dụ nút CTA mặc định ở Header).
 */
function fxt_get_default_go_url() {
    return home_url('/go/default/');
}

// ── Cột "Clicks" trong danh sách Broker (wp-admin) ──
add_filter('manage_broker_posts_columns', function ($columns) {
    $columns['fxt_clicks'] = '🔗 Clicks';
    return $columns;
});

add_action('manage_broker_posts_custom_column', function ($column, $post_id) {
    if ($column === 'fxt_clicks') {
        $count = (int) get_post_meta($post_id, '_fxt_click_count', true);
        echo esc_html(number_format_i18n($count));
    }
}, 10, 2);

add_filter('manage_edit-broker_sortable_columns', function ($columns) {
    $columns['fxt_clicks'] = 'fxt_clicks';
    return $columns;
});

add_action('pre_get_posts', function ($query) {
    if (!is_admin() || !$query->is_main_query()) return;
    if ($query->get('post_type') !== 'broker') return;
    if ($query->get('orderby') !== 'fxt_clicks') return;

    $query->set('meta_key', '_fxt_click_count');
    $query->set('orderby', 'meta_value_num');
});

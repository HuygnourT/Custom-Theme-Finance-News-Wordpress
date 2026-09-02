<?php
if (!defined('ABSPATH')) exit;

/**
 * ═══════════════════════════════════════════════════════════════
 *  AFFILIATE LINK REDIRECT — /go/{broker-slug}/ + biến thể
 * ═══════════════════════════════════════════════════════════════
 * Ẩn link affiliate gốc (dài, lộ mã tracking) đằng sau 1 link nội bộ
 * dạng /go/exness/ — khi click, site tự tra "Affiliate Link" đã lưu
 * của broker đó rồi chuyển hướng (302 redirect) sang link thật.
 *
 * BIẾN THỂ (MỚI): mỗi broker có thể có thêm nhiều link phụ dạng
 * /go/exness/android/, /go/exness/ios/, /go/exness/apk/... — mỗi biến
 * thể trỏ tới 1 URL thật KHÁC NHAU (VD: link tải app Android/iOS/APK),
 * tách biệt hoàn toàn khỏi Affiliate Link chính. Quản lý trong meta
 * box "🔗 Custom Links (Biến thể)" ở màn hình sửa Broker.
 *
 * Lợi ích:
 * - Đổi link affiliate chỉ cần sửa 1 chỗ (ô Affiliate Link trong
 *   wp-admin của broker) — không cần dò sửa từng bài viết.
 * - Đếm được số lượt click cho từng broker (cột "🔗 Clicks" trong
 *   Admin → Brokers).
 * - Đổi slug Broker vẫn không làm hỏng link /go/ cũ đã chia sẻ (tự
 *   redirect sang slug mới, xem phần "Ghi nhớ slug cũ" bên dưới).
 */

// ── Rewrite rules: /go/{broker-slug}/ và /go/{broker-slug}/{biến-thể}/ ──
add_action('init', function () {
    // Đăng ký rule có biến thể trước (cụ thể hơn)
    add_rewrite_rule('^go/([^/]+)/([^/]+)/?$', 'index.php?fxt_go_broker=$matches[1]&fxt_go_variant=$matches[2]', 'top');
    add_rewrite_rule('^go/([^/]+)/?$', 'index.php?fxt_go_broker=$matches[1]', 'top');
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'fxt_go_broker';
    $vars[] = 'fxt_go_variant';
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

// ── Ghi nhớ slug cũ khi Broker đổi slug (để link /go/{slug-cũ}/ không bị hỏng) ──
add_action('post_updated', function ($post_id, $post_after, $post_before) {
    if ($post_after->post_type !== 'broker') return;
    if (empty($post_before->post_name) || $post_after->post_name === $post_before->post_name) return;

    global $wpdb;
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_fxt_broker_old_slug' AND meta_value = %s LIMIT 1",
        $post_id, $post_before->post_name
    ));
    if (!$exists) {
        add_post_meta($post_id, '_fxt_broker_old_slug', $post_before->post_name, false);
    }
}, 10, 3);

// ── Xử lý redirect ──
add_action('template_redirect', function () {
    $slug = get_query_var('fxt_go_broker');
    if (empty($slug)) return;
    $variant = get_query_var('fxt_go_variant');

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
        // Thử tìm theo SLUG CŨ (broker đã đổi slug sau khi có tính năng /go/)
        global $wpdb;
        $old_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_fxt_broker_old_slug' AND meta_value = %s LIMIT 1",
            $slug
        ));
        $broker = $old_id ? get_post($old_id) : null;

        if (!$broker || $broker->post_type !== 'broker' || $broker->post_status !== 'publish') {
            wp_safe_redirect(home_url('/'));
            exit;
        }
    }

    // ── Có biến thể (VD: /go/exness/android/) ──
    if (!empty($variant)) {
        $custom_links = get_post_meta($broker->ID, '_fxt_broker_custom_links', true);
        if (!is_array($custom_links)) $custom_links = [];

        $target = '';
        foreach ($custom_links as $link) {
            if (!empty($link['slug']) && $link['slug'] === sanitize_title($variant)) {
                $target = $link['url'];
                break;
            }
        }

        if ($target) {
            fxt_increment_broker_click($broker->ID);
            wp_redirect(esc_url_raw($target), 302);
            exit;
        }

        // Biến thể không tồn tại -> để WordPress tự xử lý 404 tự nhiên
        // (không fallback nhầm sang link chính, tránh gây hiểu lầm)
        return;
    }

    // ── Không có biến thể -> dùng Affiliate Link chính ──
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
 * nếu đây là click đầu tiên. Áp dụng chung cho cả link chính lẫn biến thể.
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
 * Helper: Lấy URL /go/ cho 1 broker (link chính) — dùng trong template
 * thay cho link affiliate trực tiếp.
 */
function fxt_get_broker_go_url($broker_id) {
    $broker = get_post($broker_id);
    if (!$broker) return home_url('/');
    return home_url('/go/' . $broker->post_name . '/');
}

/**
 * Helper: Lấy URL /go/ cho 1 BIẾN THỂ của broker (VD: android, ios, apk).
 * Dùng khi bạn muốn chèn nút "Tải app Android" ngay trên trang review.
 */
function fxt_get_broker_variant_go_url($broker_id, $variant_slug) {
    $broker = get_post($broker_id);
    if (!$broker) return home_url('/');
    return home_url('/go/' . $broker->post_name . '/' . sanitize_title($variant_slug) . '/');
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

// ╔═══════════════════════════════════════════════════════════════╗
// ║  META BOX: Custom Links (Biến thể) — chỉ cho Broker            ║
// ╚═══════════════════════════════════════════════════════════════╝

add_action('add_meta_boxes', function () {
    add_meta_box('fxt_broker_custom_links', '🔗 Custom Links (Biến thể — Android/iOS/APK...)', 'fxt_broker_custom_links_html', 'broker', 'normal', 'default');
});

function fxt_broker_custom_links_html($post) {
    wp_nonce_field('fxt_broker_custom_links_meta', 'fxt_broker_custom_links_nonce');

    $links = get_post_meta($post->ID, '_fxt_broker_custom_links', true);
    if (!is_array($links)) $links = [];
    if (empty($links)) $links = [['slug' => '', 'url' => '']];

    $example_slug = $post->post_name ?: 'ten-broker';
    ?>
    <p style="color:#666;font-size:12px;margin-bottom:12px;">
        Tạo thêm link ẩn dạng <code>/go/<?php echo esc_html($example_slug); ?>/{biến-thể}/</code> — tách biệt với Affiliate Link chính ở meta box bên trên.<br>
        VD: nhập biến thể <code>android</code> + URL Google Play → truy cập <code>/go/<?php echo esc_html($example_slug); ?>/android/</code> sẽ tự chuyển sang link đó.
    </p>
    <div id="fxt-custom-links-wrap">
        <?php foreach ($links as $i => $l): ?>
        <div class="fxt-custom-link-row" style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
            <input type="text" name="fxt_custom_links[<?php echo $i; ?>][slug]" placeholder="android"
                   value="<?php echo esc_attr($l['slug'] ?? ''); ?>" style="width:160px;padding:6px;">
            <input type="url" name="fxt_custom_links[<?php echo $i; ?>][url]" placeholder="https://play.google.com/..."
                   value="<?php echo esc_attr($l['url'] ?? ''); ?>" style="flex:1;padding:6px;">
            <button type="button" class="button fxt-remove-custom-link" style="color:#d63638;">✕</button>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button" id="fxt-add-custom-link">+ Thêm biến thể</button>
    <script>
    (function() {
        var wrap = document.getElementById('fxt-custom-links-wrap');
        document.getElementById('fxt-add-custom-link').addEventListener('click', function() {
            var i = wrap.children.length;
            var row = document.createElement('div');
            row.className = 'fxt-custom-link-row';
            row.style.cssText = 'display:flex;gap:8px;align-items:center;margin-bottom:8px;';
            row.innerHTML = '<input type="text" name="fxt_custom_links[' + i + '][slug]" placeholder="android" style="width:160px;padding:6px;">' +
                '<input type="url" name="fxt_custom_links[' + i + '][url]" placeholder="https://..." style="flex:1;padding:6px;">' +
                '<button type="button" class="button fxt-remove-custom-link" style="color:#d63638;">✕</button>';
            wrap.appendChild(row);
        });
        wrap.addEventListener('click', function(e) {
            if (e.target.classList.contains('fxt-remove-custom-link')) {
                e.target.closest('.fxt-custom-link-row').remove();
            }
        });
    })();
    </script>
    <?php
}

add_action('save_post_broker', function ($post_id) {
    if (!isset($_POST['fxt_broker_custom_links_nonce']) || !wp_verify_nonce($_POST['fxt_broker_custom_links_nonce'], 'fxt_broker_custom_links_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $links = [];
    if (isset($_POST['fxt_custom_links']) && is_array($_POST['fxt_custom_links'])) {
        foreach ($_POST['fxt_custom_links'] as $l) {
            $slug = sanitize_title($l['slug'] ?? '');
            $url  = esc_url_raw($l['url'] ?? '');
            if ($slug === '' || $url === '') continue;
            $links[] = ['slug' => $slug, 'url' => $url];
        }
    }

    if (!empty($links)) {
        update_post_meta($post_id, '_fxt_broker_custom_links', $links);
    } else {
        delete_post_meta($post_id, '_fxt_broker_custom_links');
    }
});

<?php
/**
 * Custom Post Types - Đăng ký loại bài viết tùy chỉnh
 * 
 * - broker: Sàn giao dịch (pillar content)
 * - broker_post: Bài viết phụ hỗ trợ broker (content cluster/silo)
 *   URL: /broker-review/exness/huong-dan-nap-tien/
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

/**
 * Đăng ký Custom Post Type: Broker
 */
add_action('init', function () {

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
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-chart-area',
        'capability_type'    => 'post',
        'has_archive'        => 'brokers',
        'rewrite'            => [
            'slug'       => sanitize_title($broker_slug),
            'with_front' => false,
        ],
        'supports'           => [
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'custom-fields',
            'revisions',
        ],
    ];

    register_post_type('broker', $args);
});

/**
 * Đăng ký Taxonomy: Broker Category (Loại broker)
 */
add_action('init', function () {

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
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [
            'slug' => sanitize_title($broker_type_slug),
        ],
    ]);
});

/**
 * ╔═══════════════════════════════════════════════════════════════╗
 * ║  BROKER SUB-POST (Content Cluster / Silo)                    ║
 * ║                                                               ║
 * ║  Mục đích: Bài viết phụ hỗ trợ bài pillar broker chính       ║
 * ║  URL: /broker-review/{broker-slug}/{sub-post-slug}/           ║
 * ║  Ví dụ: /broker-review/exness/huong-dan-nap-tien/            ║
 * ╚═══════════════════════════════════════════════════════════════╝
 */
add_action('init', function () {

    $labels = [
        'name'               => 'Broker Posts',
        'singular_name'      => 'Broker Post',
        'menu_name'          => 'Broker Posts',
        'add_new'            => 'Thêm Broker Post',
        'add_new_item'       => 'Thêm Broker Post Mới',
        'edit_item'          => 'Sửa Broker Post',
        'new_item'           => 'Broker Post Mới',
        'view_item'          => 'Xem Broker Post',
        'search_items'       => 'Tìm Broker Post',
        'not_found'          => 'Không tìm thấy broker post',
        'not_found_in_trash' => 'Không có broker post trong thùng rác',
        'all_items'          => 'Tất cả Broker Posts',
    ];

    register_post_type('broker_post', [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=broker', // Hiện sub-menu dưới Brokers
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-media-text',
        'capability_type'    => 'post',
        'has_archive'        => false, // Không cần archive page
        'rewrite'            => false, // Tắt rewrite mặc định — ta tự viết
        'supports'           => [
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'custom-fields',
            'revisions',
        ],
    ]);
});

/**
 * Custom Rewrite Rules cho Broker Sub-Posts
 * 
 * URL pattern: /broker-review/exness/huong-dan-nap-tien/
 * 
 * QUAN TRỌNG: Rule này phải đăng ký SAU khi broker CPT đã register
 * và phải đặt TRƯỚC broker single rule (top priority)
 */
add_action('init', function () {
    $broker_slug = sanitize_title(get_theme_mod('fxt_broker_slug', 'broker-review'));

    // Rule: /broker-review/{broker-slug}/{sub-post-slug}/
    // Regex capture: broker-slug = $1, sub-post-slug = $2
    add_rewrite_rule(
        '^' . $broker_slug . '/([^/]+)/([^/]+)/?$',
        'index.php?broker_post=$matches[2]&fxt_parent_broker=$matches[1]',
        'top' // Priority: trước tất cả rules khác
    );
}, 20); // Priority 20 = chạy sau khi CPTs đã register (priority 10)

/**
 * Đăng ký query vars cho sub-post
 */
add_filter('query_vars', function ($vars) {
    $vars[] = 'fxt_parent_broker';
    return $vars;
});

/**
 * Fix permalink cho broker_post trong admin và frontend
 * Thay thế default permalink bằng silo URL
 */
add_filter('post_type_link', function ($post_link, $post) {
    if ($post->post_type !== 'broker_post') {
        return $post_link;
    }

    $parent_broker_id = get_post_meta($post->ID, '_fxt_parent_broker', true);
    if (!$parent_broker_id) {
        // Chưa gán broker cha → trả URL placeholder
        return $post_link;
    }

    $parent_broker = get_post($parent_broker_id);
    if (!$parent_broker) {
        return $post_link;
    }

    $broker_slug = sanitize_title(get_theme_mod('fxt_broker_slug', 'broker-review'));

    // Build URL: /broker-review/exness/huong-dan-nap-tien/
    return home_url('/' . $broker_slug . '/' . $parent_broker->post_name . '/' . $post->post_name . '/');
}, 10, 2);

/**
 * Resolve broker_post query — đảm bảo WP tìm đúng post
 * 
 * Vấn đề: WP mặc định tìm broker_post bằng slug, nhưng slug có thể trùng
 * giữa các broker khác nhau. Ta cần validate thêm parent broker.
 */
add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query()) return;

    $broker_post_slug = $query->get('broker_post');
    $parent_broker_slug = $query->get('fxt_parent_broker');

    if (!$broker_post_slug || !$parent_broker_slug) return;

    // Tìm broker cha theo slug
    $parent_broker = get_page_by_path($parent_broker_slug, OBJECT, 'broker');
    if (!$parent_broker) return;

    // Tìm broker_post theo slug VÀ parent broker
    $sub_posts = get_posts([
        'post_type'   => 'broker_post',
        'name'        => $broker_post_slug,
        'meta_key'    => '_fxt_parent_broker',
        'meta_value'  => $parent_broker->ID,
        'numberposts' => 1,
        'post_status' => 'publish',
    ]);

    if (!empty($sub_posts)) {
        // Set query để WP load đúng post
        $query->set('post_type', 'broker_post');
        $query->set('p', $sub_posts[0]->ID);
        $query->set('name', ''); // Clear name để dùng ID
        $query->set('broker_post', ''); // Clear custom var
    }
});

/**
 * Admin: Thêm column "Broker" trong danh sách broker_post
 */
add_filter('manage_broker_post_posts_columns', function ($columns) {
    $new_columns = [];
    foreach ($columns as $key => $val) {
        $new_columns[$key] = $val;
        if ($key === 'title') {
            $new_columns['parent_broker'] = 'Broker';
        }
    }
    return $new_columns;
});

add_action('manage_broker_post_posts_custom_column', function ($column, $post_id) {
    if ($column === 'parent_broker') {
        $parent_id = get_post_meta($post_id, '_fxt_parent_broker', true);
        if ($parent_id) {
            $parent = get_post($parent_id);
            if ($parent) {
                echo '<a href="' . get_edit_post_link($parent_id) . '"><strong>' . esc_html($parent->post_title) . '</strong></a>';
                return;
            }
        }
        echo '<span style="color:#999;">— Chưa gán —</span>';
    }
}, 10, 2);

/**
 * Admin: Sortable column
 */
add_filter('manage_edit-broker_post_sortable_columns', function ($columns) {
    $columns['parent_broker'] = 'parent_broker';
    return $columns;
});

/**
 * Admin: Filter dropdown — lọc broker_post theo broker cha
 */
add_action('restrict_manage_posts', function ($post_type) {
    if ($post_type !== 'broker_post') return;

    $brokers = get_posts([
        'post_type'   => 'broker',
        'numberposts' => -1,
        'orderby'     => 'title',
        'order'       => 'ASC',
    ]);

    if (empty($brokers)) return;

    $selected = $_GET['fxt_filter_broker'] ?? '';
    echo '<select name="fxt_filter_broker">';
    echo '<option value="">— Tất cả Broker —</option>';
    foreach ($brokers as $b) {
        printf(
            '<option value="%s" %s>%s</option>',
            $b->ID,
            selected($selected, $b->ID, false),
            esc_html($b->post_title)
        );
    }
    echo '</select>';
});

add_action('pre_get_posts', function ($query) {
    if (!is_admin() || !$query->is_main_query()) return;

    global $pagenow;
    if ($pagenow !== 'edit.php') return;
    if ($query->get('post_type') !== 'broker_post') return;

    $filter = $_GET['fxt_filter_broker'] ?? '';
    if ($filter) {
        $query->set('meta_key', '_fxt_parent_broker');
        $query->set('meta_value', intval($filter));
    }
});

/**
 * Flush rewrite rules khi activate theme
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

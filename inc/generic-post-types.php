<?php
/**
 * Generic Sub-Post CPT — Bài viết phụ đa chủ đề
 * 
 * Cho phép tạo bài viết phụ thuộc bất kỳ post/page/category nào
 * URL: /topic-slug/parent-slug/sub-post-slug/
 * 
 * Tính năng giống broker_post: CTA, Pros/Cons, Collapsible sections
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

// ╔═══════════════════════════════════════════════════════════════╗
// ║  REGISTER CPT: generic_post                                   ║
// ╚═══════════════════════════════════════════════════════════════╝

add_action('init', function () {

    $labels = [
        'name'               => 'Sub Posts',
        'singular_name'      => 'Sub Post',
        'menu_name'          => 'Sub Posts',
        'add_new'            => 'Thêm Sub Post',
        'add_new_item'       => 'Thêm Sub Post Mới',
        'edit_item'          => 'Sửa Sub Post',
        'new_item'           => 'Sub Post Mới',
        'view_item'          => 'Xem Sub Post',
        'search_items'       => 'Tìm Sub Post',
        'not_found'          => 'Không tìm thấy sub post',
        'not_found_in_trash' => 'Không có sub post trong thùng rác',
        'all_items'          => 'Tất cả Sub Posts',
    ];

    register_post_type('generic_post', [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-admin-page',
        'capability_type'    => 'post',
        'has_archive'        => false,
        'rewrite'            => false, // Custom rewrite rules
        'supports'           => [
            'title', 'editor', 'thumbnail', 'excerpt',
            'custom-fields', 'revisions',
        ],
    ]);

    // Taxonomy: Topic (chủ đề cho generic_post)
    register_taxonomy('sub_post_topic', 'generic_post', [
        'labels' => [
            'name'          => 'Chủ đề',
            'singular_name' => 'Chủ đề',
            'search_items'  => 'Tìm chủ đề',
            'all_items'     => 'Tất cả chủ đề',
            'edit_item'     => 'Sửa chủ đề',
            'add_new_item'  => 'Thêm chủ đề',
            'menu_name'     => 'Chủ đề',
        ],
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => false,
    ]);
});

/**
 * Tắt Gutenberg cho generic_post
 */
add_filter('use_block_editor_for_post_type', function ($use, $post_type) {
    if ($post_type === 'generic_post') return false;
    return $use;
}, 10, 2);

// ╔═══════════════════════════════════════════════════════════════╗
// ║  META BOX: Parent Post selector                               ║
// ╚═══════════════════════════════════════════════════════════════╝

add_action('add_meta_boxes', function () {
    add_meta_box(
        'fxt_generic_post_parent',
        '🔗 Parent Post (Bài cha)',
        'fxt_generic_post_parent_html',
        'generic_post',
        'side',
        'high'
    );

    add_meta_box(
        'fxt_generic_post_url_settings',
        '🌐 URL Settings',
        'fxt_generic_post_url_html',
        'generic_post',
        'side',
        'default'
    );
});

function fxt_generic_post_parent_html($post) {
    wp_nonce_field('fxt_generic_post_parent_meta', 'fxt_generic_post_parent_nonce');

    $parent_type = get_post_meta($post->ID, '_fxt_gp_parent_type', true) ?: 'post';
    $parent_id   = get_post_meta($post->ID, '_fxt_gp_parent_id', true);
    ?>
    <style>
        .fxt-gp-field { margin-bottom: 12px; }
        .fxt-gp-field label { display: block; font-weight: 600; font-size: 12px; margin-bottom: 4px; }
        .fxt-gp-field select, .fxt-gp-field input { width: 100%; padding: 6px; font-size: 13px; border: 1px solid #ccd0d4; border-radius: 4px; }
        .fxt-gp-hint { font-size: 11px; color: #888; margin-top: 4px; }
        .fxt-gp-preview { margin-top: 10px; padding: 8px; background: #f0f6fc; border: 1px solid #c3daf5; border-radius: 4px; font-size: 12px; }
    </style>

    <div class="fxt-gp-field">
        <label>Loại bài cha:</label>
        <select name="fxt_gp_parent_type" id="fxt_gp_parent_type">
            <option value="post" <?php selected($parent_type, 'post'); ?>>📝 Post (Bài viết)</option>
            <option value="page" <?php selected($parent_type, 'page'); ?>>📄 Page (Trang)</option>
            <option value="category" <?php selected($parent_type, 'category'); ?>>📁 Category</option>
            <option value="custom" <?php selected($parent_type, 'custom'); ?>>🔗 Custom URL slug</option>
        </select>
    </div>

    <div class="fxt-gp-field" id="fxt-gp-parent-post-field">
        <label>Chọn bài cha:</label>
        <select name="fxt_gp_parent_id" id="fxt_gp_parent_select">
            <option value="">— Chọn —</option>
        </select>
        <p class="fxt-gp-hint">Bài phụ sẽ nằm dưới URL của bài cha.</p>
    </div>

    <div class="fxt-gp-field" id="fxt-gp-custom-slug-field" style="display:none">
        <label>Custom parent slug:</label>
        <input type="text" name="fxt_gp_custom_slug" id="fxt_gp_custom_slug"
               value="<?php echo esc_attr(get_post_meta($post->ID, '_fxt_gp_custom_slug', true)); ?>"
               placeholder="e.g. huong-dan, kien-thuc">
        <p class="fxt-gp-hint">URL: yoursite.com/<strong>{slug}</strong>/bai-phu/</p>
    </div>

    <?php if ($parent_id || get_post_meta($post->ID, '_fxt_gp_custom_slug', true)): ?>
    <div class="fxt-gp-preview">
        🔗 URL: <code><?php echo esc_html(get_permalink($post->ID)); ?></code>
    </div>
    <?php endif; ?>

    <script>
    jQuery(function($) {
        var parentType = '<?php echo esc_js($parent_type); ?>';
        var parentId = '<?php echo esc_js($parent_id); ?>';

        function toggleFields() {
            var type = $('#fxt_gp_parent_type').val();
            if (type === 'custom') {
                $('#fxt-gp-parent-post-field').hide();
                $('#fxt-gp-custom-slug-field').show();
            } else {
                $('#fxt-gp-parent-post-field').show();
                $('#fxt-gp-custom-slug-field').hide();
                loadParentOptions(type);
            }
        }

        function loadParentOptions(type) {
            var $select = $('#fxt_gp_parent_select');
            $select.html('<option value="">Loading...</option>');

            var data = { action: 'fxt_load_parent_options', type: type, nonce: '<?php echo wp_create_nonce('fxt_load_parents'); ?>' };

            $.post(ajaxurl, data, function(response) {
                $select.html('<option value="">— Chọn —</option>');
                if (response.success && response.data) {
                    response.data.forEach(function(item) {
                        var selected = (item.id == parentId) ? ' selected' : '';
                        $select.append('<option value="' + item.id + '"' + selected + '>' + item.title + '</option>');
                    });
                }
            });
        }

        $('#fxt_gp_parent_type').on('change', toggleFields);
        toggleFields();
    });
    </script>
    <?php
}

function fxt_generic_post_url_html($post) {
    $url_prefix = get_post_meta($post->ID, '_fxt_gp_url_prefix', true);
    ?>
    <div class="fxt-gp-field">
        <label style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">URL Prefix (tùy chọn):</label>
        <input type="text" name="fxt_gp_url_prefix" value="<?php echo esc_attr($url_prefix); ?>"
               placeholder="e.g. guide, tutorial" style="width:100%; padding:6px; font-size:13px; border:1px solid #ccd0d4; border-radius:4px;">
        <p style="font-size:11px; color:#888; margin-top:4px;">
            Thêm prefix vào URL. Ví dụ: /<em>guide</em>/parent-slug/bai-phu/<br>
            Để trống = dùng slug mặc định.
        </p>
    </div>
    <?php
}

/**
 * AJAX: Load parent options based on type
 */
add_action('wp_ajax_fxt_load_parent_options', function () {
    check_ajax_referer('fxt_load_parents', 'nonce');

    $type = sanitize_text_field($_POST['type'] ?? 'post');
    $items = [];

    if ($type === 'post') {
        $posts = get_posts(['post_type' => 'post', 'numberposts' => 100, 'orderby' => 'title', 'order' => 'ASC']);
        foreach ($posts as $p) $items[] = ['id' => $p->ID, 'title' => $p->post_title];
    } elseif ($type === 'page') {
        $pages = get_posts(['post_type' => 'page', 'numberposts' => 100, 'orderby' => 'title', 'order' => 'ASC']);
        foreach ($pages as $p) $items[] = ['id' => $p->ID, 'title' => $p->post_title];
    } elseif ($type === 'category') {
        $cats = get_categories(['hide_empty' => false, 'orderby' => 'name']);
        foreach ($cats as $c) $items[] = ['id' => $c->term_id, 'title' => $c->name . ' (/' . $c->slug . '/)'];
    }

    wp_send_json_success($items);
});

/**
 * Save parent meta
 */
add_action('save_post_generic_post', function ($post_id) {
    if (!isset($_POST['fxt_generic_post_parent_nonce']) ||
        !wp_verify_nonce($_POST['fxt_generic_post_parent_nonce'], 'fxt_generic_post_parent_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $parent_type = sanitize_text_field($_POST['fxt_gp_parent_type'] ?? 'post');
    update_post_meta($post_id, '_fxt_gp_parent_type', $parent_type);

    if ($parent_type === 'custom') {
        update_post_meta($post_id, '_fxt_gp_custom_slug', sanitize_title($_POST['fxt_gp_custom_slug'] ?? ''));
        delete_post_meta($post_id, '_fxt_gp_parent_id');
    } else {
        $parent_id = intval($_POST['fxt_gp_parent_id'] ?? 0);
        if ($parent_id > 0) {
            update_post_meta($post_id, '_fxt_gp_parent_id', $parent_id);
        } else {
            delete_post_meta($post_id, '_fxt_gp_parent_id');
        }
        delete_post_meta($post_id, '_fxt_gp_custom_slug');
    }

    if (isset($_POST['fxt_gp_url_prefix'])) {
        update_post_meta($post_id, '_fxt_gp_url_prefix', sanitize_title($_POST['fxt_gp_url_prefix']));
    }
});

// ╔═══════════════════════════════════════════════════════════════╗
// ║  REWRITE RULES & PERMALINK                                    ║
// ╚═══════════════════════════════════════════════════════════════╝

/**
 * Build permalink for generic_post
 */
add_filter('post_type_link', function ($post_link, $post) {
    if ($post->post_type !== 'generic_post') return $post_link;

    $parent_type = get_post_meta($post->ID, '_fxt_gp_parent_type', true);
    $parent_slug = '';

    if ($parent_type === 'custom') {
        $parent_slug = get_post_meta($post->ID, '_fxt_gp_custom_slug', true);
    } else {
        $parent_id = get_post_meta($post->ID, '_fxt_gp_parent_id', true);
        if ($parent_id) {
            if ($parent_type === 'category') {
                $cat = get_category($parent_id);
                if ($cat) $parent_slug = $cat->slug;
            } else {
                $parent_post = get_post($parent_id);
                if ($parent_post) $parent_slug = $parent_post->post_name;
            }
        }
    }

    if (!$parent_slug) return $post_link;

    $url_prefix = get_post_meta($post->ID, '_fxt_gp_url_prefix', true);
    $parts = [];
    if ($url_prefix) $parts[] = $url_prefix;
    $parts[] = $parent_slug;
    $parts[] = $post->post_name;

    return home_url('/' . implode('/', $parts) . '/');
}, 10, 2);

/**
 * Dynamic rewrite rules for generic_post
 * Vì URL linh hoạt, ta dùng approach: thêm rule khi save post
 */
add_action('save_post_generic_post', function ($post_id) {
    // Schedule flush after save
    if (!wp_next_scheduled('fxt_flush_rewrite_rules')) {
        wp_schedule_single_event(time() + 5, 'fxt_flush_rewrite_rules');
    }
}, 20);

add_action('fxt_flush_rewrite_rules', function () {
    fxt_register_generic_post_rules();
    flush_rewrite_rules();
});

/**
 * Register rewrite rules cho tất cả generic_posts đã publish
 */
function fxt_register_generic_post_rules() {
    $posts = get_posts([
        'post_type'   => 'generic_post',
        'numberposts' => -1,
        'post_status' => 'publish',
    ]);

    foreach ($posts as $p) {
        $parent_type = get_post_meta($p->ID, '_fxt_gp_parent_type', true);
        $parent_slug = '';

        if ($parent_type === 'custom') {
            $parent_slug = get_post_meta($p->ID, '_fxt_gp_custom_slug', true);
        } else {
            $parent_id = get_post_meta($p->ID, '_fxt_gp_parent_id', true);
            if ($parent_id) {
                if ($parent_type === 'category') {
                    $cat = get_category($parent_id);
                    if ($cat) $parent_slug = $cat->slug;
                } else {
                    $pp = get_post($parent_id);
                    if ($pp) $parent_slug = $pp->post_name;
                }
            }
        }

        if (!$parent_slug) continue;

        $url_prefix = get_post_meta($p->ID, '_fxt_gp_url_prefix', true);
        $pattern = '';
        if ($url_prefix) $pattern .= preg_quote($url_prefix) . '/';
        $pattern .= preg_quote($parent_slug) . '/' . preg_quote($p->post_name);

        add_rewrite_rule(
            '^' . $pattern . '/?$',
            'index.php?generic_post=' . $p->post_name . '&p=' . $p->ID,
            'top'
        );
    }
}

// Register rules on init
add_action('init', 'fxt_register_generic_post_rules', 30);

/**
 * Admin columns cho generic_post
 */
add_filter('manage_generic_post_posts_columns', function ($columns) {
    $new = [];
    foreach ($columns as $key => $val) {
        $new[$key] = $val;
        if ($key === 'title') {
            $new['gp_parent'] = 'Parent';
        }
    }
    return $new;
});

add_action('manage_generic_post_posts_custom_column', function ($column, $post_id) {
    if ($column === 'gp_parent') {
        $type = get_post_meta($post_id, '_fxt_gp_parent_type', true);
        $parent_id = get_post_meta($post_id, '_fxt_gp_parent_id', true);
        $custom_slug = get_post_meta($post_id, '_fxt_gp_custom_slug', true);

        if ($type === 'custom' && $custom_slug) {
            echo '🔗 /' . esc_html($custom_slug) . '/';
        } elseif ($parent_id) {
            if ($type === 'category') {
                $cat = get_category($parent_id);
                echo $cat ? '📁 ' . esc_html($cat->name) : '<span style="color:#999">—</span>';
            } else {
                $parent = get_post($parent_id);
                echo $parent ? '<a href="' . get_edit_post_link($parent_id) . '">' . esc_html($parent->post_title) . '</a>' : '<span style="color:#999">—</span>';
            }
        } else {
            echo '<span style="color:#999">— Chưa gán —</span>';
        }
    }
}, 10, 2);

// ╔═══════════════════════════════════════════════════════════════╗
// ║  HELPER FUNCTIONS                                             ║
// ╚═══════════════════════════════════════════════════════════════╝

/**
 * Lấy parent info cho generic_post
 */
function fxt_get_generic_parent($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $type = get_post_meta($post_id, '_fxt_gp_parent_type', true);
    $parent_id = get_post_meta($post_id, '_fxt_gp_parent_id', true);
    $custom_slug = get_post_meta($post_id, '_fxt_gp_custom_slug', true);

    if ($type === 'custom' && $custom_slug) {
        return [
            'type'      => 'custom',
            'title'     => ucfirst(str_replace('-', ' ', $custom_slug)),
            'permalink' => home_url('/' . $custom_slug . '/'),
            'slug'      => $custom_slug,
        ];
    }

    if (!$parent_id) return null;

    if ($type === 'category') {
        $cat = get_category($parent_id);
        if (!$cat) return null;
        return [
            'type'      => 'category',
            'title'     => $cat->name,
            'permalink' => get_category_link($cat->term_id),
            'slug'      => $cat->slug,
        ];
    }

    $parent = get_post($parent_id);
    if (!$parent || $parent->post_status !== 'publish') return null;
    return [
        'type'      => $type ?: 'post',
        'title'     => $parent->post_title,
        'permalink' => get_permalink($parent->ID),
        'slug'      => $parent->post_name,
        'ID'        => $parent->ID,
    ];
}

/**
 * Lấy sibling sub-posts cùng parent
 */
function fxt_get_generic_siblings($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $type = get_post_meta($post_id, '_fxt_gp_parent_type', true);
    $parent_id = get_post_meta($post_id, '_fxt_gp_parent_id', true);
    $custom_slug = get_post_meta($post_id, '_fxt_gp_custom_slug', true);

    $meta_query = [];
    if ($type === 'custom') {
        $meta_query[] = ['key' => '_fxt_gp_parent_type', 'value' => 'custom'];
        $meta_query[] = ['key' => '_fxt_gp_custom_slug', 'value' => $custom_slug];
    } elseif ($parent_id) {
        $meta_query[] = ['key' => '_fxt_gp_parent_type', 'value' => $type];
        $meta_query[] = ['key' => '_fxt_gp_parent_id', 'value' => $parent_id];
    } else {
        return [];
    }

    return get_posts([
        'post_type'   => 'generic_post',
        'meta_query'  => $meta_query,
        'numberposts' => 20,
        'post_status' => 'publish',
        'exclude'     => [$post_id],
        'orderby'     => 'title',
        'order'       => 'ASC',
    ]);
}

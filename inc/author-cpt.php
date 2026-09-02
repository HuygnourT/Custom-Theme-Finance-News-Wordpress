<?php
if (!defined('ABSPATH')) exit;

/**
 * ═══════════════════════════════════════════════════════════════
 *  AUTHOR CPT — Hồ sơ tác giả tập trung (1 nguồn) + Đa tác giả/bài
 * ═══════════════════════════════════════════════════════════════
 * Thay thế hệ thống "Custom Author" cũ (1 tác giả nhập trực tiếp
 * từng bài) áp dụng cho Broker + Broker Post + Sub Post (generic_post).
 * Giờ tác giả là 1 hồ sơ riêng (CPT fxt_author) — sửa 1 lần, dùng lại
 * ở mọi bài họ đứng tên.
 */

// ── Đăng ký CPT: Author ──
add_action('init', function () {
    $slug = sanitize_title(get_theme_mod('fxt_author_slug', 'authors'));

    register_post_type('fxt_author', [
        'labels' => [
            'name'               => 'Authors',
            'singular_name'      => 'Author',
            'menu_name'          => '👤 Authors',
            'add_new_item'       => 'Thêm Tác giả mới',
            'edit_item'          => 'Sửa Tác giả',
            'all_items'          => 'Tất cả Tác giả',
            'search_items'       => 'Tìm Tác giả',
            'not_found'          => 'Không tìm thấy Tác giả nào',
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => $slug, 'with_front' => false],
        'supports'      => ['title', 'editor', 'thumbnail'],
        'menu_icon'     => 'dashicons-businessperson',
        'menu_position' => 21,
        'show_in_rest'  => true,
    ]);
});

/**
 * Nội dung gợi ý cho các field: Title = Tên, Featured Image = Avatar,
 * Nội dung chính (editor) = Bio đầy đủ (hiện ở trang riêng tác giả).
 */
add_action('edit_form_after_title', function ($post) {
    if ($post->post_type !== 'fxt_author') return;
    echo '<p style="color:#888;font-style:italic;margin:8px 0 0;">📝 Nội dung bên dưới = Bio đầy đủ (hiện ở trang riêng của tác giả). Avatar: dùng ô "Chọn ảnh" trong khung 📋 Thông tin bổ sung bên dưới — nếu để trống sẽ tự dùng Featured Image ở cột bên phải.</p>';
});

// ── Enqueue WordPress media uploader cho Avatar upload ──
add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, ['post.php', 'post-new.php'])) return;
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'fxt_author') {
        wp_enqueue_media();
    }
});

// ── Meta box: Chức danh + Mô tả ngắn + Avatar riêng ──
add_action('add_meta_boxes', function () {
    add_meta_box('fxt_author_details', '📋 Thông tin bổ sung', 'fxt_author_details_html', 'fxt_author', 'normal', 'high');
});

function fxt_author_details_html($post) {
    wp_nonce_field('fxt_author_details_meta', 'fxt_author_details_nonce');
    $job_title  = get_post_meta($post->ID, '_fxt_author_job_title', true);
    $short_desc = get_post_meta($post->ID, '_fxt_author_short_desc', true);
    $avatar_id  = get_post_meta($post->ID, '_fxt_author_avatar', true);
    $avatar_url = $avatar_id ? wp_get_attachment_image_url($avatar_id, 'thumbnail') : '';
    ?>
    <div style="margin-bottom:16px;padding:14px;background:#f0f6fc;border:1px solid #c3daf5;border-radius:6px;">
        <label style="display:block;font-weight:700;margin-bottom:10px;color:#1e3a5f;">🖼 Avatar (tùy chọn — tách biệt với Featured Image)</label>
        <div style="display:flex;align-items:center;gap:14px;">
            <div id="fxt-author-avatar-preview-wrap" style="width:72px;height:72px;border:2px solid #ccd0d4;border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#fff;flex-shrink:0;">
                <img id="fxt-author-avatar-preview-img" src="<?php echo esc_url($avatar_url); ?>" alt="" style="max-width:100%;max-height:100%;<?php echo $avatar_url ? '' : 'display:none;'; ?>">
                <span id="fxt-author-avatar-placeholder" style="font-size:24px;<?php echo $avatar_url ? 'display:none;' : ''; ?>">🖼</span>
            </div>
            <div>
                <input type="hidden" id="fxt_author_avatar" name="fxt_author_avatar" value="<?php echo esc_attr($avatar_id); ?>">
                <button type="button" class="button button-secondary" id="fxt-upload-author-avatar-btn">📤 Chọn ảnh</button>
                <button type="button" class="button" id="fxt-remove-author-avatar-btn" style="margin-left:6px;color:#d63638;<?php echo $avatar_id ? '' : 'display:none;'; ?>">✕ Xóa ảnh</button>
                <p style="margin:8px 0 0;font-size:12px;color:#666;font-style:italic;">Ảnh vuông (khuyến nghị 200×200px). Để trống → tự dùng Featured Image; nếu cũng không có → hiện chữ cái đầu tên.</p>
            </div>
        </div>
    </div>
    <script>
    (function($) {
        var mediaFrame;
        $('#fxt-upload-author-avatar-btn').on('click', function(e) {
            e.preventDefault();
            if (mediaFrame) { mediaFrame.open(); return; }
            mediaFrame = wp.media({
                title: 'Chọn Avatar Tác giả',
                button: { text: 'Dùng ảnh này làm Avatar' },
                multiple: false,
                library: { type: 'image' }
            });
            mediaFrame.on('select', function() {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                $('#fxt_author_avatar').val(attachment.id);
                var url = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
                $('#fxt-author-avatar-preview-img').attr('src', url).show();
                $('#fxt-author-avatar-placeholder').hide();
                $('#fxt-remove-author-avatar-btn').show();
            });
            mediaFrame.open();
        });
        $('#fxt-remove-author-avatar-btn').on('click', function() {
            $('#fxt_author_avatar').val('');
            $('#fxt-author-avatar-preview-img').attr('src', '').hide();
            $('#fxt-author-avatar-placeholder').show();
            $(this).hide();
        });
    })(jQuery);
    </script>
    <p>
        <label for="fxt_author_job_title" style="display:block;font-weight:600;margin-bottom:4px;">Chức danh</label>
        <input type="text" id="fxt_author_job_title" name="fxt_author_job_title" class="widefat"
               value="<?php echo esc_attr($job_title); ?>" placeholder="VD: Senior Forex Analyst">
    </p>
    <p>
        <label for="fxt_author_short_desc" style="display:block;font-weight:600;margin-bottom:4px;margin-top:12px;">Mô tả ngắn (hiện ở card/byline)</label>
        <textarea id="fxt_author_short_desc" name="fxt_author_short_desc" class="widefat" rows="3"
                  placeholder="1-2 câu ngắn gọn — khác với Bio đầy đủ ở nội dung chính bên trên"><?php echo esc_textarea($short_desc); ?></textarea>
    </p>
    <?php
}

add_action('save_post_fxt_author', function ($post_id) {
    if (!isset($_POST['fxt_author_details_nonce']) || !wp_verify_nonce($_POST['fxt_author_details_nonce'], 'fxt_author_details_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['fxt_author_job_title'])) {
        update_post_meta($post_id, '_fxt_author_job_title', sanitize_text_field($_POST['fxt_author_job_title']));
    }
    if (isset($_POST['fxt_author_short_desc'])) {
        update_post_meta($post_id, '_fxt_author_short_desc', sanitize_textarea_field($_POST['fxt_author_short_desc']));
    }
    if (isset($_POST['fxt_author_avatar'])) {
        $avatar_id = intval($_POST['fxt_author_avatar']);
        if ($avatar_id > 0) {
            update_post_meta($post_id, '_fxt_author_avatar', $avatar_id);
        } else {
            delete_post_meta($post_id, '_fxt_author_avatar');
        }
    }
});

// ── Meta box: Chọn (nhiều) Tác giả cho Broker / Broker Post / Sub Post ──
add_action('add_meta_boxes', function () {
    foreach (['broker', 'broker_post', 'generic_post'] as $pt) {
        add_meta_box('fxt_post_authors', '👥 Tác giả bài viết (chọn 1 hoặc nhiều)', 'fxt_post_authors_meta_box_html', $pt, 'side', 'high');
    }
});

function fxt_post_authors_meta_box_html($post) {
    wp_nonce_field('fxt_post_authors_meta', 'fxt_post_authors_nonce');

    $selected = get_post_meta($post->ID, '_fxt_post_authors', true);
    if (!is_array($selected)) $selected = [];

    $authors = get_posts([
        'post_type'      => 'fxt_author',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    ]);

    if (empty($authors)) {
        echo '<p style="font-size:12px;color:#888;">Chưa có Tác giả nào. <a href="' . esc_url(admin_url('post-new.php?post_type=fxt_author')) . '" target="_blank">Thêm tác giả mới →</a></p>';
        return;
    }

    echo '<div style="max-height:220px;overflow-y:auto;border:1px solid #ddd;padding:8px;border-radius:4px;background:#fff;">';
    foreach ($authors as $author) {
        $checked = in_array($author->ID, $selected) ? 'checked' : '';
        echo '<label style="display:block;margin-bottom:6px;font-size:13px;">';
        echo '<input type="checkbox" name="fxt_post_authors[]" value="' . esc_attr($author->ID) . '" ' . $checked . '> ';
        echo esc_html($author->post_title);
        echo '</label>';
    }
    echo '</div>';
    echo '<p style="font-size:11px;color:#888;margin-top:8px;"><a href="' . esc_url(admin_url('edit.php?post_type=fxt_author')) . '" target="_blank">Quản lý danh sách Tác giả →</a></p>';
}

foreach (['save_post_broker', 'save_post_broker_post', 'save_post_generic_post'] as $hook) {
    add_action($hook, function ($post_id) {
        if (!isset($_POST['fxt_post_authors_nonce']) || !wp_verify_nonce($_POST['fxt_post_authors_nonce'], 'fxt_post_authors_meta')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $ids = isset($_POST['fxt_post_authors']) ? array_map('intval', (array) $_POST['fxt_post_authors']) : [];
        $ids = array_values(array_unique(array_filter($ids)));

        if (!empty($ids)) {
            update_post_meta($post_id, '_fxt_post_authors', $ids);
        } else {
            delete_post_meta($post_id, '_fxt_post_authors');
        }
    });
}

/**
 * Helper: Lấy danh sách tác giả (mảng) của 1 bài Broker / Broker Post
 */
function fxt_get_post_authors($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();

    $ids = get_post_meta($post_id, '_fxt_post_authors', true);
    if (!is_array($ids) || empty($ids)) return [];

    $authors = [];
    foreach ($ids as $id) {
        $a = get_post($id);
        if (!$a || $a->post_type !== 'fxt_author' || $a->post_status !== 'publish') continue;

        $authors[] = [
            'id'         => $a->ID,
            'name'       => $a->post_title,
            'url'        => get_permalink($a->ID),
            'job_title'  => get_post_meta($a->ID, '_fxt_author_job_title', true),
            'short_desc' => get_post_meta($a->ID, '_fxt_author_short_desc', true),
            'avatar'     => fxt_get_author_avatar_url($a->ID),
        ];
    }
    return $authors;
}

/**
 * Helper: Lấy URL avatar của Tác giả — ưu tiên Avatar riêng (upload trong
 * meta box) → fallback Featured Image → rỗng (template sẽ hiện chữ cái đầu)
 */
function fxt_get_author_avatar_url($author_id, $size = 'thumbnail') {
    $avatar_id = get_post_meta($author_id, '_fxt_author_avatar', true);
    if ($avatar_id) {
        $url = wp_get_attachment_image_url($avatar_id, $size);
        if ($url) return $url;
    }
    return get_the_post_thumbnail_url($author_id, $size) ?: '';
}

/**
 * Render: Dòng byline ngắn gọn ("Tên — Chức danh, Tên 2 — Chức danh 2")
 * dùng trong post-meta trên đầu bài
 */
function fxt_render_author_byline($post_id = null) {
    $authors = fxt_get_post_authors($post_id);
    if (empty($authors)) return;

    $parts = array_map(function ($a) {
        $html = '<a href="' . esc_url($a['url']) . '">' . esc_html($a['name']) . '</a>';
        if (!empty($a['job_title'])) {
            $html .= '<em style="opacity:.7"> — ' . esc_html($a['job_title']) . '</em>';
        }
        return $html;
    }, $authors);

    echo implode(', ', $parts);
}

/**
 * Render: Box đầy đủ (avatar + tên + chức danh + mô tả ngắn) — 1 box/tác giả,
 * hiện ở cuối bài. Hỗ trợ nhiều tác giả (lặp nhiều box).
 */
function fxt_render_author_box($post_id = null) {
    $authors = fxt_get_post_authors($post_id);
    if (empty($authors)) return;

    echo '<div class="author-box-group">';
    foreach ($authors as $a) {
        echo '<div class="author-box">';
        echo '<a href="' . esc_url($a['url']) . '" class="author-avatar">';
        if ($a['avatar']) {
            echo '<img src="' . esc_url($a['avatar']) . '" alt="' . esc_attr($a['name']) . '" width="64" height="64">';
        } else {
            echo '<span class="author-initial">' . esc_html(mb_substr($a['name'], 0, 1)) . '</span>';
        }
        echo '</a>';
        echo '<div class="author-info">';
        echo '<h4 class="author-name"><a href="' . esc_url($a['url']) . '">' . esc_html($a['name']) . '</a></h4>';
        if ($a['job_title']) {
            echo '<p class="author-title">' . esc_html($a['job_title']) . '</p>';
        }
        if ($a['short_desc']) {
            echo '<p class="author-bio">' . esc_html($a['short_desc']) . '</p>';
        }
        echo '</div></div>';
    }
    echo '</div>';
}

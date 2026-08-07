<?php
/**
 * Meta Boxes - Custom Fields cho Broker + Broker Post
 * 
 * FIX 1: Thêm 'classic-editor' support để Gutenberg không block meta box
 * FIX 2: Lưu pros/cons dùng sanitize_textarea_field để giữ line breaks
 * NEW: Broker Sections với wp_editor() cho content & hidden detail
 * NEW: Broker Post meta box (parent broker selector)
 * NEW: Custom Author cho broker (pillar) + broker_post + generic_post
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

/**
 * Enqueue WordPress media uploader cho broker icon upload
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, ['post.php', 'post-new.php'])) return;
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'broker') {
        wp_enqueue_media();
    }
});

/**
 * Đăng ký Meta Box trong trang editor Broker
 */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'fxt_broker_details',
        'Information Broker',
        'fxt_broker_meta_box_html',
        'broker',
        'normal',
        'high'
    );

    // ╔═══════════════════════════════════════════════════════════╗
    // ║  BROKER POST: Meta box chọn broker cha                   ║
    // ╚═══════════════════════════════════════════════════════════╝
    add_meta_box(
        'fxt_broker_post_parent',
        '🔗 Broker Cha (Bắt buộc)',
        'fxt_broker_post_parent_html',
        'broker_post',
        'side',
        'high'
    );
});

/**
 * Render HTML cho meta box chính (không đổi)
 */
function fxt_broker_meta_box_html($post) {
    wp_nonce_field('fxt_broker_meta', 'fxt_broker_meta_nonce');

    $fields = [
        'short_name'     => get_post_meta($post->ID, '_fxt_broker_short_name', true),
        'rating'         => get_post_meta($post->ID, '_fxt_rating', true),
        'spread'         => get_post_meta($post->ID, '_fxt_spread', true),
        'leverage'       => get_post_meta($post->ID, '_fxt_leverage', true),
        'min_deposit'    => get_post_meta($post->ID, '_fxt_min_deposit', true),
        'regulation'     => get_post_meta($post->ID, '_fxt_regulation', true),
        'founded'        => get_post_meta($post->ID, '_fxt_founded', true),
        'platforms'      => get_post_meta($post->ID, '_fxt_platforms', true),
        'affiliate_link' => get_post_meta($post->ID, '_fxt_affiliate_link', true),
        'website_url'    => get_post_meta($post->ID, '_fxt_website_url', true),
        'pros'           => get_post_meta($post->ID, '_fxt_pros', true),
        'cons'           => get_post_meta($post->ID, '_fxt_cons', true),
    ];
    ?>

    <style>
        .fxt-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .fxt-meta-field { margin-bottom: 12px; }
        .fxt-meta-field label { display: block; font-weight: 600; margin-bottom: 4px; color: #1e3a5f; }
        .fxt-meta-field input, .fxt-meta-field textarea { width: 100%; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px; }
        .fxt-meta-field input:focus, .fxt-meta-field textarea:focus { border-color: #2271b1; outline: none; box-shadow: 0 0 0 1px #2271b1; }
        .fxt-meta-section { background: #f6f7f7; padding: 15px; border-radius: 6px; margin-top: 15px; }
        .fxt-meta-section h4 { margin: 0 0 10px; color: #1e3a5f; }
        .fxt-meta-hint { font-size: 12px; color: #666; margin-top: 4px; font-style: italic; }
    </style>

    <?php
    // Icon data
    $broker_icon_id  = get_post_meta($post->ID, '_fxt_broker_icon', true);
    $broker_icon_url = $broker_icon_id ? wp_get_attachment_image_url($broker_icon_id, 'thumbnail') : '';
    ?>
    <div style="margin-bottom:16px;padding:14px;background:#f0f6fc;border:1px solid #c3daf5;border-radius:6px;">
        <label style="display:block;font-weight:700;margin-bottom:10px;color:#1e3a5f;">🖼 Broker Icon / Logo (tách biệt với Featured Image)</label>
        <div style="display:flex;align-items:center;gap:14px;">
            <div id="fxt-icon-preview-wrap" style="width:72px;height:72px;border:2px solid #ccd0d4;border-radius:6px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#fff;flex-shrink:0;">
                <img id="fxt-icon-preview-img" src="<?php echo esc_url($broker_icon_url); ?>" alt="" style="max-width:100%;max-height:100%;<?php echo $broker_icon_url ? '' : 'display:none;'; ?>">
                <span id="fxt-icon-placeholder" style="font-size:24px;<?php echo $broker_icon_url ? 'display:none;' : ''; ?>">🖼</span>
            </div>
            <div>
                <input type="hidden" id="fxt_broker_icon" name="fxt_broker_icon" value="<?php echo esc_attr($broker_icon_id); ?>">
                <button type="button" class="button button-secondary" id="fxt-upload-icon-btn">📤 Upload Icon</button>
                <button type="button" class="button" id="fxt-remove-icon-btn" style="margin-left:6px;color:#d63638;<?php echo $broker_icon_id ? '' : 'display:none;'; ?>">✕ Xóa Icon</button>
                <p style="margin:8px 0 0;font-size:12px;color:#666;font-style:italic;">Ảnh vuông nhỏ (khuyến nghị 200×200px). Hiển thị trong cards, bảng so sánh, hero section.<br>Nếu để trống → sẽ dùng Featured Image làm fallback.</p>
            </div>
        </div>
    </div>
    <script>
    (function($) {
        var mediaFrame;
        $('#fxt-upload-icon-btn').on('click', function(e) {
            e.preventDefault();
            if (mediaFrame) { mediaFrame.open(); return; }
            mediaFrame = wp.media({
                title: 'Chọn Broker Icon',
                button: { text: 'Dùng ảnh này làm icon' },
                multiple: false,
                library: { type: 'image' }
            });
            mediaFrame.on('select', function() {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                $('#fxt_broker_icon').val(attachment.id);
                var url = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
                $('#fxt-icon-preview-img').attr('src', url).show();
                $('#fxt-icon-placeholder').hide();
                $('#fxt-remove-icon-btn').show();
            });
            mediaFrame.open();
        });
        $('#fxt-remove-icon-btn').on('click', function() {
            $('#fxt_broker_icon').val('');
            $('#fxt-icon-preview-img').attr('src', '').hide();
            $('#fxt-icon-placeholder').show();
            $(this).hide();
        });
    })(jQuery);
    </script>

    <div style="margin-bottom:16px;padding:14px;background:#fef8e8;border:1px solid #f0dca0;border-radius:6px;">
        <label for="fxt_broker_short_name" style="display:block;font-weight:700;margin-bottom:6px;color:#1e3a5f;">🏷 Tên hiển thị ngắn (dùng cho nút CTA, Card, Sidebar...)</label>
        <input type="text" id="fxt_broker_short_name" name="fxt_broker_short_name"
               value="<?php echo esc_attr($fields['short_name']); ?>"
               placeholder="VD: Exness" style="width:100%;padding:8px;border:1px solid #ccd0d4;border-radius:4px;">
        <p class="fxt-meta-hint" style="margin-top:6px;">Để trống = tự động dùng Tiêu đề bài viết. Dùng field này khi Tiêu đề có thêm chữ SEO (VD: "Exness Review 2026") nhưng bạn chỉ muốn hiện "Exness" ở nút CTA / Card / Sidebar.</p>
    </div>

    <div class="fxt-meta-grid">
        <div>
            <div class="fxt-meta-field">
                <label for="fxt_rating">⭐ Rating (0–10)</label>
                <input type="number" id="fxt_rating" name="fxt_rating"
                       value="<?php echo esc_attr($fields['rating']); ?>"
                       min="0" max="10" step="0.1" placeholder="8.5">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_spread">📊 Spread (pips)</label>
                <input type="text" id="fxt_spread" name="fxt_spread"
                       value="<?php echo esc_attr($fields['spread']); ?>"
                       placeholder="Từ 0.0 pips">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_leverage">📈 Maximum Leverage</label>
                <input type="text" id="fxt_leverage" name="fxt_leverage"
                       value="<?php echo esc_attr($fields['leverage']); ?>"
                       placeholder="1:2000">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_min_deposit">💰 Minimum Deposit</label>
                <input type="text" id="fxt_min_deposit" name="fxt_min_deposit"
                       value="<?php echo esc_attr($fields['min_deposit']); ?>"
                       placeholder="$1">
            </div>
        </div>
        <div>
            <div class="fxt-meta-field">
                <label for="fxt_regulation">🏛 Regulation</label>
                <input type="text" id="fxt_regulation" name="fxt_regulation"
                       value="<?php echo esc_attr($fields['regulation']); ?>"
                       placeholder="FCA, CySEC, ASIC...">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_founded">📅 Year Founded</label>
                <input type="text" id="fxt_founded" name="fxt_founded"
                       value="<?php echo esc_attr($fields['founded']); ?>"
                       placeholder="2008">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_platforms">🖥 Trading Platforms</label>
                <input type="text" id="fxt_platforms" name="fxt_platforms"
                       value="<?php echo esc_attr($fields['platforms']); ?>"
                       placeholder="MT4, MT5, cTrader...">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_affiliate_link">🔗 Affiliate Link</label>
                <input type="url" id="fxt_affiliate_link" name="fxt_affiliate_link"
                       value="<?php echo esc_attr($fields['affiliate_link']); ?>"
                       placeholder="https://exness.com/a/xxxxx">
            </div>
        </div>
    </div>

    <div class="fxt-meta-field">
        <label for="fxt_website_url">🌐 Official Website</label>
        <input type="url" id="fxt_website_url" name="fxt_website_url"
               value="<?php echo esc_attr($fields['website_url']); ?>"
               placeholder="https://exness.com">
    </div>

    <div class="fxt-meta-section">
        <h4>✅ Pros (one per line)</h4>
        <textarea id="fxt_pros" name="fxt_pros" rows="5"
                  placeholder="Spread thấp&#10;Rút tiền nhanh&#10;Hỗ trợ tiếng Việt"><?php echo esc_textarea($fields['pros']); ?></textarea>
        <p class="fxt-meta-hint">Press Enter to add each advantage. Each line = 1 bullet point on the website.</p>
    </div>

    <div class="fxt-meta-section">
        <h4>❌ Cons (one per line)</h4>
        <textarea id="fxt_cons" name="fxt_cons" rows="5"
                  placeholder="Phí qua đêm cao&#10;Không có bonus"><?php echo esc_textarea($fields['cons']); ?></textarea>
        <p class="fxt-meta-hint">Press Enter to add each disadvantage. Each line = 1 bullet point on the website.</p>
    </div>
    <?php
}

// ╔═══════════════════════════════════════════════════════════════╗
// ║  BROKER POST: Render meta box chọn Broker cha                ║
// ╚═══════════════════════════════════════════════════════════════╝

function fxt_broker_post_parent_html($post) {
    wp_nonce_field('fxt_broker_post_meta', 'fxt_broker_post_meta_nonce');

    $current_parent = get_post_meta($post->ID, '_fxt_parent_broker', true);

    $brokers = get_posts([
        'post_type'   => 'broker',
        'numberposts' => -1,
        'orderby'     => 'title',
        'order'       => 'ASC',
        'post_status' => 'publish',
    ]);
    ?>
    <style>
        .fxt-parent-broker-select { width: 100%; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; }
        .fxt-parent-broker-select:focus { border-color: #2271b1; outline: none; box-shadow: 0 0 0 1px #2271b1; }
        .fxt-parent-broker-hint { font-size: 11px; color: #666; margin-top: 8px; line-height: 1.5; }
        .fxt-parent-broker-preview { margin-top: 12px; padding: 10px; background: #f0f6fc; border: 1px solid #c3daf5; border-radius: 4px; font-size: 12px; display: none; }
        .fxt-parent-broker-preview.visible { display: block; }
        .fxt-parent-broker-preview a { font-weight: 600; }
    </style>

    <p><strong>Chọn Broker pillar mà bài này hỗ trợ:</strong></p>

    <select name="fxt_parent_broker" id="fxt_parent_broker" class="fxt-parent-broker-select">
        <option value="">— Chọn Broker —</option>
        <?php foreach ($brokers as $b): ?>
            <option value="<?php echo $b->ID; ?>" <?php selected($current_parent, $b->ID); ?>>
                <?php echo esc_html($b->post_title); ?>
                <?php
                $rating = get_post_meta($b->ID, '_fxt_rating', true);
                if ($rating) echo ' (' . $rating . '/10)';
                ?>
            </option>
        <?php endforeach; ?>
    </select>

    <p class="fxt-parent-broker-hint">
        ⚠️ <strong>Bắt buộc.</strong> Bài này sẽ nằm trong URL silo của broker đã chọn.<br>
        Ví dụ: <code>/broker-review/<em>exness</em>/<em>bai-viet-nay</em>/</code><br>
        💡 Sau khi publish, vào <strong>Settings → Permalinks</strong> và nhấn "Save Changes" nếu link 404.
    </p>

    <?php if ($current_parent): 
        $parent = get_post($current_parent);
        if ($parent):
    ?>
    <div class="fxt-parent-broker-preview visible">
        📌 Broker: <a href="<?php echo get_edit_post_link($current_parent); ?>"><?php echo esc_html($parent->post_title); ?></a><br>
        🔗 URL: <code><?php echo esc_html(get_permalink($post->ID)); ?></code>
    </div>
    <?php endif; endif; ?>

    <?php
    // Hiện danh sách các broker_post khác cùng broker (internal linking)
    if ($current_parent):
        $siblings = get_posts([
            'post_type'   => 'broker_post',
            'meta_key'    => '_fxt_parent_broker',
            'meta_value'  => $current_parent,
            'numberposts' => 20,
            'post_status' => 'publish',
            'exclude'     => [$post->ID],
        ]);
        if (!empty($siblings)):
    ?>
    <div style="margin-top:16px; padding:10px; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:4px;">
        <strong style="font-size:12px; color:#1e3a5f;">📝 Các bài phụ khác cùng Broker:</strong>
        <ul style="margin:8px 0 0 16px; font-size:12px; list-style:disc;">
            <?php foreach ($siblings as $sib): ?>
            <li>
                <a href="<?php echo get_edit_post_link($sib->ID); ?>"><?php echo esc_html($sib->post_title); ?></a>
                <span style="color:#999;">— <?php echo $sib->post_status; ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <p style="font-size:11px; color:#888; margin-top:6px;">💡 Dùng danh sách này để internal link giữa các bài phụ.</p>
    </div>
    <?php
        endif;
    endif;
}

/**
 * Save Broker Post parent
 */
add_action('save_post_broker_post', function ($post_id) {
    if (!isset($_POST['fxt_broker_post_meta_nonce']) ||
        !wp_verify_nonce($_POST['fxt_broker_post_meta_nonce'], 'fxt_broker_post_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['fxt_parent_broker'])) {
        $parent_id = intval($_POST['fxt_parent_broker']);
        if ($parent_id > 0) {
            update_post_meta($post_id, '_fxt_parent_broker', $parent_id);
        } else {
            delete_post_meta($post_id, '_fxt_parent_broker');
        }
    }
});


// ╔═══════════════════════════════════════════════════════════════╗
// ║  SAVE META DATA                                              ║
// ╚═══════════════════════════════════════════════════════════════╝

add_action('save_post_broker', function ($post_id) {

    if (!isset($_POST['fxt_broker_meta_nonce']) ||
        !wp_verify_nonce($_POST['fxt_broker_meta_nonce'], 'fxt_broker_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $text_fields = [
        'fxt_broker_short_name' => '_fxt_broker_short_name',
        'fxt_rating'      => '_fxt_rating',
        'fxt_spread'      => '_fxt_spread',
        'fxt_leverage'    => '_fxt_leverage',
        'fxt_min_deposit' => '_fxt_min_deposit',
        'fxt_regulation'  => '_fxt_regulation',
        'fxt_founded'     => '_fxt_founded',
        'fxt_platforms'   => '_fxt_platforms',
    ];
    $textarea_fields = [
        'fxt_pros' => '_fxt_pros',
        'fxt_cons' => '_fxt_cons',
    ];
    $url_fields = [
        'fxt_affiliate_link' => '_fxt_affiliate_link',
        'fxt_website_url'    => '_fxt_website_url',
    ];

    foreach ($text_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_key]));
        }
    }
    foreach ($textarea_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, sanitize_textarea_field($_POST[$form_key]));
        }
    }
    foreach ($url_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, esc_url_raw($_POST[$form_key]));
        }
    }

    // Save broker icon (attachment ID)
    if (isset($_POST['fxt_broker_icon'])) {
        $icon_id = intval($_POST['fxt_broker_icon']);
        if ($icon_id > 0) {
            update_post_meta($post_id, '_fxt_broker_icon', $icon_id);
        } else {
            delete_post_meta($post_id, '_fxt_broker_icon');
        }
    }

});

/**
 * Helper: Lấy tất cả meta data của broker
 */
function fxt_get_broker_meta($post_id) {
    return [
        'short_name'     => get_post_meta($post_id, '_fxt_broker_short_name', true),
        'icon_id'        => get_post_meta($post_id, '_fxt_broker_icon', true),
        'rating'         => get_post_meta($post_id, '_fxt_rating', true),
        'spread'         => get_post_meta($post_id, '_fxt_spread', true),
        'leverage'       => get_post_meta($post_id, '_fxt_leverage', true),
        'min_deposit'    => get_post_meta($post_id, '_fxt_min_deposit', true),
        'regulation'     => get_post_meta($post_id, '_fxt_regulation', true),
        'founded'        => get_post_meta($post_id, '_fxt_founded', true),
        'platforms'      => get_post_meta($post_id, '_fxt_platforms', true),
        'affiliate_link' => get_post_meta($post_id, '_fxt_affiliate_link', true),
        'website_url'    => get_post_meta($post_id, '_fxt_website_url', true),
        'pros'           => array_filter(array_map('trim', explode("\n", get_post_meta($post_id, '_fxt_pros', true) ?: ''))),
        'cons'           => array_filter(array_map('trim', explode("\n", get_post_meta($post_id, '_fxt_cons', true) ?: ''))),
    ];
}

/**
 * Helper: Lấy Tên hiển thị của Broker (dùng cho CTA/Card/Sidebar...)
 * Ưu tiên: _fxt_broker_short_name → fallback Tiêu đề bài viết
 */
function fxt_get_broker_name($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $short_name = get_post_meta($post_id, '_fxt_broker_short_name', true);
    return $short_name !== '' ? $short_name : get_the_title($post_id);
}

/**
 * Helper: Lấy HTML ảnh icon của broker
 * Ưu tiên: dedicated icon → featured image → initials
 */
function fxt_get_broker_icon_html($post_id = null, $size = 'fxt-broker-logo') {
    if (!$post_id) $post_id = get_the_ID();

    $icon_id = get_post_meta($post_id, '_fxt_broker_icon', true);
    if ($icon_id) {
        return wp_get_attachment_image($icon_id, $size, false, [
            'alt'     => esc_attr(fxt_get_broker_name($post_id)),
            'loading' => 'lazy',
        ]);
    }

    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail($post_id, $size);
    }

    return '<span style="font-size:1.5rem;font-weight:800;color:var(--c-primary)">'
        . esc_html(mb_substr(fxt_get_broker_name($post_id), 0, 2))
        . '</span>';
}

/**
 * Helper: Lấy parent broker data cho broker_post
 */
function fxt_get_parent_broker($broker_post_id = null) {
    if (!$broker_post_id) $broker_post_id = get_the_ID();
    $parent_id = get_post_meta($broker_post_id, '_fxt_parent_broker', true);
    if (!$parent_id) return null;

    $parent = get_post($parent_id);
    if (!$parent || $parent->post_status !== 'publish') return null;

    return [
        'ID'             => $parent->ID,
        'title'          => fxt_get_broker_name($parent->ID),
        'permalink'      => get_permalink($parent->ID),
        'meta'           => fxt_get_broker_meta($parent->ID),
        'affiliate_link' => get_post_meta($parent->ID, '_fxt_affiliate_link', true) ?: get_theme_mod('fxt_default_affiliate_link', ''),
        'icon_html'      => fxt_get_broker_icon_html($parent->ID, 'thumbnail'),
    ];
}

/**
 * Helper: Lấy tất cả broker_posts thuộc 1 broker
 */
function fxt_get_broker_sub_posts($broker_id, $exclude = 0) {
    return get_posts([
        'post_type'   => 'broker_post',
        'meta_key'    => '_fxt_parent_broker',
        'meta_value'  => $broker_id,
        'numberposts' => -1,
        'post_status' => 'publish',
        'exclude'     => $exclude ? [$exclude] : [],
        'orderby'     => 'title',
        'order'       => 'ASC',
    ]);
}


// ╔═══════════════════════════════════════════════════════════════════╗
// ║  CUSTOM AUTHOR META BOX — broker + broker_post + generic_post    ║
// ╚═══════════════════════════════════════════════════════════════════╝

// add_action('add_meta_boxes', function () {
//     $author_post_types = ['generic_post'];
//     foreach ($author_post_types as $pt) {
//         add_meta_box(
//             'fxt_custom_author_' . $pt,
//             '✏️ Custom Author (Tác giả hiển thị)',
//             'fxt_broker_post_author_html',
//             $pt,
//             'side',
//             'default'
//         );
//     }
// });

function fxt_broker_post_author_html($post) {
    wp_nonce_field('fxt_custom_author_meta', 'fxt_custom_author_nonce');

    $author_name = get_post_meta($post->ID, '_fxt_custom_author_name', true);
    $author_title = get_post_meta($post->ID, '_fxt_custom_author_title', true);
    $author_bio = get_post_meta($post->ID, '_fxt_custom_author_bio', true);
    $author_avatar_url = get_post_meta($post->ID, '_fxt_custom_author_avatar', true);
    ?>
    <style>
        .fxt-author-field { margin-bottom: 12px; }
        .fxt-author-field label { display: block; font-weight: 600; font-size: 12px; margin-bottom: 4px; color: #1e3a5f; }
        .fxt-author-field input, .fxt-author-field textarea { width: 100%; padding: 6px 10px; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; }
        .fxt-author-field input:focus, .fxt-author-field textarea:focus { border-color: #2271b1; outline: none; box-shadow: 0 0 0 1px #2271b1; }
        .fxt-author-hint { font-size: 11px; color: #888; margin-top: 3px; font-style: italic; }
        .fxt-author-preview { margin-top: 12px; padding: 10px; background: #f9fafb; border: 1px solid #e0e0e0; border-radius: 6px; }
        .fxt-author-preview-name { font-weight: 700; font-size: 13px; }
        .fxt-author-preview-title { font-size: 11px; color: #666; }
    </style>

    <div class="fxt-author-field">
        <label for="fxt_custom_author_name">Tên hiển thị:</label>
        <input type="text" id="fxt_custom_author_name" name="fxt_custom_author_name"
               value="<?php echo esc_attr($author_name); ?>"
               placeholder="e.g. Nguyễn Văn A">
        <p class="fxt-author-hint">Để trống = dùng tên tác giả WordPress mặc định.</p>
    </div>

    <div class="fxt-author-field">
        <label for="fxt_custom_author_title">Chức danh / Title:</label>
        <input type="text" id="fxt_custom_author_title" name="fxt_custom_author_title"
               value="<?php echo esc_attr($author_title); ?>"
               placeholder="e.g. Forex Analyst, Senior Trader">
        <p class="fxt-author-hint">Hiển thị dưới tên tác giả (tùy chọn).</p>
    </div>

    <div class="fxt-author-field">
        <label for="fxt_custom_author_bio">Bio ngắn:</label>
        <textarea id="fxt_custom_author_bio" name="fxt_custom_author_bio" rows="3"
                  placeholder="Kinh nghiệm 5 năm giao dịch Forex..."><?php echo esc_textarea($author_bio); ?></textarea>
        <p class="fxt-author-hint">Để trống = dùng bio từ WP User Profile.</p>
    </div>

    <div class="fxt-author-field">
        <label for="fxt_custom_author_avatar">URL Avatar (tùy chọn):</label>
        <input type="url" id="fxt_custom_author_avatar" name="fxt_custom_author_avatar"
               value="<?php echo esc_attr($author_avatar_url); ?>"
               placeholder="https://...avatar.jpg">
        <p class="fxt-author-hint">Để trống = dùng Gravatar mặc định.</p>
    </div>

    <?php if ($author_name): ?>
    <div class="fxt-author-preview">
        <div class="fxt-author-preview-name"><?php echo esc_html($author_name); ?></div>
        <?php if ($author_title): ?>
        <div class="fxt-author-preview-title"><?php echo esc_html($author_title); ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php
}

/**
 * Save custom author meta
 */
function fxt_save_custom_author_meta($post_id) {
    if (!isset($_POST['fxt_custom_author_nonce']) ||
        !wp_verify_nonce($_POST['fxt_custom_author_nonce'], 'fxt_custom_author_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = [
        'fxt_custom_author_name'   => 'sanitize_text_field',
        'fxt_custom_author_title'  => 'sanitize_text_field',
        'fxt_custom_author_bio'    => 'sanitize_textarea_field',
        'fxt_custom_author_avatar' => 'esc_url_raw',
    ];

    foreach ($fields as $field => $sanitize) {
        if (isset($_POST[$field])) {
            $value = call_user_func($sanitize, $_POST[$field]);
            if ($value) {
                update_post_meta($post_id, '_' . $field, $value);
            } else {
                delete_post_meta($post_id, '_' . $field);
            }
        }
    }
}

add_action('save_post_generic_post', 'fxt_save_custom_author_meta');


// ╔═══════════════════════════════════════════════════════════════════╗
// ║  SEO & KEYWORDS META BOX                                         ║
// ║  Dùng chung: broker (pillar) + broker_post + generic_post        ║
// ╚═══════════════════════════════════════════════════════════════════╝

add_action('add_meta_boxes', function () {
    add_meta_box(
        'fxt_seo_keywords',
        '🔍 SEO & Keywords',
        'fxt_seo_meta_box_html',
        'broker',
        'normal',
        'high'
    );
});

/**
 * Render SEO meta box
 */
function fxt_seo_meta_box_html($post) {
    wp_nonce_field('fxt_seo_meta_save', 'fxt_seo_meta_nonce');

    $seo_title  = get_post_meta($post->ID, '_fxt_seo_title', true);
    $seo_desc   = get_post_meta($post->ID, '_fxt_seo_desc', true);
    $focus_kw   = get_post_meta($post->ID, '_fxt_focus_keyword', true);
    $second_kw  = get_post_meta($post->ID, '_fxt_secondary_keywords', true);

    $post_title  = get_the_title($post->ID) ?: '(Post Title)';
    $post_url    = get_permalink($post->ID) ?: home_url('/');
    $site_name   = get_bloginfo('name');

    $serp_default_title = $post_title . ' | ' . $site_name;
    $serp_default_desc  = 'No meta description — Google will auto-generate from page content.';
    ?>
    <style>
        .fxt-serp-preview {
            background: #fff; border: 1px solid #e0e0e0; border-radius: 8px;
            padding: 16px 20px; margin-bottom: 20px; font-family: Arial, sans-serif;
        }
        .fxt-serp-label { font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; }
        .fxt-serp-title { color: #1a0dab; font-size: 20px; font-weight: 400; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 600px; }
        .fxt-serp-url   { color: #006621; font-size: 13px; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 600px; }
        .fxt-serp-desc  { color: #545454; font-size: 13px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 600px; }
        .fxt-seo-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .fxt-seo-field  { margin-bottom: 14px; }
        .fxt-seo-field label { display: flex; justify-content: space-between; align-items: center; font-weight: 600; margin-bottom: 5px; color: #1e3a5f; font-size: 13px; }
        .fxt-seo-field input, .fxt-seo-field textarea { width: 100%; padding: 8px 10px; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; box-sizing: border-box; }
        .fxt-seo-field input:focus, .fxt-seo-field textarea:focus { border-color: #2271b1; outline: none; box-shadow: 0 0 0 1px #2271b1; }
        .fxt-seo-counter { font-size: 11px; font-weight: 400; color: #888; font-style: italic; }
        .fxt-seo-hint   { font-size: 11px; color: #888; margin-top: 3px; font-style: italic; }
        .fxt-counter-good { color: #46b450 !important; font-weight: 700; }
        .fxt-counter-warn { color: #f0821e !important; font-weight: 700; }
        .fxt-counter-bad  { color: #dc3232 !important; font-weight: 700; }
        .fxt-seo-divider  { border: none; border-top: 1px solid #e8e8e8; margin: 16px 0; }
    </style>

    <div class="fxt-serp-preview">
        <div class="fxt-serp-label">🔍 Google Search Preview</div>
        <div class="fxt-serp-title" id="fxt-serp-title"><?php echo esc_html($seo_title ?: $serp_default_title); ?></div>
        <div class="fxt-serp-url"   id="fxt-serp-url"><?php echo esc_html($post_url); ?></div>
        <div class="fxt-serp-desc"  id="fxt-serp-desc"><?php echo esc_html($seo_desc ?: $serp_default_desc); ?></div>
    </div>

    <div class="fxt-seo-field">
        <label for="fxt_seo_title">
            🏷 SEO Title
            <span class="fxt-seo-counter" id="fxt-title-counter"><?php echo mb_strlen($seo_title); ?> / 60</span>
        </label>
        <input type="text" id="fxt_seo_title" name="fxt_seo_title"
               value="<?php echo esc_attr($seo_title); ?>"
               placeholder="<?php echo esc_attr($serp_default_title); ?>">
        <p class="fxt-seo-hint">Để trống = dùng tiêu đề bài. Tối ưu: <strong>50–60 ký tự</strong>.</p>
    </div>

    <div class="fxt-seo-field">
        <label for="fxt_seo_desc">
            📝 Meta Description
            <span class="fxt-seo-counter" id="fxt-desc-counter"><?php echo mb_strlen($seo_desc); ?> / 160</span>
        </label>
        <textarea id="fxt_seo_desc" name="fxt_seo_desc" rows="3"
                  placeholder="Mô tả ngắn gọn hiển thị trên Google. Để trống = dùng post excerpt."><?php echo esc_textarea($seo_desc); ?></textarea>
        <p class="fxt-seo-hint">Tối ưu: <strong>150–160 ký tự</strong>.</p>
    </div>

    <hr class="fxt-seo-divider">

    <div class="fxt-seo-grid">
        <div class="fxt-seo-field">
            <label for="fxt_focus_keyword">🎯 Focus Keyword (Primary)</label>
            <input type="text" id="fxt_focus_keyword" name="fxt_focus_keyword"
                   value="<?php echo esc_attr($focus_kw); ?>"
                   placeholder="e.g. Exness broker review">
            <p class="fxt-seo-hint">Keyword chính trang này nhắm đến.</p>
        </div>
        <div class="fxt-seo-field">
            <label for="fxt_secondary_keywords">🔑 Secondary Keywords (mỗi dòng 1 từ)</label>
            <textarea id="fxt_secondary_keywords" name="fxt_secondary_keywords" rows="4"
                      placeholder="exness review&#10;exness spread&#10;exness leverage&#10;sàn exness uy tín"><?php echo esc_textarea($second_kw); ?></textarea>
            <p class="fxt-seo-hint">LSI keywords / long-tail variations.</p>
        </div>
    </div>

    <script>
    (function($) {
        var defaultTitle = <?php echo json_encode($serp_default_title); ?>;
        var defaultDesc  = <?php echo json_encode($serp_default_desc); ?>;

        function updateCounter(val, id, min, max) {
            var len = val.length, el = document.getElementById(id);
            if (!el) return;
            el.textContent = len + ' / ' + max;
            el.className = 'fxt-seo-counter';
            if (len >= min && len <= max) el.classList.add('fxt-counter-good');
            else if (len > max)           el.classList.add('fxt-counter-bad');
            else if (len > 0)             el.classList.add('fxt-counter-warn');
        }

        function updatePreview() {
            $('#fxt-serp-title').text($('#fxt_seo_title').val().trim() || defaultTitle);
            $('#fxt-serp-desc').text($('#fxt_seo_desc').val().trim()   || defaultDesc);
        }

        $('#fxt_seo_title').on('input', function() {
            updateCounter(this.value, 'fxt-title-counter', 50, 60);
            updatePreview();
        });
        $('#fxt_seo_desc').on('input', function() {
            updateCounter(this.value, 'fxt-desc-counter', 150, 160);
            updatePreview();
        });

        updateCounter($('#fxt_seo_title').val(), 'fxt-title-counter', 50, 60);
        updateCounter($('#fxt_seo_desc').val(),  'fxt-desc-counter',  150, 160);
    })(jQuery);
    </script>
    <?php
}

/**
 * Save SEO meta
 */
add_action('save_post', function ($post_id) {
    if (!isset($_POST['fxt_seo_meta_nonce']) ||
        !wp_verify_nonce($_POST['fxt_seo_meta_nonce'], 'fxt_seo_meta_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $text_fields = [
        'fxt_seo_title'      => '_fxt_seo_title',
        'fxt_focus_keyword'  => '_fxt_focus_keyword',
    ];
    $textarea_fields = [
        'fxt_seo_desc'            => '_fxt_seo_desc',
        'fxt_secondary_keywords'  => '_fxt_secondary_keywords',
    ];

    foreach ($text_fields as $key => $meta) {
        if (isset($_POST[$key])) {
            $val = sanitize_text_field($_POST[$key]);
            $val ? update_post_meta($post_id, $meta, $val) : delete_post_meta($post_id, $meta);
        }
    }
    foreach ($textarea_fields as $key => $meta) {
        if (isset($_POST[$key])) {
            $val = sanitize_textarea_field($_POST[$key]);
            $val ? update_post_meta($post_id, $meta, $val) : delete_post_meta($post_id, $meta);
        }
    }
});

/**
 * Helper: Lấy custom author data
 */
function fxt_get_custom_author($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();

    $custom_name = get_post_meta($post_id, '_fxt_custom_author_name', true);
    if (!$custom_name) return null;

    return [
        'name'   => $custom_name,
        'title'  => get_post_meta($post_id, '_fxt_custom_author_title', true),
        'bio'    => get_post_meta($post_id, '_fxt_custom_author_bio', true),
        'avatar' => get_post_meta($post_id, '_fxt_custom_author_avatar', true),
    ];
}

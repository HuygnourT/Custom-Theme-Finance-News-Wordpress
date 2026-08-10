<?php
if (!defined('ABSPATH')) exit;

/**
 * Meta Boxes cho Sub Posts (broker_post + generic_post)
 *
 * broker_post: GIỮ NGUYÊN đầy đủ hệ thống Section cũ (CTA Buttons /
 * Pros & Cons / Content Sections / Intro & Outro).
 *
 * generic_post: ĐÃ BỎ toàn bộ hệ thống Section — chỉ dùng đúng 1 ô
 * Gutenberg mặc định (the_content) cho nội dung chính.
 *
 * @package FXTradingToday
 */

// ╔═══════════════════════════════════════════════════════════════╗
// ║  META BOXES: CTA / Pros & Cons / Sections — CHỈ broker_post   ║
// ╚═══════════════════════════════════════════════════════════════╝

add_action('add_meta_boxes', function () {
    $pt = 'broker_post';

    add_meta_box(
        'fxt_sub_cta_buttons',
        '🔘 CTA Buttons',
        'fxt_sub_cta_buttons_html',
        $pt,
        'normal',
        'high'
    );

    add_meta_box(
        'fxt_sub_pros_cons',
        '✅❌ Pros & Cons',
        'fxt_sub_pros_cons_html',
        $pt,
        'normal',
        'high'
    );

    add_meta_box(
        'fxt_sub_sections',
        '📑 Content Sections (Collapsible / CTA / Pros-Cons)',
        'fxt_sub_sections_html',
        $pt,
        'normal',
        'default'
    );
});

// ╔═══════════════════════════════════════════════════════════════╗
// ║  CTA BUTTONS META BOX                                        ║
// ╚═══════════════════════════════════════════════════════════════╝

function fxt_sub_cta_buttons_html($post) {
    wp_nonce_field('fxt_sub_post_meta', 'fxt_sub_post_meta_nonce');

    $cta_buttons = get_post_meta($post->ID, '_fxt_sub_cta_buttons', true);
    if (!is_array($cta_buttons)) $cta_buttons = [];
    if (empty($cta_buttons)) $cta_buttons = [['text' => '', 'url' => '', 'style' => 'primary', 'new_tab' => '']];
    ?>
    <div id="fxt-sub-cta-wrap">
        <?php foreach ($cta_buttons as $i => $btn): ?>
        <div class="fxt-cta-row" style="display:flex;gap:8px;align-items:center;margin-bottom:8px;padding:10px;background:#f6f7f7;border-radius:4px;">
            <input type="text" name="fxt_sub_cta[<?php echo $i; ?>][text]" placeholder="Text nút" value="<?php echo esc_attr($btn['text'] ?? ''); ?>" style="flex:1;padding:6px;">
            <input type="url" name="fxt_sub_cta[<?php echo $i; ?>][url]" placeholder="https://..." value="<?php echo esc_attr($btn['url'] ?? ''); ?>" style="flex:2;padding:6px;">
            <select name="fxt_sub_cta[<?php echo $i; ?>][style]" style="padding:6px;">
                <?php foreach (['primary' => 'Primary', 'outline' => 'Outline', 'cta' => 'CTA'] as $sv => $sl): ?>
                <option value="<?php echo esc_attr($sv); ?>" <?php selected($btn['style'] ?? 'primary', $sv); ?>><?php echo esc_html($sl); ?></option>
                <?php endforeach; ?>
            </select>
            <label style="font-size:12px;white-space:nowrap;"><input type="checkbox" name="fxt_sub_cta[<?php echo $i; ?>][new_tab]" value="1" <?php checked(!empty($btn['new_tab'])); ?>> Tab mới</label>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button" id="fxt-add-cta-row">+ Thêm nút CTA</button>
    <script>
    (function() {
        var wrap = document.getElementById('fxt-sub-cta-wrap');
        document.getElementById('fxt-add-cta-row').addEventListener('click', function() {
            var i = wrap.children.length;
            var row = document.createElement('div');
            row.className = 'fxt-cta-row';
            row.style.cssText = 'display:flex;gap:8px;align-items:center;margin-bottom:8px;padding:10px;background:#f6f7f7;border-radius:4px;';
            row.innerHTML = '<input type="text" name="fxt_sub_cta[' + i + '][text]" placeholder="Text nút" style="flex:1;padding:6px;">' +
                '<input type="url" name="fxt_sub_cta[' + i + '][url]" placeholder="https://..." style="flex:2;padding:6px;">' +
                '<select name="fxt_sub_cta[' + i + '][style]" style="padding:6px;"><option value="primary">Primary</option><option value="outline">Outline</option><option value="cta">CTA</option></select>' +
                '<label style="font-size:12px;white-space:nowrap;"><input type="checkbox" name="fxt_sub_cta[' + i + '][new_tab]" value="1"> Tab mới</label>';
            wrap.appendChild(row);
        });
    })();
    </script>
    <?php
}

// ╔═══════════════════════════════════════════════════════════════╗
// ║  PROS & CONS META BOX                                        ║
// ╚═══════════════════════════════════════════════════════════════╝

function fxt_sub_pros_cons_html($post) {
    $pros = get_post_meta($post->ID, '_fxt_sub_pros', true);
    $cons = get_post_meta($post->ID, '_fxt_sub_cons', true);
    ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div>
            <label style="display:block;font-weight:600;margin-bottom:6px;">✅ Pros (mỗi dòng 1 mục)</label>
            <textarea name="fxt_sub_pros" rows="6" style="width:100%;padding:8px;"><?php echo esc_textarea($pros); ?></textarea>
        </div>
        <div>
            <label style="display:block;font-weight:600;margin-bottom:6px;">❌ Cons (mỗi dòng 1 mục)</label>
            <textarea name="fxt_sub_cons" rows="6" style="width:100%;padding:8px;"><?php echo esc_textarea($cons); ?></textarea>
        </div>
    </div>
    <?php
}

// ╔═══════════════════════════════════════════════════════════════╗
// ║  CONTENT SECTIONS META BOX (Collapsible / CTA / Pros-Cons)   ║
// ╚═══════════════════════════════════════════════════════════════╝

function fxt_sub_sections_html($post) {
    $sections = get_post_meta($post->ID, '_fxt_sub_sections', true);
    if (!is_array($sections)) $sections = [];
    ?>
    <div id="fxt-sub-sections-wrap">
        <?php foreach ($sections as $i => $sec) fxt_render_sub_section_fields($i, $sec); ?>
    </div>
    <button type="button" class="button" id="fxt-add-sub-section" data-nonce="<?php echo esc_attr(wp_create_nonce('fxt_add_sub_section_nonce')); ?>">+ Thêm Section</button>
    <script>
    (function($) {
        var wrap = $('#fxt-sub-sections-wrap');
        $('#fxt-add-sub-section').on('click', function() {
            var index = wrap.children().length;
            var nonce = $(this).data('nonce');
            $.post(ajaxurl, { action: 'fxt_add_sub_section', index: index, nonce: nonce }, function(html) {
                wrap.append(html);
            });
        });
        wrap.on('click', '.fxt-remove-sub-section', function() {
            $(this).closest('.fxt-sub-section-block').remove();
        });
    })(jQuery);
    </script>
    <?php
}

function fxt_render_sub_section_fields($index, $data) {
    $title           = $data['title'] ?? '';
    $content         = $data['content'] ?? '';
    $show_proscons   = !empty($data['show_proscons']);
    $pros            = $data['pros'] ?? '';
    $cons            = $data['cons'] ?? '';
    $collapsible     = !empty($data['collapsible']);
    $collapse_detail = $data['collapse_detail'] ?? '';
    $show_text       = $data['show_text'] ?? '';
    $hide_text       = $data['hide_text'] ?? '';
    $cta_buttons     = is_array($data['cta_buttons'] ?? null) ? $data['cta_buttons'] : [];
    ?>
    <div class="fxt-sub-section-block" style="border:1px solid #ddd;padding:14px;margin-bottom:14px;border-radius:6px;background:#fff;">
        <p><label style="font-weight:600;">Tiêu đề Section</label><br>
            <input type="text" name="fxt_sub_sections[<?php echo $index; ?>][title]" value="<?php echo esc_attr($title); ?>" style="width:100%;padding:6px;"></p>

        <p><label style="font-weight:600;">Nội dung</label><br>
            <textarea name="fxt_sub_sections[<?php echo $index; ?>][content]" rows="5" style="width:100%;padding:6px;"><?php echo esc_textarea($content); ?></textarea></p>

        <p><label><input type="checkbox" name="fxt_sub_sections[<?php echo $index; ?>][show_proscons]" value="1" <?php checked($show_proscons); ?>> Hiện Pros/Cons riêng cho section này</label></p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
            <textarea name="fxt_sub_sections[<?php echo $index; ?>][pros]" rows="3" placeholder="Pros (mỗi dòng 1 mục)" style="width:100%;padding:6px;"><?php echo esc_textarea($pros); ?></textarea>
            <textarea name="fxt_sub_sections[<?php echo $index; ?>][cons]" rows="3" placeholder="Cons (mỗi dòng 1 mục)" style="width:100%;padding:6px;"><?php echo esc_textarea($cons); ?></textarea>
        </div>

        <p><label><input type="checkbox" name="fxt_sub_sections[<?php echo $index; ?>][collapsible]" value="1" <?php checked($collapsible); ?>> Có nội dung ẩn/hiện (collapsible)</label></p>
        <textarea name="fxt_sub_sections[<?php echo $index; ?>][collapse_detail]" rows="4" placeholder="Nội dung ẩn/hiện" style="width:100%;padding:6px;margin-bottom:8px;"><?php echo esc_textarea($collapse_detail); ?></textarea>
        <div style="display:flex;gap:10px;margin-bottom:10px;">
            <input type="text" name="fxt_sub_sections[<?php echo $index; ?>][show_text]" placeholder="Text nút Hiện (VD: ▼ Show details)" value="<?php echo esc_attr($show_text); ?>" style="flex:1;padding:6px;">
            <input type="text" name="fxt_sub_sections[<?php echo $index; ?>][hide_text]" placeholder="Text nút Ẩn (VD: ▲ Hide details)" value="<?php echo esc_attr($hide_text); ?>" style="flex:1;padding:6px;">
        </div>

        <p style="font-weight:600;margin-bottom:6px;">CTA Buttons riêng cho section này:</p>
        <?php if (empty($cta_buttons)) $cta_buttons = [['text' => '', 'url' => '', 'style' => 'primary', 'new_tab' => '']]; ?>
        <?php foreach ($cta_buttons as $ci => $cb): ?>
        <div style="display:flex;gap:8px;margin-bottom:6px;">
            <input type="text" name="fxt_sub_sections[<?php echo $index; ?>][cta_buttons][<?php echo $ci; ?>][text]" placeholder="Text nút" value="<?php echo esc_attr($cb['text'] ?? ''); ?>" style="flex:1;padding:6px;">
            <input type="url" name="fxt_sub_sections[<?php echo $index; ?>][cta_buttons][<?php echo $ci; ?>][url]" placeholder="https://..." value="<?php echo esc_attr($cb['url'] ?? ''); ?>" style="flex:2;padding:6px;">
            <select name="fxt_sub_sections[<?php echo $index; ?>][cta_buttons][<?php echo $ci; ?>][style]" style="padding:6px;">
                <?php foreach (['primary' => 'Primary', 'outline' => 'Outline', 'cta' => 'CTA'] as $sv => $sl): ?>
                <option value="<?php echo esc_attr($sv); ?>" <?php selected($cb['style'] ?? 'primary', $sv); ?>><?php echo esc_html($sl); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endforeach; ?>

        <button type="button" class="button fxt-remove-sub-section" style="margin-top:8px;color:#d63638;">✕ Xóa Section này</button>
    </div>
    <?php
}

/**
 * AJAX: Add new sub section
 */
add_action('wp_ajax_fxt_add_sub_section', function () {
    check_ajax_referer('fxt_add_sub_section_nonce', 'nonce');
    if (!current_user_can('edit_posts')) wp_die('Unauthorized');

    $index = intval($_POST['index'] ?? 0);
    ob_start();
    fxt_render_sub_section_fields($index, []);
    echo ob_get_clean();
    wp_die();
});

/**
 * SAVE: Sub post meta data (CTA, Pros/Cons, Sections) — CHỈ broker_post
 */
function fxt_save_sub_post_meta($post_id) {
    if (!isset($_POST['fxt_sub_post_meta_nonce']) ||
        !wp_verify_nonce($_POST['fxt_sub_post_meta_nonce'], 'fxt_sub_post_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // CTA Buttons
    $cta_buttons = [];
    if (isset($_POST['fxt_sub_cta']) && is_array($_POST['fxt_sub_cta'])) {
        foreach ($_POST['fxt_sub_cta'] as $btn) {
            if (empty($btn['text']) && empty($btn['url'])) continue;
            $cta_buttons[] = [
                'text'    => sanitize_text_field($btn['text'] ?? ''),
                'url'     => esc_url_raw($btn['url'] ?? ''),
                'style'   => sanitize_text_field($btn['style'] ?? 'primary'),
                'new_tab' => !empty($btn['new_tab']) ? '1' : '',
            ];
        }
    }
    update_post_meta($post_id, '_fxt_sub_cta_buttons', $cta_buttons);

    // Pros/Cons
    if (isset($_POST['fxt_sub_pros'])) {
        update_post_meta($post_id, '_fxt_sub_pros', sanitize_textarea_field($_POST['fxt_sub_pros']));
    }
    if (isset($_POST['fxt_sub_cons'])) {
        update_post_meta($post_id, '_fxt_sub_cons', sanitize_textarea_field($_POST['fxt_sub_cons']));
    }

    // Sections
    if (isset($_POST['fxt_sub_sections']) && is_array($_POST['fxt_sub_sections'])) {
        $sections = [];
        foreach ($_POST['fxt_sub_sections'] as $sec) {
            if (empty($sec['title']) && empty($sec['content'])) continue;

            $cta_btns = [];
            if (!empty($sec['cta_buttons']) && is_array($sec['cta_buttons'])) {
                foreach ($sec['cta_buttons'] as $cb) {
                    if (empty($cb['text']) && empty($cb['url'])) continue;
                    $cta_btns[] = [
                        'text'    => sanitize_text_field($cb['text'] ?? ''),
                        'url'     => esc_url_raw($cb['url'] ?? ''),
                        'style'   => sanitize_text_field($cb['style'] ?? 'primary'),
                        'new_tab' => !empty($cb['new_tab']) ? '1' : '',
                    ];
                }
            }

            $sections[] = [
                'title'           => sanitize_text_field($sec['title'] ?? ''),
                'content'         => wp_kses_post($sec['content'] ?? ''),
                'show_proscons'   => !empty($sec['show_proscons']) ? '1' : '',
                'pros'            => sanitize_textarea_field($sec['pros'] ?? ''),
                'cons'            => sanitize_textarea_field($sec['cons'] ?? ''),
                'collapsible'     => !empty($sec['collapsible']) ? '1' : '',
                'collapse_detail' => wp_kses_post($sec['collapse_detail'] ?? ''),
                'show_text'       => sanitize_text_field($sec['show_text'] ?? ''),
                'hide_text'       => sanitize_text_field($sec['hide_text'] ?? ''),
                'cta_buttons'     => $cta_btns,
            ];
        }
        update_post_meta($post_id, '_fxt_sub_sections', $sections);
    } else {
        delete_post_meta($post_id, '_fxt_sub_sections');
    }
}

add_action('save_post_broker_post', 'fxt_save_sub_post_meta');


// ╔═══════════════════════════════════════════════════════════════════╗
// ║  INTRO & OUTRO TEXT META BOX — CHỈ broker_post                   ║
// ╚═══════════════════════════════════════════════════════════════════╝

add_action('add_meta_boxes', function () {
    add_meta_box(
        'fxt_sub_intro_outro',
        '📝 Intro & Outro Text (Đầu & Cuối bài)',
        'fxt_sub_intro_outro_html',
        'broker_post',
        'normal',
        'high'
    );
});

/**
 * Render HTML cho Intro & Outro meta box
 */
function fxt_sub_intro_outro_html($post) {
    wp_nonce_field('fxt_sub_intro_outro_meta', 'fxt_sub_intro_outro_nonce');

    $intro_text = get_post_meta($post->ID, '_fxt_sub_intro_text', true);
    $outro_text = get_post_meta($post->ID, '_fxt_sub_outro_text', true);

    $intro_editor_id = 'fxt_sub_intro_text';
    $outro_editor_id = 'fxt_sub_outro_text';
    ?>
    <p style="margin-bottom:16px; color:#555; font-size:13px;">
        <strong>Intro Text</strong> hiển thị ngay sau box thông tin broker (trước nội dung chính).<br>
        <strong>Outro Text</strong> hiển thị ở cuối bài (sau nội dung, trước CTA box).
    </p>

    <div style="display:grid;grid-template-columns:1fr;gap:20px;">
        <div>
            <label style="display:block;font-weight:700;margin-bottom:8px;font-size:14px;color:#1e3a5f;">📌 Intro Text (Đoạn mở đầu)</label>
            <?php
            wp_editor($intro_text, $intro_editor_id, [
                'textarea_name' => 'fxt_sub_intro_text',
                'textarea_rows' => 6,
                'media_buttons' => true,
                'teeny'         => false,
                'quicktags'     => true,
                'tinymce'       => [
                    'toolbar1' => 'formatselect bold italic bullist numlist blockquote link unlink fullscreen',
                    'toolbar2' => 'strikethrough hr forecolor pastetext removeformat undo redo',
                    'height'   => 180,
                ],
            ]);
            ?>
            <p style="font-size:11px;color:#888;margin-top:4px;font-style:italic;">Để trống = không hiện intro.</p>
        </div>

        <div>
            <label style="display:block;font-weight:700;margin-bottom:8px;font-size:14px;color:#1e3a5f;">📌 Outro Text (Đoạn kết thúc)</label>
            <?php
            wp_editor($outro_text, $outro_editor_id, [
                'textarea_name' => 'fxt_sub_outro_text',
                'textarea_rows' => 6,
                'media_buttons' => true,
                'teeny'         => false,
                'quicktags'     => true,
                'tinymce'       => [
                    'toolbar1' => 'formatselect bold italic bullist numlist blockquote link unlink fullscreen',
                    'toolbar2' => 'strikethrough hr forecolor pastetext removeformat undo redo',
                    'height'   => 180,
                ],
            ]);
            ?>
            <p style="font-size:11px;color:#888;margin-top:4px;font-style:italic;">Để trống = không hiện outro.</p>
        </div>
    </div>
    <?php
}

/**
 * Save Intro & Outro text
 */
function fxt_save_intro_outro_meta($post_id) {
    if (!isset($_POST['fxt_sub_intro_outro_nonce']) ||
        !wp_verify_nonce($_POST['fxt_sub_intro_outro_nonce'], 'fxt_sub_intro_outro_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['fxt_sub_intro_text'])) {
        $intro = wp_kses_post($_POST['fxt_sub_intro_text']);
        if ($intro) {
            update_post_meta($post_id, '_fxt_sub_intro_text', $intro);
        } else {
            delete_post_meta($post_id, '_fxt_sub_intro_text');
        }
    }

    if (isset($_POST['fxt_sub_outro_text'])) {
        $outro = wp_kses_post($_POST['fxt_sub_outro_text']);
        if ($outro) {
            update_post_meta($post_id, '_fxt_sub_outro_text', $outro);
        } else {
            delete_post_meta($post_id, '_fxt_sub_outro_text');
        }
    }
}

add_action('save_post_broker_post', 'fxt_save_intro_outro_meta');


// ╔═══════════════════════════════════════════════════════════════════╗
// ║  SEO & KEYWORDS META BOX cho broker_post + generic_post          ║
// ║  Render dùng hàm fxt_seo_meta_box_html() từ meta-boxes.php       ║
// ╚═══════════════════════════════════════════════════════════════════╝

add_action('add_meta_boxes', function () {
    $post_types = ['broker_post', 'generic_post'];
    foreach ($post_types as $pt) {
        add_meta_box(
            'fxt_seo_keywords',
            '🔍 SEO & Keywords',
            'fxt_seo_meta_box_html',
            $pt,
            'normal',
            'high'
        );
    }
});
// Save được xử lý bởi hook save_post trong meta-boxes.php (dùng nonce fxt_seo_meta_nonce)


// ╔═══════════════════════════════════════════════════════════════════╗
// ║  SLUG (URL) META BOX — chỉ cho broker_post                       ║
// ╚═══════════════════════════════════════════════════════════════════╝

add_action('add_meta_boxes', function () {
    add_meta_box(
        'fxt_broker_post_slug',
        '🔗 Slug (URL đường dẫn)',
        'fxt_broker_post_slug_html',
        'broker_post',
        'side',
        'high'
    );
});

function fxt_broker_post_slug_html($post) {
    wp_nonce_field('fxt_slug_save', 'fxt_slug_nonce');

    $current_slug = $post->post_name ?: sanitize_title($post->post_title);
    $broker_slug  = sanitize_title(get_theme_mod('fxt_broker_slug', 'broker-reviews'));

    // Lấy slug của broker cha
    $parent_id   = get_post_meta($post->ID, '_fxt_parent_broker', true);
    $parent_slug = '';
    if ($parent_id) {
        $parent      = get_post($parent_id);
        $parent_slug = $parent ? $parent->post_name : '';
    }

    $base_url = home_url('/' . $broker_slug . '/' . ($parent_slug ?: '{broker-slug}') . '/');
    ?>
    <style>
        .fxt-slug-field { margin-bottom: 10px; }
        .fxt-slug-field label { display: block; font-weight: 600; font-size: 12px; margin-bottom: 4px; color: #1e3a5f; }
        .fxt-slug-input { width: 100%; padding: 6px 10px; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; font-family: monospace; box-sizing: border-box; }
        .fxt-slug-input:focus { border-color: #2271b1; outline: none; box-shadow: 0 0 0 1px #2271b1; }
        .fxt-slug-preview { margin-top: 10px; padding: 8px 10px; background: #f0f6fc; border: 1px solid #c3daf5; border-radius: 4px; font-size: 11px; word-break: break-all; line-height: 1.6; }
        .fxt-slug-preview .slug-base { color: #555; }
        .fxt-slug-preview .slug-part { color: #1a0dab; font-weight: 700; }
        .fxt-slug-warn { margin-top: 8px; padding: 7px 9px; background: #fff8e5; border-left: 3px solid #f0821e; font-size: 11px; color: #555; line-height: 1.5; }
    </style>

    <div class="fxt-slug-field">
        <label for="fxt_post_slug">Slug hiện tại:</label>
        <input type="text"
               id="fxt_post_slug"
               name="fxt_post_slug"
               class="fxt-slug-input"
               value="<?php echo esc_attr($current_slug); ?>"
               placeholder="ten-bai-viet">
    </div>

    <div class="fxt-slug-preview" id="fxt-slug-preview">
        <span class="slug-base"><?php echo esc_html($base_url); ?></span><span class="slug-part" id="fxt-slug-part"><?php echo esc_html($current_slug ?: 'ten-bai-viet'); ?></span><span class="slug-base">/</span>
    </div>

    <?php if ($post->post_status === 'publish'): ?>
    <div class="fxt-slug-warn">
        ⚠️ Bài đã publish. Đổi slug sẽ <strong>thay đổi URL</strong> — các link cũ sẽ 404.
    </div>
    <?php endif; ?>

    <script>
    (function() {
        var input   = document.getElementById('fxt_post_slug');
        var preview = document.getElementById('fxt-slug-part');
        if (!input || !preview) return;

        input.addEventListener('input', function() {
            // Sanitise live: chữ thường, gạch ngang, không dấu cách
            var val = this.value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-_]/g, '');
            preview.textContent = val || 'ten-bai-viet';
        });

        // Khi blur: chuẩn hoá lại giá trị trong input
        input.addEventListener('blur', function() {
            this.value = this.value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-_]/g, '');
            preview.textContent = this.value || 'ten-bai-viet';
        });
    })();
    </script>
    <?php
}

/**
 * Save slug của broker_post
 * Dùng wpdb trực tiếp để tránh recursion khi gọi wp_update_post trong save_post
 */
add_action('save_post_broker_post', function ($post_id) {
    if (!isset($_POST['fxt_slug_nonce']) ||
        !wp_verify_nonce($_POST['fxt_slug_nonce'], 'fxt_slug_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (!isset($_POST['fxt_post_slug'])) return;

    $new_slug = sanitize_title(trim($_POST['fxt_post_slug']));
    if (!$new_slug) return;

    $post = get_post($post_id);
    if (!$post || $post->post_name === $new_slug) return;

    // Đảm bảo slug duy nhất trong cùng post type
    $unique_slug = wp_unique_post_slug(
        $new_slug,
        $post_id,
        $post->post_status,
        'broker_post',
        $post->post_parent
    );

    global $wpdb;
    $wpdb->update(
        $wpdb->posts,
        ['post_name' => $unique_slug],
        ['ID'        => $post_id],
        ['%s'],
        ['%d']
    );

    clean_post_cache($post_id);
});

// ╔═══════════════════════════════════════════════════════════════════╗
// ║  RELATED POSTS META BOX — Custom related posts cho sidebar       ║
// ╚═══════════════════════════════════════════════════════════════════╝

add_action('add_meta_boxes', function () {
    add_meta_box(
        'fxt_sub_related_posts',
        '🔗 Related Posts (Sidebar)',
        'fxt_sub_related_posts_html',
        'broker_post',
        'side',
        'default'
    );
});

function fxt_sub_related_posts_html($post) {
    wp_nonce_field('fxt_sub_related_meta', 'fxt_sub_related_nonce');

    $hide        = get_post_meta($post->ID, '_fxt_sub_related_hide', true);
    $related_ids = get_post_meta($post->ID, '_fxt_sub_related_ids', true);
    if (!is_array($related_ids)) $related_ids = [];

    $custom_title = get_post_meta($post->ID, '_fxt_sub_related_title', true);

    // Lấy parent broker
    $parent_id = get_post_meta($post->ID, '_fxt_parent_broker', true);
    $siblings  = [];
    if ($parent_id) {
        $siblings = get_posts([
            'post_type'   => 'broker_post',
            'meta_key'    => '_fxt_parent_broker',
            'meta_value'  => $parent_id,
            'numberposts' => -1,
            'post_status' => 'publish',
            'exclude'     => [$post->ID],
            'orderby'     => 'title',
            'order'       => 'ASC',
        ]);
    }
    ?>
    <style>
        .fxt-rel-hide-wrap { padding: 8px; background: #fff8e5; border-left: 3px solid #f0821e; border-radius: 3px; margin-bottom: 12px; }
        .fxt-rel-field { margin-bottom: 12px; }
        .fxt-rel-field label.fxt-rel-label { display: block; font-weight: 600; font-size: 12px; margin-bottom: 4px; color: #1e3a5f; }
        .fxt-rel-field input[type="text"] { width: 100%; padding: 6px 10px; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; }
        .fxt-rel-list { max-height: 280px; overflow-y: auto; border: 1px solid #ccd0d4; border-radius: 4px; padding: 8px; background: #fff; }
        .fxt-rel-item { display: flex; align-items: flex-start; gap: 8px; padding: 6px 4px; border-bottom: 1px solid #f0f0f0; }
        .fxt-rel-item:last-child { border-bottom: none; }
        .fxt-rel-item label { font-size: 12px; cursor: pointer; line-height: 1.4; }
        .fxt-rel-item input[type="checkbox"] { margin-top: 3px; flex-shrink: 0; }
        .fxt-rel-empty { padding: 16px; text-align: center; color: #888; font-style: italic; font-size: 12px; }
        .fxt-rel-hint { font-size: 11px; color: #666; margin-top: 6px; line-height: 1.5; font-style: italic; }
    </style>

    <div class="fxt-rel-hide-wrap">
        <label style="font-size:12px; display:flex; align-items:center; gap:6px;">
            <input type="checkbox" name="fxt_sub_related_hide" value="1" <?php checked($hide, '1'); ?>>
            <strong>🚫 Ẩn box Related Posts ở sidebar</strong>
        </label>
    </div>

    <div class="fxt-rel-field">
        <label class="fxt-rel-label">📌 Tiêu đề widget (tùy chọn):</label>
        <input type="text" name="fxt_sub_related_title"
               value="<?php echo esc_attr($custom_title); ?>"
               placeholder="Mặc định: 📚 More About {broker}">
        <p class="fxt-rel-hint">Để trống = dùng "📚 More About [Tên broker]". Có thể dùng <code>{broker}</code> để chèn tên broker.</p>
    </div>

    <div class="fxt-rel-field">
        <label class="fxt-rel-label">✅ Chọn bài hiển thị (cùng broker cha):</label>

        <?php if (empty($siblings)): ?>
            <div class="fxt-rel-empty">
                <?php echo $parent_id ? 'Chưa có broker_post nào khác cùng broker.' : '⚠️ Chọn broker cha và Save trước để thấy danh sách.'; ?>
            </div>
        <?php else: ?>
            <div class="fxt-rel-list">
                <?php foreach ($siblings as $sib): ?>
                <div class="fxt-rel-item">
                    <input type="checkbox"
                           id="fxt_rel_<?php echo $sib->ID; ?>"
                           name="fxt_sub_related_ids[]"
                           value="<?php echo $sib->ID; ?>"
                           <?php checked(in_array($sib->ID, $related_ids)); ?>>
                    <label for="fxt_rel_<?php echo $sib->ID; ?>">
                        <?php echo esc_html($sib->post_title); ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="fxt-rel-hint">💡 Không tích chọn bài nào = không hiện box. Cũng có thể tự động lấy theo bài pillar bằng cách xem mục "Bài pillar" bên dưới.</p>
        <?php endif; ?>
    </div>

    <div class="fxt-rel-field" style="margin-top:14px; padding-top:12px; border-top:1px solid #e0e0e0;">
        <label style="font-size:12px; display:flex; align-items:center; gap:6px;">
            <input type="checkbox" name="fxt_sub_related_show_pillar" value="1"
                   <?php checked(get_post_meta($post->ID, '_fxt_sub_related_show_pillar', true), '1'); ?>>
            <strong>⭐ Hiển thị link đến bài pillar (Review broker) ở đầu danh sách</strong>
        </label>
    </div>
    <?php
}

/**
 * Save related posts meta
 */
add_action('save_post_broker_post', function ($post_id) {
    if (!isset($_POST['fxt_sub_related_nonce']) ||
        !wp_verify_nonce($_POST['fxt_sub_related_nonce'], 'fxt_sub_related_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Hide flag
    if (!empty($_POST['fxt_sub_related_hide'])) {
        update_post_meta($post_id, '_fxt_sub_related_hide', '1');
    } else {
        delete_post_meta($post_id, '_fxt_sub_related_hide');
    }

    // Show pillar
    if (!empty($_POST['fxt_sub_related_show_pillar'])) {
        update_post_meta($post_id, '_fxt_sub_related_show_pillar', '1');
    } else {
        delete_post_meta($post_id, '_fxt_sub_related_show_pillar');
    }

    // Custom title
    if (isset($_POST['fxt_sub_related_title'])) {
        $title = sanitize_text_field($_POST['fxt_sub_related_title']);
        if ($title) {
            update_post_meta($post_id, '_fxt_sub_related_title', $title);
        } else {
            delete_post_meta($post_id, '_fxt_sub_related_title');
        }
    }

    // Selected IDs
    $ids = [];
    if (!empty($_POST['fxt_sub_related_ids']) && is_array($_POST['fxt_sub_related_ids'])) {
        foreach ($_POST['fxt_sub_related_ids'] as $id) {
            $id = intval($id);
            if ($id > 0) $ids[] = $id;
        }
    }
    if ($ids) {
        update_post_meta($post_id, '_fxt_sub_related_ids', $ids);
    } else {
        delete_post_meta($post_id, '_fxt_sub_related_ids');
    }
});

/**
 * Helper: Lấy data related posts cho sidebar
 * Returns: ['hidden' => bool, 'title' => string, 'show_pillar' => bool, 'parent' => array, 'posts' => array]
 */
function fxt_get_sub_post_related_data($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();

    $hidden = get_post_meta($post_id, '_fxt_sub_related_hide', true) === '1';
    if ($hidden) {
        return ['hidden' => true];
    }

    $ids         = get_post_meta($post_id, '_fxt_sub_related_ids', true);
    $show_pillar = get_post_meta($post_id, '_fxt_sub_related_show_pillar', true) === '1';
    $custom_title = get_post_meta($post_id, '_fxt_sub_related_title', true);
    $parent      = function_exists('fxt_get_parent_broker') ? fxt_get_parent_broker($post_id) : null;

    $posts = [];
    if (is_array($ids) && !empty($ids)) {
        $posts = get_posts([
            'post_type'   => 'broker_post',
            'post__in'    => $ids,
            'orderby'     => 'post__in',
            'numberposts' => -1,
            'post_status' => 'publish',
        ]);
    }

    // Nếu không có pillar và không chọn bài nào → coi như không hiện
    if (empty($posts) && (!$show_pillar || !$parent)) {
        return ['hidden' => true];
    }

    // Build title
    $broker_name = $parent ? $parent['title'] : '';
    if ($custom_title) {
        $title = str_replace('{broker}', $broker_name, $custom_title);
    } else {
        $title = $broker_name ? '📚 More About ' . $broker_name : '📚 Related Posts';
    }

    return [
        'hidden'      => false,
        'title'       => $title,
        'show_pillar' => $show_pillar,
        'parent'      => $parent,
        'posts'       => $posts,
    ];
}
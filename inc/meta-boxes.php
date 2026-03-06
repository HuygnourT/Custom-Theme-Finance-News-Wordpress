<?php
/**
 * Meta Boxes - Custom Fields cho Broker
 * 
 * FIX 1: Thêm 'classic-editor' support để Gutenberg không block meta box
 * FIX 2: Lưu pros/cons dùng wp_kses_post thay vì sanitize_text_field
 *         để giữ line breaks
 * NEW: Thêm Broker Sections (tabs, per-section pros/cons, collapsible details)
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

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

    add_meta_box(
        'fxt_broker_sections',
        '📑 Broker Content Sections (Tabs / Collapsible)',
        'fxt_broker_sections_meta_box_html',
        'broker',
        'normal',
        'default'
    );
});

/**
 * FIX: Tắt Gutenberg cho broker post type
 */
add_filter('use_block_editor_for_post_type', function ($use_block_editor, $post_type) {
    if ($post_type === 'broker') {
        return false;
    }
    return $use_block_editor;
}, 10, 2);

/**
 * Render HTML cho meta box chính
 */
function fxt_broker_meta_box_html($post) {
    wp_nonce_field('fxt_broker_meta', 'fxt_broker_meta_nonce');

    $fields = [
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

/**
 * Render HTML cho Broker Sections meta box
 * Mỗi section = 1 tab trên frontend
 */
function fxt_broker_sections_meta_box_html($post) {
    $sections = get_post_meta($post->ID, '_fxt_broker_sections', true);
    if (!is_array($sections)) $sections = [];
    ?>

    <style>
        .fxt-sections-wrap { margin-top: 10px; }
        .fxt-section-item { background: #f9fafb; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 16px; position: relative; }
        .fxt-section-item .fxt-section-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; cursor: move; }
        .fxt-section-item .fxt-section-number { background: #2271b1; color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0; }
        .fxt-section-item .fxt-remove-section { position: absolute; top: 12px; right: 12px; background: #d63638; color: #fff; border: none; border-radius: 4px; padding: 4px 10px; cursor: pointer; font-size: 12px; }
        .fxt-section-item .fxt-remove-section:hover { background: #b32d2e; }
        .fxt-section-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .fxt-section-field { margin-bottom: 10px; }
        .fxt-section-field label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 13px; color: #1e3a5f; }
        .fxt-section-field input,
        .fxt-section-field textarea,
        .fxt-section-field select { width: 100%; padding: 6px 10px; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; }
        .fxt-section-field textarea { min-height: 80px; }
        .fxt-section-field input:focus,
        .fxt-section-field textarea:focus { border-color: #2271b1; outline: none; }
        .fxt-section-proscons { background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 14px; margin-top: 8px; }
        .fxt-section-proscons h5 { margin: 0 0 8px; font-size: 13px; color: #1e3a5f; }
        .fxt-add-section { margin-top: 12px; }
        .fxt-section-hint { font-size: 11px; color: #888; margin-top: 3px; font-style: italic; }
        .fxt-section-full { grid-column: 1 / -1; }
        .fxt-checkbox-field { display: flex; align-items: center; gap: 8px; margin-top: 6px; }
        .fxt-checkbox-field input[type="checkbox"] { width: auto; }
    </style>

    <p style="margin-bottom:16px; color:#555;">
        Mỗi section sẽ hiển thị dưới dạng <strong>tab ngang</strong> ở đầu trang broker.
        Click vào tab → cuộn tới nội dung. Mỗi section có thể có <strong>Pros/Cons riêng</strong> và <strong>nội dung ẩn/hiện</strong>.
    </p>

    <div class="fxt-sections-wrap" id="fxt-sections-wrap">
        <?php
        if (!empty($sections)):
            foreach ($sections as $i => $sec):
                fxt_render_section_fields($i, $sec);
            endforeach;
        endif;
        ?>
    </div>

    <button type="button" class="button button-primary fxt-add-section" id="fxt-add-section">
        ➕ Add New Section
    </button>

    <!-- Template ẩn cho JS clone -->
    <script type="text/html" id="fxt-section-template">
        <?php fxt_render_section_fields('__INDEX__', []); ?>
    </script>

    <script>
    (function(){
        var wrap = document.getElementById('fxt-sections-wrap');
        var addBtn = document.getElementById('fxt-add-section');
        var template = document.getElementById('fxt-section-template').innerHTML;

        function getNextIndex() {
            var items = wrap.querySelectorAll('.fxt-section-item');
            return items.length;
        }

        function reindex() {
            var items = wrap.querySelectorAll('.fxt-section-item');
            items.forEach(function(item, idx) {
                item.querySelector('.fxt-section-number').textContent = (idx + 1);
                // Update all input/textarea/select names
                item.querySelectorAll('[name]').forEach(function(el) {
                    el.name = el.name.replace(/fxt_sections\[\d+\]/, 'fxt_sections[' + idx + ']');
                });
            });
        }

        addBtn.addEventListener('click', function() {
            var idx = getNextIndex();
            var html = template.replace(/__INDEX__/g, idx);
            var div = document.createElement('div');
            div.innerHTML = html.trim();
            var newItem = div.firstElementChild;
            wrap.appendChild(newItem);
            bindRemove(newItem);
            reindex();
        });

        function bindRemove(item) {
            var btn = item.querySelector('.fxt-remove-section');
            if (btn) {
                btn.addEventListener('click', function() {
                    if (confirm('Remove this section?')) {
                        item.remove();
                        reindex();
                    }
                });
            }
        }

        // Bind existing
        wrap.querySelectorAll('.fxt-section-item').forEach(bindRemove);
    })();
    </script>

    <?php
}

/**
 * Render fields cho 1 section
 */
function fxt_render_section_fields($index, $data) {
    $title           = $data['title'] ?? '';
    $content         = $data['content'] ?? '';
    $show_proscons   = !empty($data['show_proscons']) ? '1' : '';
    $pros            = $data['pros'] ?? '';
    $cons            = $data['cons'] ?? '';
    $collapsible     = !empty($data['collapsible']) ? '1' : '';
    $collapse_detail = $data['collapse_detail'] ?? '';
    $show_text       = $data['show_text'] ?? '';
    $hide_text       = $data['hide_text'] ?? '';
    $num = is_numeric($index) ? ($index + 1) : '#';
    ?>
    <div class="fxt-section-item">
        <div class="fxt-section-header">
            <span class="fxt-section-number"><?php echo $num; ?></span>
            <strong style="flex:1"><?php echo $title ? esc_html($title) : 'New Section'; ?></strong>
        </div>
        <button type="button" class="fxt-remove-section">✕ Remove</button>

        <div class="fxt-section-grid">
            <div class="fxt-section-field">
                <label>📌 Tab Title (hiển thị trên tab ngang)</label>
                <input type="text" name="fxt_sections[<?php echo $index; ?>][title]"
                       value="<?php echo esc_attr($title); ?>"
                       placeholder="e.g. Spreads & Fees, Platforms, Safety...">
            </div>

            <div class="fxt-section-field">
                <div class="fxt-checkbox-field">
                    <input type="checkbox" name="fxt_sections[<?php echo $index; ?>][show_proscons]" value="1"
                           <?php checked($show_proscons, '1'); ?>>
                    <label>✅❌ Show Pros/Cons for this section</label>
                </div>
                <div class="fxt-checkbox-field" style="margin-top:8px">
                    <input type="checkbox" name="fxt_sections[<?php echo $index; ?>][collapsible]" value="1"
                           <?php checked($collapsible, '1'); ?>>
                    <label>🔽 Make this section collapsible (show/hide detail)</label>
                </div>
            </div>

            <div class="fxt-section-field fxt-section-full">
                <label>📝 Section Content</label>
                <textarea name="fxt_sections[<?php echo $index; ?>][content]" rows="6"
                          placeholder="Nội dung chi tiết cho section này (hỗ trợ HTML)..."><?php echo esc_textarea($content); ?></textarea>
                <p class="fxt-section-hint">HTML is supported. This content appears under the section heading.</p>
            </div>
        </div>

        <div class="fxt-section-proscons">
            <h5>Pros/Cons riêng cho section này (chỉ hiện khi bật checkbox ở trên)</h5>
            <div class="fxt-section-grid">
                <div class="fxt-section-field">
                    <label>✅ Pros (one per line)</label>
                    <textarea name="fxt_sections[<?php echo $index; ?>][pros]" rows="3"
                              placeholder="Low spread&#10;Fast execution"><?php echo esc_textarea($pros); ?></textarea>
                </div>
                <div class="fxt-section-field">
                    <label>❌ Cons (one per line)</label>
                    <textarea name="fxt_sections[<?php echo $index; ?>][cons]" rows="3"
                              placeholder="High swap fees&#10;Limited tools"><?php echo esc_textarea($cons); ?></textarea>
                </div>
            </div>
        </div>

        <div style="margin-top:12px; background:#fff; border:1px solid #e0e0e0; border-radius:6px; padding:14px;">
            <h5 style="margin:0 0 8px; font-size:13px; color:#1e3a5f;">🔽 Collapsible Detail (chỉ hiện khi bật checkbox)</h5>
            <div class="fxt-section-field">
                <label>Hidden detail content (hiện khi click "Show more")</label>
                <textarea name="fxt_sections[<?php echo $index; ?>][collapse_detail]" rows="4"
                          placeholder="Nội dung chi tiết ẩn, hiện khi user click expand..."><?php echo esc_textarea($collapse_detail); ?></textarea>
                <p class="fxt-section-hint">HTML is supported. This content is hidden by default and shown when user clicks.</p>
            </div>
            <div class="fxt-section-grid">
                <div class="fxt-section-field">
                    <label>Text "Show more" button</label>
                    <input type="text" name="fxt_sections[<?php echo $index; ?>][show_text]"
                           value="<?php echo esc_attr($show_text); ?>"
                           placeholder="Leave empty to use default from Customizer">
                </div>
                <div class="fxt-section-field">
                    <label>Text "Show less" button</label>
                    <input type="text" name="fxt_sections[<?php echo $index; ?>][hide_text]"
                           value="<?php echo esc_attr($hide_text); ?>"
                           placeholder="Leave empty to use default from Customizer">
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Lưu meta data khi save post
 */
add_action('save_post_broker', function ($post_id) {

    // Kiểm tra nonce
    if (!isset($_POST['fxt_broker_meta_nonce']) ||
        !wp_verify_nonce($_POST['fxt_broker_meta_nonce'], 'fxt_broker_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Text fields
    $text_fields = [
        'fxt_rating'         => '_fxt_rating',
        'fxt_spread'         => '_fxt_spread',
        'fxt_leverage'       => '_fxt_leverage',
        'fxt_min_deposit'    => '_fxt_min_deposit',
        'fxt_regulation'     => '_fxt_regulation',
        'fxt_founded'        => '_fxt_founded',
        'fxt_platforms'      => '_fxt_platforms',
    ];

    $textarea_fields = [
        'fxt_pros'           => '_fxt_pros',
        'fxt_cons'           => '_fxt_cons',
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

    // === Save Broker Sections ===
    if (isset($_POST['fxt_sections']) && is_array($_POST['fxt_sections'])) {
        $sections = [];
        foreach ($_POST['fxt_sections'] as $sec) {
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
            ];
        }
        update_post_meta($post_id, '_fxt_broker_sections', $sections);
    } else {
        delete_post_meta($post_id, '_fxt_broker_sections');
    }
});

/**
 * Helper: Lấy tất cả meta data của broker
 */
function fxt_get_broker_meta($post_id) {
    return [
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
 * Helper: Lấy broker sections
 */
function fxt_get_broker_sections($post_id) {
    $sections = get_post_meta($post_id, '_fxt_broker_sections', true);
    if (!is_array($sections)) return [];
    
    // Process pros/cons thành arrays
    foreach ($sections as &$sec) {
        $sec['pros_arr'] = array_filter(array_map('trim', explode("\n", $sec['pros'] ?? '')));
        $sec['cons_arr'] = array_filter(array_map('trim', explode("\n", $sec['cons'] ?? '')));
    }
    return $sections;
}

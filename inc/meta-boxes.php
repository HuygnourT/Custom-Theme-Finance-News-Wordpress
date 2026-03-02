<?php
/**
 * Meta Boxes - Custom Fields cho Broker
 * 
 * FIX 1: Thêm 'classic-editor' support để Gutenberg không block meta box
 * FIX 2: Lưu pros/cons dùng wp_kses_post thay vì sanitize_text_field
 *         để giữ line breaks
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
});

/**
 * FIX: Tắt Gutenberg cho broker post type
 * Gutenberg + classic meta box = conflict → không save được
 */
add_filter('use_block_editor_for_post_type', function ($use_block_editor, $post_type) {
    if ($post_type === 'broker') {
        return false; // Dùng Classic Editor cho broker
    }
    return $use_block_editor;
}, 10, 2);

/**
 * Render HTML cho meta box
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
 * Lưu meta data khi save post
 * FIX: Dùng sanitize_textarea_field cho pros/cons để giữ \n
 */
add_action('save_post_broker', function ($post_id) {

    // Kiểm tra nonce
    if (!isset($_POST['fxt_broker_meta_nonce']) ||
        !wp_verify_nonce($_POST['fxt_broker_meta_nonce'], 'fxt_broker_meta')) {
        return;
    }

    // Không lưu khi autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Kiểm tra quyền
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Text fields - sanitize_text_field (1 dòng)
    $text_fields = [
        'fxt_rating'         => '_fxt_rating',
        'fxt_spread'         => '_fxt_spread',
        'fxt_leverage'       => '_fxt_leverage',
        'fxt_min_deposit'    => '_fxt_min_deposit',
        'fxt_regulation'     => '_fxt_regulation',
        'fxt_founded'        => '_fxt_founded',
        'fxt_platforms'      => '_fxt_platforms',
    ];

    // Textarea fields - sanitize_textarea_field (giữ line breaks)
    $textarea_fields = [
        'fxt_pros'           => '_fxt_pros',
        'fxt_cons'           => '_fxt_cons',
    ];

    // URL fields
    $url_fields = [
        'fxt_affiliate_link' => '_fxt_affiliate_link',
        'fxt_website_url'    => '_fxt_website_url',
    ];

    foreach ($text_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_key]));
        }
    }

    // FIX: Dùng sanitize_textarea_field thay vì sanitize_text_field
    // sanitize_textarea_field GIỮ line breaks (\n)
    // sanitize_text_field XÓA line breaks → pros/cons thành 1 dòng
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

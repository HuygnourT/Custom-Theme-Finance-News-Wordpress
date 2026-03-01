<?php
/**
 * Meta Boxes - Custom Fields cho Broker
 * 
 * Meta box = form nhập liệu thêm trong editor
 * Giống việc thêm fields vào form trong frontend app
 * 
 * Mỗi broker sẽ có: rating, spread, leverage, min deposit,
 * regulation, affiliate link, pros/cons...
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

/**
 * Đăng ký Meta Box trong trang editor Broker
 */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'fxt_broker_details',           // ID
        'Thông Tin Broker',             // Tiêu đề hiển thị
        'fxt_broker_meta_box_html',     // Callback render HTML
        'broker',                       // Post type
        'normal',                       // Vị trí: normal (dưới editor), side (sidebar)
        'high'                          // Ưu tiên: high (hiện trên cùng)
    );
});

/**
 * Render HTML cho meta box
 * Đây là form nhập liệu trong WP Admin
 */
function fxt_broker_meta_box_html($post) {
    // Nonce field - bảo mật chống CSRF (giống csrf token trong Express)
    wp_nonce_field('fxt_broker_meta', 'fxt_broker_meta_nonce');

    // Lấy giá trị đã lưu (nếu có)
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
    </style>

    <div class="fxt-meta-grid">
        <!-- Cột trái -->
        <div>
            <div class="fxt-meta-field">
                <label for="fxt_rating">⭐ Điểm đánh giá (0-10)</label>
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
                <label for="fxt_leverage">📈 Đòn bẩy tối đa</label>
                <input type="text" id="fxt_leverage" name="fxt_leverage"
                       value="<?php echo esc_attr($fields['leverage']); ?>"
                       placeholder="1:2000">
            </div>

            <div class="fxt-meta-field">
                <label for="fxt_min_deposit">💰 Nạp tối thiểu</label>
                <input type="text" id="fxt_min_deposit" name="fxt_min_deposit"
                       value="<?php echo esc_attr($fields['min_deposit']); ?>"
                       placeholder="$1">
            </div>
        </div>

        <!-- Cột phải -->
        <div>
            <div class="fxt-meta-field">
                <label for="fxt_regulation">🏛 Giấy phép</label>
                <input type="text" id="fxt_regulation" name="fxt_regulation"
                       value="<?php echo esc_attr($fields['regulation']); ?>"
                       placeholder="FCA, CySEC, ASIC...">
            </div>

            <div class="fxt-meta-field">
                <label for="fxt_founded">📅 Năm thành lập</label>
                <input type="text" id="fxt_founded" name="fxt_founded"
                       value="<?php echo esc_attr($fields['founded']); ?>"
                       placeholder="2008">
            </div>

            <div class="fxt-meta-field">
                <label for="fxt_platforms">🖥 Nền tảng giao dịch</label>
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

    <!-- Website URL -->
    <div class="fxt-meta-field">
        <label for="fxt_website_url">🌐 Website chính thức</label>
        <input type="url" id="fxt_website_url" name="fxt_website_url"
               value="<?php echo esc_attr($fields['website_url']); ?>"
               placeholder="https://exness.com">
    </div>

    <!-- Ưu/Nhược điểm -->
    <div class="fxt-meta-section">
        <h4>✅ Ưu điểm (mỗi ưu điểm 1 dòng)</h4>
        <textarea id="fxt_pros" name="fxt_pros" rows="4"
                  placeholder="Spread thấp&#10;Rút tiền nhanh&#10;Hỗ trợ tiếng Việt"><?php echo esc_textarea($fields['pros']); ?></textarea>
    </div>

    <div class="fxt-meta-section">
        <h4>❌ Nhược điểm (mỗi nhược điểm 1 dòng)</h4>
        <textarea id="fxt_cons" name="fxt_cons" rows="4"
                  placeholder="Phí qua đêm cao&#10;Không có bonus"><?php echo esc_textarea($fields['cons']); ?></textarea>
    </div>

    <?php
}

/**
 * Lưu meta data khi save post
 * Giống xử lý POST request trong Express
 */
add_action('save_post_broker', function ($post_id) {

    // Kiểm tra nonce (CSRF protection)
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

    // Danh sách fields cần lưu
    $text_fields = [
        'fxt_rating'         => '_fxt_rating',
        'fxt_spread'         => '_fxt_spread',
        'fxt_leverage'       => '_fxt_leverage',
        'fxt_min_deposit'    => '_fxt_min_deposit',
        'fxt_regulation'     => '_fxt_regulation',
        'fxt_founded'        => '_fxt_founded',
        'fxt_platforms'      => '_fxt_platforms',
        'fxt_pros'           => '_fxt_pros',
        'fxt_cons'           => '_fxt_cons',
    ];

    $url_fields = [
        'fxt_affiliate_link' => '_fxt_affiliate_link',
        'fxt_website_url'    => '_fxt_website_url',
    ];

    // Lưu text fields
    foreach ($text_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_key]));
        }
    }

    // Lưu URL fields
    foreach ($url_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, esc_url_raw($_POST[$form_key]));
        }
    }
});

/**
 * Helper: Lấy tất cả meta data của broker
 * Dùng trong template: $broker = fxt_get_broker_meta($post->ID);
 */
function fxt_get_broker_meta($post_id) {
    return [
        'rating'         => get_post_meta($post_id, '_fxt_rating', true),
        'spread'         => get_post_meta($post_id, '_fxt_spread', true),
        'leverage'       => get_post_meta($post_id, '_fxt_leverage', true),
        'min_deposit'    => get_post_meta($post_id, '_fxt_min_deposit', true),
        'regulation'     => get_post_meta($post_id, '_fxt_regulation', true),
        'founded'        => get_post_meta($post_id, '_fxt_founded', true),
        'platforms'       => get_post_meta($post_id, '_fxt_platforms', true),
        'affiliate_link' => get_post_meta($post_id, '_fxt_affiliate_link', true),
        'website_url'    => get_post_meta($post_id, '_fxt_website_url', true),
        'pros'           => array_filter(explode("\n", get_post_meta($post_id, '_fxt_pros', true) ?: '')),
        'cons'           => array_filter(explode("\n", get_post_meta($post_id, '_fxt_cons', true) ?: '')),
    ];
}

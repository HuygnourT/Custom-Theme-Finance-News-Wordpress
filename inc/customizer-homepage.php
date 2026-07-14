<?php
/**
 * inc/customizer-homepage.php — Tùy chỉnh trang chủ (không hard-code)
 *
 * Mỗi section đều có ô "Ẩn section này" (mặc định HIỆN — bỏ trống = hiện).
 * Guide By Topic: mỗi ô = 1 broker, danh sách bên dưới là LINK TỰ NHẬP
 * (mỗi dòng: "Tiêu đề | https://link", không giới hạn số dòng).
 *
 * Require trong functions.php, đặt SAU customizer.php.
 *
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

if (!function_exists('fxt_sanitize_checkbox')) {
    function fxt_sanitize_checkbox($v) { return $v ? '1' : ''; }
}

/** Helper đăng ký 1 field nhanh */
function fxt_home_field($wp, $section, $id, $label, $default = '', $type = 'text', $desc = '') {
    $map = [
        'text'          => 'sanitize_text_field',
        'textarea'      => 'wp_kses_post',
        'textarea_raw'  => 'sanitize_textarea_field',
        'url'           => 'esc_url_raw',
        'number'        => 'absint',
        'checkbox'      => 'fxt_sanitize_checkbox',
    ];
    $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => $map[$type] ?? 'sanitize_text_field']);

    $ctrl_type = ($type === 'textarea_raw') ? 'textarea' : $type;
    $args = ['label' => $label, 'section' => $section, 'type' => $ctrl_type];
    if ($desc) $args['description'] = $desc;
    if ($type === 'number') $args['input_attrs'] = ['min' => 1, 'max' => 12, 'step' => 1];
    $wp->add_control($id, $args);
}

add_action('customize_register', function ($wp_customize) {

    // ╔═══════════════════════════════════════════════╗
    // ║  SECTION 2 — BROKERS (tiêu đề + text thêm)    ║
    // ║  Đã gộp: "🏠 Homepage - Sections" (title +     ║
    // ║  view-all) và "🏠 Home · Brokers" (text thêm)  ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_home_brokers', ['title' => '🏠 Home · Brokers', 'priority' => 26]);
    fxt_home_field($wp_customize, 'fxt_home_brokers', 'fxt_home_brokers_text_top',    'Text phía TRÊN danh sách broker', '', 'textarea');
    fxt_home_field($wp_customize, 'fxt_home_brokers', 'fxt_home_brokers_text_bottom', 'Text phía DƯỚI danh sách broker', '', 'textarea');
    fxt_home_field($wp_customize, 'fxt_home_brokers', 'fxt_home_brokers_note',        'Khung Note (có viền)', '', 'textarea', 'Để trống = ẩn. Cho phép HTML cơ bản.');

    // ╔═══════════════════════════════════════════════╗
    // ║  SECTION 3 — HOW WE CAN HELP                  ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_home_help', ['title' => '🏠 Home · How We Can Help', 'priority' => 27]);
    fxt_home_field($wp_customize, 'fxt_home_help', 'fxt_home_help_hide', 'Ẩn section này', '', 'checkbox');
    fxt_home_field($wp_customize, 'fxt_home_help', 'fxt_home_help_title', 'Tiêu đề', 'How We Can Help You', 'text');
    fxt_home_field($wp_customize, 'fxt_home_help', 'fxt_home_help_intro', 'Đoạn intro (đầu tiên)',
        'Whether you are a beginner or an experienced trader, our resources guide you to the right broker and smarter decisions.', 'textarea');

    $help_box_defaults = [
        1 => ['🔍', 'In-depth Reviews', 'Objective, data-driven broker reviews covering spreads, regulation and execution.'],
        2 => ['⚖️', 'Side-by-side Comparison', 'Compare brokers on the metrics that matter so you can choose with confidence.'],
        3 => ['📚', 'Free Education', 'Guides and strategies to help you trade safely and consistently.'],
    ];
    foreach ($help_box_defaults as $i => $d) {
        fxt_home_field($wp_customize, 'fxt_home_help', "fxt_home_help_box{$i}_icon",  "Ô {$i} · Icon (emoji)", $d[0], 'text');
        fxt_home_field($wp_customize, 'fxt_home_help', "fxt_home_help_box{$i}_title", "Ô {$i} · Tiêu đề",       $d[1], 'text');
        fxt_home_field($wp_customize, 'fxt_home_help', "fxt_home_help_box{$i}_text",  "Ô {$i} · Nội dung",      $d[2], 'textarea');
    }
    fxt_home_field($wp_customize, 'fxt_home_help', 'fxt_home_help_cta_text', 'CTA dưới cùng · Text', 'Explore our beginner guide →', 'text');
    fxt_home_field($wp_customize, 'fxt_home_help', 'fxt_home_help_cta_url',  'CTA dưới cùng · Link', '', 'url', 'Để trống = ẩn nút.');

    // ╔═══════════════════════════════════════════════╗
    // ║  SECTION 4 — GUIDE BY TOPIC (custom links)    ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_home_guide', [
        'title'       => '🏠 Home · Guide By Topic',
        'description' => 'Mỗi ô = 1 broker. Danh sách bên dưới là link tự nhập (không giới hạn số dòng).',
        'priority'    => 28,
    ]);
    fxt_home_field($wp_customize, 'fxt_home_guide', 'fxt_home_guide_hide',  'Ẩn section này', '', 'checkbox');
    fxt_home_field($wp_customize, 'fxt_home_guide', 'fxt_home_guide_title', 'Tiêu đề', 'Guide By Topic', 'text');
    fxt_home_field($wp_customize, 'fxt_home_guide', 'fxt_home_guide_intro', 'Đoạn intro (tùy chọn)', '', 'textarea');

    // Danh sách broker để chọn làm header mỗi ô
    $broker_choices = ['' => '— Chọn broker —'];
    $bks = get_posts(['post_type'=>'broker','numberposts'=>-1,'orderby'=>'title','order'=>'ASC','post_status'=>'publish']);
    foreach ($bks as $b) $broker_choices[$b->ID] = $b->post_title;

    for ($i = 1; $i <= 6; $i++) {
        // Header = broker
        $wp_customize->add_setting("fxt_home_guide_item{$i}_broker", ['default'=>'', 'sanitize_callback'=>'absint']);
        $wp_customize->add_control("fxt_home_guide_item{$i}_broker", [
            'label'=>"Ô {$i} · Broker (header — để trống = ẩn ô)", 'section'=>'fxt_home_guide', 'type'=>'select', 'choices'=>$broker_choices,
        ]);

        // Danh sách link tự nhập, mỗi dòng "Tiêu đề | URL"
        $wp_customize->add_setting("fxt_home_guide_item{$i}_links", ['default'=>'', 'sanitize_callback'=>'sanitize_textarea_field']);
        $wp_customize->add_control("fxt_home_guide_item{$i}_links", [
            'label'       => "Ô {$i} · Danh sách link",
            'description' => 'Mỗi dòng 1 link, dạng: <code>Tiêu đề | https://link</code>. Không giới hạn số dòng.',
            'section'     => 'fxt_home_guide',
            'type'        => 'textarea',
        ]);

        // Nút CTA dưới cùng (tùy chọn)
        $wp_customize->add_setting("fxt_home_guide_item{$i}_link_text", ['default'=>'', 'sanitize_callback'=>'sanitize_text_field']);
        $wp_customize->add_control("fxt_home_guide_item{$i}_link_text", [
            'label'=>"Ô {$i} · Nút CTA · Text (tùy chọn)", 'section'=>'fxt_home_guide', 'type'=>'text',
        ]);
        $wp_customize->add_setting("fxt_home_guide_item{$i}_link_url", ['default'=>'', 'sanitize_callback'=>'esc_url_raw']);
        $wp_customize->add_control("fxt_home_guide_item{$i}_link_url", [
            'label'=>"Ô {$i} · Nút CTA · URL (tùy chọn)", 'section'=>'fxt_home_guide', 'type'=>'url',
        ]);
    }

    // ╔═══════════════════════════════════════════════╗
    // ║  SECTION 5 — EVERYTHING YOU NEED TO KNOW      ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_home_eyntk', ['title' => '🏠 Home · Everything You Need', 'priority' => 29]);
    fxt_home_field($wp_customize, 'fxt_home_eyntk', 'fxt_home_eyntk_hide',  'Ẩn section này', '', 'checkbox');
    fxt_home_field($wp_customize, 'fxt_home_eyntk', 'fxt_home_eyntk_title', 'Tiêu đề', 'Everything You Need to Know', 'text');
    fxt_home_field($wp_customize, 'fxt_home_eyntk', 'fxt_home_eyntk_intro', 'Đoạn intro (tùy chọn)', '', 'textarea');
    for ($i = 1; $i <= 4; $i++) {
        fxt_home_field($wp_customize, 'fxt_home_eyntk', "fxt_home_eyntk_b{$i}_title", "Khối {$i} · Tiêu đề (để trống = ẩn)", '', 'text');
        fxt_home_field($wp_customize, 'fxt_home_eyntk', "fxt_home_eyntk_b{$i}_text",  "Khối {$i} · Nội dung", '', 'textarea');
    }

    // ╔═══════════════════════════════════════════════╗
    // ║  SECTION 6 — ABOUT US                         ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_home_about', ['title' => '🏠 Home · About Us', 'priority' => 30]);
    fxt_home_field($wp_customize, 'fxt_home_about', 'fxt_home_about_hide', 'Ẩn section này', '', 'checkbox');
    fxt_home_field($wp_customize, 'fxt_home_about', 'fxt_home_about_title', 'Bên trái · Tiêu đề', 'About Us', 'text');
    fxt_home_field($wp_customize, 'fxt_home_about', 'fxt_home_about_text',  'Bên trái · Nội dung chính',
        'FX Trading Today delivers independent broker reviews and practical trading education to help investors make informed decisions.', 'textarea');
    fxt_home_field($wp_customize, 'fxt_home_about', 'fxt_home_about_right_title', 'Bên phải · Tiêu đề', 'Why Traders Trust Us', 'text');
    for ($i = 1; $i <= 6; $i++) {
        fxt_home_field($wp_customize, 'fxt_home_about', "fxt_home_about_item{$i}_title", "Bên phải · Mục {$i} · Tiêu đề (để trống = ẩn)", '', 'text');
        fxt_home_field($wp_customize, 'fxt_home_about', "fxt_home_about_item{$i}_text",  "Bên phải · Mục {$i} · Mô tả (tùy chọn)", '', 'text');
    }
    fxt_home_field($wp_customize, 'fxt_home_about', 'fxt_home_about_disclaimer', 'Risk Disclaimer (dưới cùng)',
        '⚠️ Forex/CFD trading involves high risk. You may lose all of your invested capital.', 'textarea', 'Để trống = ẩn.');
});

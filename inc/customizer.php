<?php
/**
 * Theme Customizer v2
 * TẤT CẢ text đều custom được từ Appearance → Customize
 * Nội dung lưu trong database (wp_options) → update theme không mất
 */
if (!defined('ABSPATH')) exit;

add_action('customize_register', function ($wp_customize) {

    // ╔═══════════════════════════════════════════════╗
    // ║  1. HERO SECTION (HOMEPAGE)                   ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_hero', [
        'title'    => '🏠 Homepage',
        'priority' => 25,
    ]);

    $hero_fields = [
        'fxt_hero_badge'    => ['Badge text', 'Latest Forex Broker Reviews ' . date('Y')],
        'fxt_hero_title'    => ['Main Title (use {accent} to highlight text)', '{accent}Trusted{/accent} Forex Broker Reviews for Investors'],
        'fxt_hero_desc'     => ['Description', 'Detailed comparison of top Forex brokers. Objective reviews of spreads, leverage, regulation, and real trading experience'],
        'fxt_hero_btn1'     => ['Primary Button', 'View Broker Reviews'],
        'fxt_hero_btn2'     => ['Secondary Button', 'Forex Education'],
        'fxt_hero_stat1_num'   => ['Statistics 1 - Số', '15+'],
        'fxt_hero_stat1_label' => ['Statistics 1 - Label', 'Brokers Reviewed'],
        'fxt_hero_stat2_num'   => ['Statistics 2 - Số', '200+'],
        'fxt_hero_stat2_label' => ['Statistics 2 - Label', 'Educational Articles'],
        'fxt_hero_stat3_num'   => ['Statistics 3 - Số', '50K+'],
        'fxt_hero_stat3_label' => ['Statistics 3 - Label', 'Monthly Readers'],
    ];

    foreach ($hero_fields as $id => [$label, $default]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'fxt_hero', 'type' => 'text']);
    }

    // ╔═══════════════════════════════════════════════╗
    // ║  2. SECTION TITLES (HOME PAGE)                ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_sections', [
        'title'    => '🏠 Trang chủ - Sections',
        'priority' => 26,
    ]);

    $section_fields = [
        'fxt_section_brokers'    => ['Title Top Brokers', '🏆 Top Recommended Brokers'],
        'fxt_section_latest'     => ['Title Bài viết mới', '📝 Latest Articles'],
        'fxt_section_knowledge'  => ['Title Kiến thức', '📚 Education'],
        'fxt_section_viewall'    => ['Text "View All"', 'View All →'],
        'fxt_cta_title'          => ['CTA - Title', 'Start Trading Forex Today'],
        'fxt_cta_desc'           => ['CTA - Description', 'Compare and choose the best broker for your needs'],
        'fxt_cta_btn'            => ['CTA - Button', 'Compare Forex Brokers →'],
        'fxt_knowledge_category' => ['Slug category education', 'education'],
    ];

    foreach ($section_fields as $id => [$label, $default]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'fxt_sections', 'type' => 'text']);
    }

    // ╔═══════════════════════════════════════════════╗
    // ║  3. BROKER REVIEW LABELS                      ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_broker_labels', [
        'title'    => '📊 Labels trang Broker',
        'priority' => 27,
    ]);

    $broker_fields = [
        'fxt_broker_review_prefix' => ['Prefix Title review', 'Review'],
        'fxt_broker_open_account'  => ['Text Open Account Button', 'Open Account'],
        'fxt_broker_read_review'   => ['Text Read Review Button', 'Read Review'],
        'fxt_broker_overview'      => ['Title Overview', 'Overview'],
        'fxt_broker_pros_title'    => ['Title Pros', '✅ Pros'],
        'fxt_broker_cons_title'    => ['Title Cons', '❌ Cons'],
        'fxt_broker_cta_ready'     => ['CTA - Ready text (use {name} for the broker name)', 'Are you ready to trade with {name}?'],
        'fxt_broker_cta_desc'      => ['CTA - Description', 'Open an account in just a few minutes and start trading today.'],
        'fxt_broker_cta_btn'       => ['CTA - Button text', 'Get Started →'],
        // Spec labels
        'fxt_label_spread'     => ['Label: Spread', 'Spread'],
        'fxt_label_leverage'   => ['Label: Leverage', 'Leverage'],
        'fxt_label_deposit'    => ['Label: Minimum Deposit', 'Minimum Deposit'],
        'fxt_label_regulation' => ['Label: Regulation', 'Regulation'],
        'fxt_label_platforms'  => ['Label: Platform', 'Platform'],
        'fxt_label_founded'    => ['Label: Founded Year', 'Founded Year'],
        'fxt_label_website'    => ['Label: Website', 'Website'],
    ];

    foreach ($broker_fields as $id => [$label, $default]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'fxt_broker_labels', 'type' => 'text']);
    }

    // ╔═══════════════════════════════════════════════╗
    // ║  4. UI LABELS (General)                       ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_ui_labels', [
        'title'    => '🔤 Labels General',
        'priority' => 28,
    ]);

    $ui_fields = [
        'fxt_label_search_placeholder' => ['Placeholder Search', 'Search articles, brokers...'],
        'fxt_label_search_btn'         => ['Search Button', 'Search'],
        'fxt_label_reading_time'       => ['Reading time (use {min} for minute)', '{min} min read'],
        'fxt_label_ago'                => ['Text "ago" (time ago)', 'ago'],
        'fxt_label_toc'                => ['Title Table of Contents', '📑 Table of Contents'],
        'fxt_label_share'              => ['Text Share', 'Share:'],
        'fxt_label_related'            => ['Title Related Articles', 'Related Articles'],
        'fxt_label_prev'               => ['Pagination: Previous', '← Previous'],
        'fxt_label_next'               => ['Pagination: Next', 'Next →'],
        'fxt_label_notfound'           => ['Not Found - Title', 'No Content Found'],
        'fxt_label_notfound_search'    => ['No Results Found for "{query}"', 'No results found for "{query}". Please try different keywords.'],
        'fxt_label_latest_posts'       => ['Title blog', 'Latest Articles'],
        'fxt_label_back_home'          => ['Back to Home (404)', 'Back to Home'],
        'fxt_label_404_title'          => ['404 - Title', 'Page Not Found'],
        'fxt_label_404_desc'           => ['404 - Description', 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.'],
    ];

    foreach ($ui_fields as $id => [$label, $default]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'fxt_ui_labels', 'type' => 'text']);
    }

    // ╔═══════════════════════════════════════════════╗
    // ║  5. SIDEBAR                                   ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_sidebar_labels', [
        'title'    => '📌 Labels Sidebar',
        'priority' => 29,
    ]);

    $sidebar_fields = [
        'fxt_sidebar_search'   => ['Widget Search', '🔍 Search'],
        'fxt_sidebar_brokers'  => ['Widget Top Broker', '🏆 Top Broker'],
        'fxt_sidebar_popular'  => ['Widget Bài phổ biến', '📈 Popular Articles'],
    ];

    foreach ($sidebar_fields as $id => [$label, $default]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'fxt_sidebar_labels', 'type' => 'text']);
    }

    // ╔═══════════════════════════════════════════════╗
    // ║  6. AFFILIATE                                 ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_affiliate', ['title' => '💰 Affiliate Setup', 'priority' => 30]);

    $wp_customize->add_setting('fxt_default_affiliate_link', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('fxt_default_affiliate_link', ['label' => 'Link Affiliate mặc định', 'section' => 'fxt_affiliate', 'type' => 'url']);

    $wp_customize->add_setting('fxt_cta_text', ['default' => 'Open Account', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('fxt_cta_text', ['label' => 'Text CTA Button (Header)', 'section' => 'fxt_affiliate', 'type' => 'text']);

    // ╔═══════════════════════════════════════════════╗
    // ║  7. SOCIAL MEDIA                              ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_social', ['title' => '🌐 Social Media', 'priority' => 35]);

    foreach (['facebook' => 'Facebook URL', 'telegram' => 'Telegram URL', 'youtube' => 'YouTube URL', 'tiktok' => 'TikTok URL'] as $key => $label) {
        $wp_customize->add_setting("fxt_social_{$key}", ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
        $wp_customize->add_control("fxt_social_{$key}", ['label' => $label, 'section' => 'fxt_social', 'type' => 'url']);
    }

    // ╔═══════════════════════════════════════════════╗
    // ║  8. FOOTER                                    ║
    // ╚═══════════════════════════════════════════════╝
    $wp_customize->add_section('fxt_footer', ['title' => '📋 Footer', 'priority' => 40]);

    $wp_customize->add_setting('fxt_footer_about', ['default' => 'Providing trusted Forex education, broker reviews, and trading strategies for investors.', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('fxt_footer_about', ['label' => 'Short Description', 'section' => 'fxt_footer', 'type' => 'textarea']);

    $wp_customize->add_setting('fxt_footer_col3_title', ['default' => 'More information', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('fxt_footer_col3_title', ['label' => 'Title column 3', 'section' => 'fxt_footer', 'type' => 'text']);

    $wp_customize->add_setting('fxt_disclaimer', ['default' => '⚠️ Forex/CFD trading involves high risk. You may lose all of your invested capital.', 'sanitize_callback' => 'wp_kses_post']);
    $wp_customize->add_control('fxt_disclaimer', ['label' => 'Risk Disclaimer', 'section' => 'fxt_footer', 'type' => 'textarea']);

    $wp_customize->add_setting('fxt_copyright', ['default' => '© ' . date('Y') . ' FX Trading Today. All rights reserved.', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('fxt_copyright', ['label' => 'Copyright', 'section' => 'fxt_footer', 'type' => 'text']);
});

/**
 * Helper: Lấy customizer value nhanh
 * Dùng: fxt_text('fxt_hero_title')
 */
function fxt_text($key, $fallback = '') {
    return get_theme_mod($key, $fallback);
}

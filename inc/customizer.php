<?php
/**
 * Theme Customizer - Tùy chỉnh theme trong WP Admin
 * 
 * Vào Appearance > Customize để chỉnh sửa
 * Giống config panel trong admin dashboard
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

add_action('customize_register', function ($wp_customize) {

    // ═══════════════════════════════════════════
    // Section: Thông tin Affiliate
    // ═══════════════════════════════════════════
    $wp_customize->add_section('fxt_affiliate', [
        'title'    => 'Cài đặt Affiliate',
        'priority' => 30,
    ]);

    // Default affiliate link (dùng khi không có link riêng cho broker)
    $wp_customize->add_setting('fxt_default_affiliate_link', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('fxt_default_affiliate_link', [
        'label'   => 'Link Affiliate mặc định',
        'section' => 'fxt_affiliate',
        'type'    => 'url',
    ]);

    // CTA button text
    $wp_customize->add_setting('fxt_cta_text', [
        'default'           => 'Mở tài khoản ngay',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('fxt_cta_text', [
        'label'   => 'Text nút CTA',
        'section' => 'fxt_affiliate',
        'type'    => 'text',
    ]);

    // ═══════════════════════════════════════════
    // Section: Social Media
    // ═══════════════════════════════════════════
    $wp_customize->add_section('fxt_social', [
        'title'    => 'Mạng xã hội',
        'priority' => 35,
    ]);

    $socials = [
        'facebook'  => 'Facebook URL',
        'telegram'  => 'Telegram URL',
        'youtube'   => 'YouTube URL',
        'tiktok'    => 'TikTok URL',
    ];

    foreach ($socials as $key => $label) {
        $wp_customize->add_setting("fxt_social_{$key}", [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ]);
        $wp_customize->add_control("fxt_social_{$key}", [
            'label'   => $label,
            'section' => 'fxt_social',
            'type'    => 'url',
        ]);
    }

    // ═══════════════════════════════════════════
    // Section: Footer
    // ═══════════════════════════════════════════
    $wp_customize->add_section('fxt_footer', [
        'title'    => 'Cài đặt Footer',
        'priority' => 40,
    ]);

    // Disclaimer text
    $wp_customize->add_setting('fxt_disclaimer', [
        'default'           => 'Giao dịch Forex/CFD có rủi ro cao. Bạn có thể mất toàn bộ vốn đầu tư. Hãy chỉ giao dịch với số tiền bạn có thể chấp nhận mất.',
        'sanitize_callback' => 'wp_kses_post',
    ]);
    $wp_customize->add_control('fxt_disclaimer', [
        'label'   => 'Cảnh báo rủi ro (Footer)',
        'section' => 'fxt_footer',
        'type'    => 'textarea',
    ]);

    // Copyright text
    $wp_customize->add_setting('fxt_copyright', [
        'default'           => '© ' . date('Y') . ' FX Trading Today. All rights reserved.',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('fxt_copyright', [
        'label'   => 'Copyright text',
        'section' => 'fxt_footer',
        'type'    => 'text',
    ]);
});

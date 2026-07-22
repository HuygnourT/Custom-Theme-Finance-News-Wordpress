<?php
if (!defined('ABSPATH')) exit;

/**
 * ═══════════════════════════════════════════════════════════════
 *  SITE DATA OVERRIDE — nguồn dữ liệu thật, độc lập với Customizer
 * ═══════════════════════════════════════════════════════════════
 *
 * VÌ SAO CÓ FILE NÀY:
 * Customizer đôi khi bấm Publish/Save nhưng không lưu được, hoặc mất
 * dữ liệu vừa nhập. File này giải quyết vấn đề đó: dữ liệu bạn khai
 * báo TẠI ĐÂY luôn được áp dụng — bất kể database của Customizer có
 * lưu đúng hay không, và bất kể sau này bạn cập nhật theme bao nhiêu
 * lần (miễn là giữ nguyên file này, không ghi đè nó).
 *
 * CÁCH DÙNG:
 * - Bỏ dấu "//" ở đầu dòng field bạn muốn khóa cứng theo giá trị của
 *   bạn, sửa lại nội dung. Field đó sẽ LUÔN dùng giá trị ở đây, không
 *   phụ thuộc Customizer nữa.
 * - Field nào vẫn để nguyên dấu "//" (comment) → KHÔNG bị ảnh hưởng,
 *   hoạt động bình thường qua Customizer/DB như cũ.
 * - Danh sách dưới đây liệt kê ĐẦY ĐỦ toàn bộ field hiện có trong
 *   theme (tính đến thời điểm file này được tạo), kèm giá trị mặc
 *   định hiện tại trong code — để bạn tiện đối chiếu/sao chép.
 *
 * LƯU Ý:
 * - Field nào bạn khai báo ở đây thì Customizer sẽ KHÔNG còn tác dụng
 *   với field đó nữa (nhập gì trong Customizer cũng bị ghi đè lại).
 * - Riêng "Site Logo" (custom_logo) là ảnh (lưu attachment ID), KHÔNG
 *   đưa vào cơ chế override text này — logo vẫn luôn chỉnh qua
 *   Customize → Site Identity như bình thường.
 */

function fxt_site_data_overrides() {
    return [

        // ╔═══════════════════════════════════════════════╗
        // ║  1. HERO (Trang chủ)                          ║
        // ╚═══════════════════════════════════════════════╝
        'fxt_hero_badge'      => 'Latest Forex Broker Reviews ' . date('Y'),
        'fxt_hero_title'      => '{accent}Trusted{/accent} Forex Broker Reviews for Investors',
        'fxt_hero_desc'       => 'Detailed comparison of top Forex brokers. Objective reviews of spreads, leverage, regulation, and real trading experience',
        'fxt_hero_btn1'       => 'View Broker Reviews|https://fxtradingtoday.com/broker-reviews/',   // Format: Text|URL
        'fxt_hero_btn2'       => 'Best Broker Review|https://fxtradingtoday.com/broker-reviews/exness-review/',                  // Format: Text|URL
        'fxt_hero_stats_text' => '',

        // ╔═══════════════════════════════════════════════╗
        // ║  2. HOME · BROKERS (section top brokers)      ║
        // ╚═══════════════════════════════════════════════╝
        'fxt_section_brokers'         => '🏆 Top Recommended Brokers',
        'fxt_section_viewall'         => 'View All →',
        'fxt_home_brokers_text_top'   => '',   // Text phía TRÊN danh sách broker
        'fxt_home_brokers_text_bottom'=> '',   // Text phía DƯỚI danh sách broker
        'fxt_home_brokers_note'       => '',   // Khung Note (cho phép HTML cơ bản)

        // // ╔═══════════════════════════════════════════════╗
        // // ║  3. LABELS - BROKER PAGES                     ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_broker_review_prefix' => 'Review',
        'fxt_broker_open_account'  => 'Open Account',
        'fxt_broker_read_review'   => 'Read Review',
        'fxt_broker_overview'      => 'Overview',
        'fxt_broker_pros_title'    => '✅ Pros',
        'fxt_broker_cons_title'    => '❌ Cons',
        'fxt_broker_cta_ready'     => 'Are you ready to trade with {name}?',   // {name} = tên broker
        'fxt_broker_cta_desc'      => 'Open an account in just a few minutes and start trading today.',
        'fxt_broker_cta_btn'       => 'Get Started →',
        'fxt_label_spread'         => 'Spread',
        'fxt_label_leverage'       => 'Leverage',
        'fxt_label_deposit'        => 'Minimum Deposit',
        'fxt_label_regulation'     => 'Regulation',
        'fxt_label_platforms'      => 'Platform',
        'fxt_label_founded'        => 'Founded Year',
        'fxt_label_website'        => 'Website',
        'fxt_broker_section_show'  => '▼ Show details',
        'fxt_broker_section_hide'  => '▲ Hide details',

        // // ╔═══════════════════════════════════════════════╗
        // // ║  3b. BROKER COMPARISON PAGE                   ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_compare_title'              => 'Best Forex Brokers Comparison {year}',   // {year} = năm hiện tại
        'fxt_compare_desc'               => 'Detailed comparison of spreads, leverage, regulation, and features of the top forex brokers.',
        'fxt_compare_search_placeholder' => 'Search brokers...',
        'fxt_compare_sort_rating'        => 'Sort: Highest Rating',
        'fxt_compare_sort_spread'        => 'Sort: Lowest Spread',
        'fxt_compare_sort_deposit'       => 'Sort: Lowest Minimum Deposit',
        'fxt_compare_no_brokers'         => 'No brokers have been added yet.',

        // // ╔═══════════════════════════════════════════════╗
        // // ║  3c. BROKER PERMALINKS                        ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_broker_slug'      => 'broker-reviews',
        'fxt_broker_type_slug' => 'broker-type',

        // // ╔═══════════════════════════════════════════════╗
        // // ║  4. LABELS - GENERAL (UI)                     ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_label_search_placeholder'   => 'Search articles, brokers...',
        'fxt_label_search_btn'           => 'Search',
        'fxt_label_search_results_title' => 'Search results: "{query}"',
        'fxt_label_search_count'         => 'Found {count} results',
        'fxt_label_reading_time'         => '{min} min read',
        'fxt_label_ago'                  => 'ago',
        'fxt_label_toc'                  => '📑 Table of Contents',
        'fxt_label_share'                => 'Share:',
        'fxt_label_tags'                 => 'Tags:',
        'fxt_label_related'              => 'Related Articles',
        'fxt_label_prev'                 => '← Previous',
        'fxt_label_next'                 => 'Next →',
        'fxt_label_notfound'             => 'No Content Found',
        'fxt_label_notfound_search'      => 'No results found for "{query}". Please try different keywords.',
        'fxt_label_latest_posts'         => 'Latest Articles',
        'fxt_label_back_home'            => 'Back to Home',
        'fxt_label_404_title'            => 'Page Not Found',
        'fxt_label_404_desc'             => 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.',

        // // ╔═══════════════════════════════════════════════╗
        // // ║  4b. BREADCRUMBS                              ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_breadcrumb_home'           => 'Home',
        'fxt_breadcrumb_broker_archive' => 'Broker Reviews',
        'fxt_breadcrumb_search_prefix'  => 'Search: ',

        // // ╔═══════════════════════════════════════════════╗
        // // ║  5. SIDEBAR                                   ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_sidebar_search'  => '🔍 Search',
        'fxt_sidebar_brokers' => '🏆 Top Broker',
        'fxt_sidebar_popular' => '📈 Popular Articles',

        // // ╔═══════════════════════════════════════════════╗
        // // ║  6. AFFILIATE                                 ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_default_affiliate_link' => '',   // URL affiliate mặc định khi broker chưa điền
        'fxt_cta_text'               => 'Open Account',

        // // ╔═══════════════════════════════════════════════╗
        // // ║  7. SOCIAL MEDIA                              ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_social_facebook' => '',
        'fxt_social_telegram' => '',
        'fxt_social_youtube'  => '',
        'fxt_social_tiktok'   => '',
        'fxt_social_linkedin' => '',

        // // ╔═══════════════════════════════════════════════╗
        // // ║  8. FOOTER                                    ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_footer_about'        => 'FX Trading Today provides independent broker reviews and trading guides for retail traders across Asia. Our research is supported by affiliate partnerships with selected brokers, but no broker can pay for a higher ranking, favorable review, or editorial coverage.',
        'fxt_footer_col2_content' => "# Brokers Review|https://fxtradingtoday.com/broker-reviews/\n> Exness|https://fxtradingtoday.com/broker-reviews/exness-review/\n> IC Markets|https://fxtradingtoday.com/broker-reviews/ic-markets/\n> Vantage|https://fxtradingtoday.com/broker-reviews/vantage-review/",
        'fxt_footer_col3_content' => "## Information|\n> About us|https://fxtradingtoday.com/about-us/\n> Our Standard|https://fxtradingtoday.com/about-us/\n> Disclaimer|https://fxtradingtoday.com/disclaimer/\n> Privacy Policy|https://fxtradingtoday.com/privacy-policy/",
        'fxt_disclaimer'          => '⚠️ Forex/CFD trading involves high risk. You may lose all of your invested capital.',
        'fxt_copyright'           => '© ' . date('Y') . ' FX Trading Today. All rights reserved.',

        // // ╔═══════════════════════════════════════════════╗
        // // ║  9. CATEGORY BAR (Mega Menu)                  ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_catbar_enable'    => '1',      // '1' = bật, '' = tắt
        'fxt_catbar_style'     => 'light',  // light | dark | primary
        'fxt_catbar_menu_text' => "# Home|https://fxtradingtoday.com\n# Brokers Review|https://fxtradingtoday.com/broker-reviews/\n> Exness|https://fxtradingtoday.com/broker-reviews/exness-review/\n> IC Markets|https://fxtradingtoday.com/broker-reviews/ic-markets/\n> Vantage|https://fxtradingtoday.com/broker-reviews/vantage-review/\n# About us|https://fxtradingtoday.com/about-us/\n> Our Standard|https://fxtradingtoday.com/fx-trading-today-standard/",

        // // ╔═══════════════════════════════════════════════╗
        // // ║  10. HOME · HOW WE CAN HELP                   ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_home_help_hide'      => '',   // '1' = ẩn cả section
        'fxt_home_help_title'     => 'How We Can Help You',
        'fxt_home_help_intro'     => 'Whether you are a beginner or an experienced trader, our resources guide you to the right broker and smarter decisions.',
        'fxt_home_help_box1_icon'  => '🔍',
        'fxt_home_help_box1_title' => 'I’m New to Trading — Where Should I Start?',
        'fxt_home_help_box1_text'  => 'Start with a clear beginner roadmap that explains what online trading is, how brokers operate, how to choose between forex, CFDs, stocks, and other products, and how to place your first trade safely without taking unnecessary risk.',
        'fxt_home_help_box2_icon'  => '🎯',
        'fxt_home_help_box2_title' => 'Find the Right Broker for Your Trading Needs',
        'fxt_home_help_box2_text'  => 'Compare brokers based on the factors that matter most, including regulation, trading costs, minimum deposits, payment methods, and platform features. Whether you\'re a beginner or an experienced trader, our reviews help you identify the broker that best matches your goals and trading style.',
        'fxt_home_help_box3_icon'  => '🏆',
        'fxt_home_help_box3_title' => 'Explore the Best Brokers by Category',
        'fxt_home_help_box3_text'  => 'Discover top-rated brokers across the categories that matter most to Asian traders. Compare the best brokers for forex trading, CFDs, crypto, low minimum deposits, beginner-friendly platforms, Islamic accounts, local payment methods, and more. ',
        'fxt_home_help_cta_text'  => 'Explore our beginner guide →',
        'fxt_home_help_cta_url'   => '',   // Để trống = ẩn nút

        // // ╔═══════════════════════════════════════════════╗
        // // ║  11. HOME · GUIDE BY TOPIC                    ║
        // // ║  (mỗi ô i = 1 broker, i chạy từ 1 đến 6)      ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_home_guide_hide'  => '',   // '1' = ẩn cả section
        'fxt_home_guide_title' => 'Guide By Topic',
        'fxt_home_guide_intro' => '',
        //
        'fxt_home_guide_item1_broker'    => '3321',   // ID bài viết Broker (số)
        'fxt_home_guide_item1_links'     => "How to Open Exness Account: Step-by-Step Guide for Beginners | https://fxtradingtoday.com/broker-reviews/exness-review/exness-account-opening/\nExness Login : How to Access Your Exness Account | https://fxtradingtoday.com/broker-reviews/exness-review/exness-sign-in/\nHow to Download MT4 Exness on Windows, macOS, Android, and iOS | https://fxtradingtoday.com/broker-reviews/exness-review/exness-mt4/		\nHow to Add Exness Account to MT4 | https://fxtradingtoday.com/broker-reviews/exness-review/exness-connect-mt4/\nHow to Download MT5 Exness on Windows, MacOS, Android, and iOS | https://fxtradingtoday.com/broker-reviews/exness-review/exness-mt5/\nHow to Add Exness Account to MT5 | https://fxtradingtoday.com/broker-reviews/exness-review/exness-connect-mt5/\nExness Minimum Deposit | https://fxtradingtoday.com/broker-reviews/exness-review/minimum-deposit/\nHow to Reset Exness Password | https://fxtradingtoday.com/broker-reviews/exness-review/exness-reset-password/\nExness Account Types Explained: Standard, Pro, Raw Spread & Zero | https://fxtradingtoday.com/broker-reviews/exness-review/exness-account-types-explained-standard-pro-raw-spread-zero/\nWhat Is the Exness Personal Area? | https://fxtradingtoday.com/broker-reviews/exness-review/exness-personal-area/\nHow to Download Exness on Android | https://fxtradingtoday.com/broker-reviews/exness-review/exness-android/\nHow to Download Exness Trade App on iOS |  https://fxtradingtoday.com/broker-reviews/exness-review/exness-ios/\nExness Trading Platforms: MT4, MT5, Exness Terminal & Exness App Review | https://fxtradingtoday.com/broker-reviews/exness-review/exness-platform/\nIs Exness Supervised by a Regulator? | https://fxtradingtoday.com/broker-reviews/exness-review/is-exness-supervised-by-a-regulator/\nIs Exness Good for Beginners in 2026? | https://fxtradingtoday.com/broker-reviews/exness-review/is-exness-good-for-beginners/\nIs Exness Safe or a Scam?  | https://fxtradingtoday.com/broker-reviews/exness-review/is-exness-safe-or-a-scam/",   // Mỗi dòng: "Tiêu đề | https://link"
        'fxt_home_guide_item1_link_text' => '',   // Text nút CTA dưới cùng (tùy chọn)
        'fxt_home_guide_item1_link_url'  => '',   // URL nút CTA dưới cùng (tùy chọn)
        
        'fxt_home_guide_item2_broker'    => '3440',
        'fxt_home_guide_item2_links'     => 'IC Markets Deposit Bonus 100% Explained | https://fxtradingtoday.com/broker-reviews/ic-markets/ic-markets-deposit-bonus-explained/',
        'fxt_home_guide_item2_link_text' => '',
        'fxt_home_guide_item2_link_url'  => '',
        
        'fxt_home_guide_item3_broker'    => '3407',
        'fxt_home_guide_item3_links'     => 'How to Open Vantage Account | https://fxtradingtoday.com/broker-reviews/vantage-review/vantage-account-opening/\nIs Vantage Good for Beginners in 2026? | https://fxtradingtoday.com/broker-reviews/vantage-review/is-vantage-good-for-beginners/\nHow to Withdraw Money from Vantage in 2026 | https://fxtradingtoday.com/broker-reviews/vantage-review/vantage-withdraw/\nVantage Minimum Deposit | https://fxtradingtoday.com/broker-reviews/vantage-review/vantage-minimum-deposit/',
        'fxt_home_guide_item3_link_text' => '',
        'fxt_home_guide_item3_link_url'  => '',
        
        'fxt_home_guide_item4_broker'    => '',
        'fxt_home_guide_item4_links'     => '',
        'fxt_home_guide_item4_link_text' => '',
        'fxt_home_guide_item4_link_url'  => '',
        
        'fxt_home_guide_item5_broker'    => '',
        'fxt_home_guide_item5_links'     => '',
        'fxt_home_guide_item5_link_text' => '',
        'fxt_home_guide_item5_link_url'  => '',
        
        'fxt_home_guide_item6_broker'    => '',
        'fxt_home_guide_item6_links'     => '',
        'fxt_home_guide_item6_link_text' => '',
        'fxt_home_guide_item6_link_url'  => '',

        // // ╔═══════════════════════════════════════════════╗
        // // ║  12. HOME · EVERYTHING YOU NEED TO KNOW       ║
        // // ║  (mỗi khối i chạy từ 1 đến 4)                 ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_home_eyntk_hide'  => '',   // '1' = ẩn cả section
        'fxt_home_eyntk_title' => 'Everything You Need to Know',
        'fxt_home_eyntk_intro' => '',
        
        'fxt_home_eyntk_b1_title' => '',   // Để trống = ẩn khối
        'fxt_home_eyntk_b1_text'  => '',
        'fxt_home_eyntk_b2_title' => '',
        'fxt_home_eyntk_b2_text'  => '',
        'fxt_home_eyntk_b3_title' => '',
        'fxt_home_eyntk_b3_text'  => '',
        'fxt_home_eyntk_b4_title' => '',
        'fxt_home_eyntk_b4_text'  => '',

        // // ╔═══════════════════════════════════════════════╗
        // // ║  13. HOME · ABOUT US                          ║
        // // ╚═══════════════════════════════════════════════╝
        'fxt_home_about_hide'  => '',   // '1' = ẩn cả section
        'fxt_home_about_title' => 'About Us',
        'fxt_home_about_text'  => 'FX Trading Today provides independent broker reviews and trading guides for retail traders across Asia. Our research is supported by affiliate partnerships with selected brokers, but no broker can pay for a higher ranking, favorable review, or editorial coverage.',
        'fxt_home_about_accordion_text' => "#Why Traders Trust Us\n>We review every broker firsthand — real accounts, real spreads, real withdrawal tests — before publishing any rating.\n* See Our Methodology|/fx-trading-today-standard/\n#How We Choose Brokers\n>Regulation, trading costs, platform reliability, and customer support are scored using the same checklist for every broker.\n* View Broker Reviews|/broker-reviews/",
        'fxt_home_about_disclaimer' => '⚠️ Forex and CFD trading carry a high level of risk and may not be suitable for all investors. You could lose your entire investment',

    ];
}

/**
 * Chặn ngay tại gốc: option "theme_mods_{theme}" — nơi WordPress lưu
 * TOÀN BỘ giá trị Customizer. Hook này chạy TRƯỚC mọi get_theme_mod()
 * trong theme, nên không cần sửa bất kỳ file template nào khác.
 */
add_filter('option_theme_mods_' . get_stylesheet(), function ($value) {
    $overrides = fxt_site_data_overrides();
    if (!is_array($value)) $value = [];

    foreach ($overrides as $key => $override_value) {
        if ($override_value !== null && $override_value !== '') {
            $value[$key] = $override_value;
        }
    }

    return $value;
}, 20);

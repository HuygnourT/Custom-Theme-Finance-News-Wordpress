<?php
/**
 * Demo Import - Tự động tạo nội dung mẫu khi activate theme
 * 
 * Tạo: 3 Broker, 6 bài viết, pages cần thiết, menu
 * Chạy 1 lần duy nhất khi activate theme
 * 
 * @package FXTradingToday
 */
if (!defined('ABSPATH')) exit;

// === KHÔNG tự chạy khi activate — chỉ import thủ công qua admin ===
// (Tránh ghi đè bài viết cũ và settings)

// === Thêm nút import trong admin ===
add_action('admin_menu', function () {
    add_theme_page(
        'Import Demo Content',
        '⚡ Import Demo',
        'manage_options',
        'fxt-demo-import',
        'fxt_demo_import_page'
    );
});

function fxt_demo_import_page() {
    // Đếm bài viết hiện có
    $existing_posts = wp_count_posts();
    $existing_brokers = wp_count_posts('broker');
    $has_content = ($existing_posts->publish > 0);

    if (isset($_POST['fxt_run_import']) && check_admin_referer('fxt_demo_import')) {
        $skip_settings = isset($_POST['fxt_skip_settings']);
        fxt_import_demo_content($skip_settings);
        echo '<div class="notice notice-success"><p>✅ Demo content đã được import thành công!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>FX Trading Today - Import Demo Content</h1>

        <?php if ($has_content): ?>
        <div class="notice notice-warning" style="padding:12px">
            <p><strong>⚠️ Website đã có <?php echo $existing_posts->publish; ?> bài viết.</strong></p>
            <p>Bài viết cũ sẽ <strong>KHÔNG bị xóa</strong>. Demo chỉ thêm nội dung mẫu mới (brokers, categories, menu).</p>
        </div>
        <?php endif; ?>

        <p>Import sẽ tạo: 3 Broker mẫu, 6 bài viết, categories, pages, và menu. Không tạo trùng lặp.</p>

        <form method="post">
            <?php wp_nonce_field('fxt_demo_import'); ?>

            <p>
                <label>
                    <input type="checkbox" name="fxt_skip_settings" value="1" <?php echo $has_content ? 'checked' : ''; ?>>
                    <strong>Giữ nguyên Settings hiện tại</strong> (không đổi trang chủ, Reading settings)
                </label>
            </p>

            <p><button type="submit" name="fxt_run_import" class="button button-primary button-hero">🚀 Import Demo Content</button></p>
        </form>

        <hr>
        <h3>Hướng dẫn sau khi import:</h3>
        <ol>
            <li>Vào <strong>Settings → Permalinks</strong> → chọn "Post name" → Save</li>
            <li>Vào <strong>Appearance → Menus</strong> → kiểm tra menu đã gán đúng vị trí</li>
            <li>Vào <strong>Settings → Reading</strong> → chọn trang chủ và trang blog</li>
            <li>Vào <strong>Appearance → Customize</strong> → upload logo, điền affiliate link</li>
        </ol>
    </div>
    <?php
}

function fxt_import_demo_content($skip_settings = false) {
    // Kiểm tra đã import chưa
    if (get_option('fxt_demo_imported')) return;

    // === 1. Tạo Categories ===
    $cats = [
        'kien-thuc-forex'  => 'Kiến thức Forex',
        'chien-luoc'       => 'Chiến lược Trading',
        'tin-tuc'          => 'Tin tức',
        'huong-dan'        => 'Hướng dẫn',
    ];
    $cat_ids = [];
    foreach ($cats as $slug => $name) {
        $term = term_exists($slug, 'category');
        if (!$term) {
            $term = wp_insert_term($name, 'category', ['slug' => $slug]);
        }
        $cat_ids[$slug] = is_array($term) ? $term['term_id'] : $term;
    }

    // === 2. Tạo Brokers ===
    $brokers = [
        [
            'title' => 'Exness',
            'content' => '<h2>Exness có an toàn không?</h2>
<p>Exness là một trong những sàn Forex có uy tín cao nhất thế giới, được quản lý bởi nhiều cơ quan tài chính hàng đầu bao gồm FCA (Anh), CySEC (Châu Âu), và FSCA (Nam Phi).</p>
<h2>Spread và phí giao dịch</h2>
<p>Với tài khoản Raw Spread, bạn có thể giao dịch EUR/USD với spread từ 0.0 pips và commission chỉ $3.5/lot mỗi chiều. Đây là mức cạnh tranh nhất thị trường hiện nay.</p>
<h2>Đòn bẩy và quản lý rủi ro</h2>
<p>Exness cung cấp đòn bẩy lên đến 1:2000 cho các tài khoản Standard. Tuy nhiên, đòn bẩy cao đi kèm với rủi ro lớn.</p>',
            'excerpt' => 'Exness là sàn Forex được thành lập năm 2008, nổi tiếng với spread cực thấp và tốc độ rút tiền nhanh nhất thị trường.',
            'meta' => ['_fxt_rating' => '9.2', '_fxt_spread' => 'Từ 0.0 pips', '_fxt_leverage' => '1:2000', '_fxt_min_deposit' => '$1', '_fxt_regulation' => 'FCA, CySEC, FSCA', '_fxt_founded' => '2008', '_fxt_platforms' => 'MT4, MT5, Exness Terminal', '_fxt_pros' => "Spread cực thấp từ 0.0 pips\nRút tiền tức thì 24/7\nĐòn bẩy linh hoạt lên đến 1:2000\nHỗ trợ tiếng Việt 24/7\nNạp tối thiểu chỉ \$1", '_fxt_cons' => "Phí swap qua đêm tương đối cao\nKhông có bonus cho trader\nCông cụ phân tích tích hợp còn hạn chế"],
        ],
        [
            'title' => 'ICMarkets',
            'content' => '<h2>ICMarkets - Sàn dành cho trader chuyên nghiệp</h2>
<p>ICMarkets được thành lập tại Sydney, Australia năm 2007 và là một trong những sàn Forex lớn nhất thế giới tính theo khối lượng giao dịch.</p>
<h2>Raw Spread Account</h2>
<p>Tài khoản Raw Spread của ICMarkets có spread trung bình chỉ 0.1 pips cho EUR/USD, với commission $3.5/lot.</p>',
            'excerpt' => 'ICMarkets là sàn Forex hàng đầu Australia, nổi tiếng với spread raw cực thấp và tốc độ thực thi lệnh nhanh.',
            'meta' => ['_fxt_rating' => '8.8', '_fxt_spread' => 'Từ 0.0 pips', '_fxt_leverage' => '1:500', '_fxt_min_deposit' => '$200', '_fxt_regulation' => 'ASIC, CySEC, SCB', '_fxt_founded' => '2007', '_fxt_platforms' => 'MT4, MT5, cTrader', '_fxt_pros' => "Raw spread cực thấp\nTốc độ thực thi lệnh nhanh\nHỗ trợ cTrader\nKhối lượng giao dịch lớn", '_fxt_cons' => "Nạp tối thiểu $200\nGiao diện hỗ trợ tiếng Việt chưa hoàn thiện"],
        ],
        [
            'title' => 'XM Global',
            'content' => '<h2>XM - Sàn Forex phổ biến nhất cho người mới</h2>
<p>XM được thành lập năm 2009 và hiện có hơn 10 triệu khách hàng trên toàn thế giới. Sàn nổi tiếng với chương trình bonus hấp dẫn.</p>
<h2>Các loại tài khoản</h2>
<p>XM cung cấp nhiều loại tài khoản: Micro, Standard, XM Ultra Low và Shares Account.</p>',
            'excerpt' => 'XM Global là sàn Forex quốc tế phổ biến với bonus hấp dẫn, nạp tối thiểu thấp và hỗ trợ đa ngôn ngữ.',
            'meta' => ['_fxt_rating' => '8.5', '_fxt_spread' => 'Từ 0.6 pips', '_fxt_leverage' => '1:888', '_fxt_min_deposit' => '$5', '_fxt_regulation' => 'ASIC, CySEC, FCA', '_fxt_founded' => '2009', '_fxt_platforms' => 'MT4, MT5', '_fxt_pros' => "Nạp tối thiểu chỉ \$5\nChương trình bonus hấp dẫn\nHỗ trợ tiếng Việt tốt\nĐược quản lý bởi 3 cơ quan uy tín", '_fxt_cons' => "Spread không thấp bằng Exness, ICMarkets\nKhông hỗ trợ cTrader"],
        ],
    ];

    foreach ($brokers as $broker) {
        if (get_page_by_title($broker['title'], OBJECT, 'broker')) continue;
        $post_id = wp_insert_post([
            'post_title'   => $broker['title'],
            'post_content' => $broker['content'],
            'post_excerpt' => $broker['excerpt'],
            'post_type'    => 'broker',
            'post_status'  => 'publish',
        ]);
        if ($post_id && !is_wp_error($post_id)) {
            foreach ($broker['meta'] as $key => $val) {
                update_post_meta($post_id, $key, $val);
            }
        }
    }

    // === 3. Tạo bài viết mẫu ===
    $posts = [
        ['Forex là gì? Hướng dẫn toàn diện cho người mới bắt đầu', 'kien-thuc-forex', '<h2>Forex là gì?</h2><p>Forex (Foreign Exchange) là thị trường giao dịch ngoại hối lớn nhất thế giới, với khối lượng giao dịch trung bình hơn 6 nghìn tỷ USD mỗi ngày.</p><h2>Cách hoạt động của thị trường Forex</h2><p>Thị trường Forex hoạt động 24/5 thông qua mạng lưới ngân hàng, tổ chức tài chính và nhà đầu tư cá nhân trên toàn thế giới.</p><h2>Các cặp tiền tệ phổ biến</h2><p>Các cặp tiền tệ chính bao gồm EUR/USD, GBP/USD, USD/JPY, và USD/CHF.</p>'],
        ['5 chiến lược Scalping hiệu quả cho khung M5 và M15', 'chien-luoc', '<h2>Scalping là gì?</h2><p>Scalping là phương pháp giao dịch ngắn hạn, mở và đóng lệnh trong vài phút để kiếm lợi nhuận nhỏ nhưng thường xuyên.</p><h2>Chiến lược 1: EMA Cross</h2><p>Sử dụng giao cắt EMA 9 và EMA 21 trên khung M5 để xác định điểm vào lệnh.</p>'],
        ['Cách mở tài khoản Exness và xác minh trong 5 phút', 'huong-dan', '<h2>Bước 1: Đăng ký tài khoản</h2><p>Truy cập website Exness, nhấn "Đăng ký" và điền thông tin email, mật khẩu.</p><h2>Bước 2: Xác minh danh tính</h2><p>Upload ảnh CMND/CCCD hoặc hộ chiếu. Quá trình xác minh thường hoàn tất trong vài phút.</p>'],
        ['Cách đọc nến Nhật Bản: 20 mô hình nến quan trọng nhất', 'kien-thuc-forex', '<h2>Nến Nhật là gì?</h2><p>Nến Nhật (Japanese Candlestick) là phương pháp biểu diễn giá phổ biến nhất trong phân tích kỹ thuật.</p><h2>Mô hình nến đảo chiều tăng</h2><p>Hammer, Morning Star, Bullish Engulfing là 3 mô hình đảo chiều tăng phổ biến nhất.</p>'],
        ['Quản lý rủi ro: Quy tắc 1% và cách tính lot size', 'kien-thuc-forex', '<h2>Tại sao quản lý rủi ro quan trọng?</h2><p>Quản lý rủi ro là kỹ năng quan trọng nhất quyết định sự sống còn của trader. Không có chiến lược nào thắng 100% - quản lý vốn giúp bạn tồn tại lâu dài.</p><h2>Quy tắc 1%</h2><p>Không bao giờ rủi ro quá 1-2% tổng vốn cho một lệnh giao dịch.</p>'],
        ['So sánh Exness vs ICMarkets: Sàn nào tốt hơn?', 'kien-thuc-forex', '<h2>Tổng quan</h2><p>Exness và ICMarkets đều là sàn Forex hàng đầu với spread thấp. Bài viết này so sánh chi tiết để giúp bạn chọn sàn phù hợp.</p><h2>So sánh Spread</h2><p>Cả hai đều cung cấp spread từ 0.0 pips, nhưng commission và swap có sự khác biệt.</p>'],
    ];

    foreach ($posts as $post_data) {
        if (get_page_by_title($post_data[0], OBJECT, 'post')) continue;
        wp_insert_post([
            'post_title'    => $post_data[0],
            'post_content'  => $post_data[2],
            'post_type'     => 'post',
            'post_status'   => 'publish',
            'post_category' => [$cat_ids[$post_data[1]] ?? 1],
        ]);
    }

    // === 4. Tạo Pages ===
    $pages = [
        'Trang chủ'          => '',
        'Blog'               => '',
        'So sánh sàn Forex'  => '', // Sau khi tạo, set template = So sánh Broker
        'Về chúng tôi'       => '<p>FX Trading Today cung cấp đánh giá sàn Forex khách quan và kiến thức giao dịch cho nhà đầu tư Việt Nam.</p>',
        'Liên hệ'            => '<p>Email: contact@fxtradingtoday.com</p>',
        'Disclaimer'         => '<p>Nội dung trên website này chỉ mang tính chất tham khảo. Giao dịch Forex/CFD có rủi ro cao.</p>',
        'Chính sách bảo mật' => '<p>Chúng tôi cam kết bảo vệ thông tin cá nhân của bạn.</p>',
    ];

    $page_ids = [];
    foreach ($pages as $title => $content) {
        if (get_page_by_title($title, OBJECT, 'page')) {
            $existing = get_page_by_title($title, OBJECT, 'page');
            $page_ids[$title] = $existing->ID;
            continue;
        }
        $page_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $content,
            'post_type'    => 'page',
            'post_status'  => 'publish',
        ]);
        $page_ids[$title] = $page_id;
    }

    // Set page template cho So sánh
    if (isset($page_ids['So sánh sàn Forex'])) {
        update_post_meta($page_ids['So sánh sàn Forex'], '_wp_page_template', 'page-templates/template-brokers.php');
    }

    // === 5. Cấu hình Reading Settings (chỉ khi user cho phép) ===
    if (!$skip_settings) {
        if (isset($page_ids['Trang chủ'])) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $page_ids['Trang chủ']);
        }
        if (isset($page_ids['Blog'])) {
            update_option('page_for_posts', $page_ids['Blog']);
        }
    }

    // === 6. Tạo Menu ===
    $menu_name = 'Menu Chính';
    $menu_exists = wp_get_nav_menu_object($menu_name);

    if (!$menu_exists) {
        $menu_id = wp_create_nav_menu($menu_name);

        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => 'Trang chủ',
            'menu-item-url'    => home_url('/'),
            'menu-item-status' => 'publish',
            'menu-item-type'   => 'custom',
        ]);

        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'     => 'Đánh giá sàn',
            'menu-item-url'       => get_post_type_archive_link('broker') ?: home_url('/brokers/'),
            'menu-item-status'    => 'publish',
            'menu-item-type'      => 'custom',
        ]);

        foreach (['Kiến thức Forex' => 'kien-thuc-forex', 'Chiến lược' => 'chien-luoc', 'Hướng dẫn' => 'huong-dan', 'Tin tức' => 'tin-tuc'] as $label => $slug) {
            $cat = get_category_by_slug($slug);
            if ($cat) {
                wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-title'     => $label,
                    'menu-item-object-id' => $cat->term_id,
                    'menu-item-object'    => 'category',
                    'menu-item-type'      => 'taxonomy',
                    'menu-item-status'    => 'publish',
                ]);
            }
        }

        if (isset($page_ids['So sánh sàn Forex'])) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'     => 'So sánh sàn',
                'menu-item-object-id' => $page_ids['So sánh sàn Forex'],
                'menu-item-object'    => 'page',
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }

        // Gán menu vào vị trí
        $locations = get_theme_mod('nav_menu_locations');
        $locations['primary'] = $menu_id;
        $locations['mobile'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    // === 7. Cấu hình Permalink ===
    update_option('permalink_structure', '/%postname%/');
    flush_rewrite_rules();

    // === 8. Đánh dấu đã import ===
    update_option('fxt_demo_imported', true);
}

/**
 * Reset demo (dùng khi cần import lại)
 * Vào: WP Admin → Appearance → Import Demo → thêm ?reset=1 vào URL
 */
add_action('admin_init', function () {
    if (isset($_GET['page']) && $_GET['page'] === 'fxt-demo-import' && isset($_GET['reset'])) {
        delete_option('fxt_demo_imported');
        add_action('admin_notices', function () {
            echo '<div class="notice notice-warning"><p>Demo import flag đã được reset. Bạn có thể import lại.</p></div>';
        });
    }
});

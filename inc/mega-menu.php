<?php
/**
 * Mega Menu — Category Bar dưới header
 * 
 * Hiển thị các Category tabs với dropdown con
 * Quản lý qua WP Admin → Appearance → Customize → Category Bar
 * hoặc qua Menu location 'category_bar'
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

// ╔═══════════════════════════════════════════════════════════════╗
// ║  REGISTER MENU LOCATION                                       ║
// ╚═══════════════════════════════════════════════════════════════╝

add_action('after_setup_theme', function () {
    register_nav_menus([
        'category_bar' => 'Category Bar (dưới Header)',
    ]);
}, 20);

// ╔═══════════════════════════════════════════════════════════════╗
// ║  CUSTOMIZER SETTINGS                                          ║
// ╚═══════════════════════════════════════════════════════════════╝

add_action('customize_register', function ($wp_customize) {

    $wp_customize->add_section('fxt_category_bar', [
        'title'       => '📂 Category Bar (Header)',
        'description' => 'Cấu hình thanh Category hiển thị dưới header. Nếu đã tạo Menu "Category Bar", menu sẽ được ưu tiên. Nếu không, cấu trúc menu nhập bên dưới sẽ được dùng.',
        'priority'    => 24,
    ]);

    // Bật/tắt category bar
    $wp_customize->add_setting('fxt_catbar_enable', [
        'default'           => '1',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('fxt_catbar_enable', [
        'label'   => 'Bật Category Bar',
        'section' => 'fxt_category_bar',
        'type'    => 'checkbox',
    ]);

    // Style
    $wp_customize->add_setting('fxt_catbar_style', [
        'default'           => 'light',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('fxt_catbar_style', [
        'label'   => 'Style',
        'section' => 'fxt_category_bar',
        'type'    => 'select',
        'choices' => [
            'light' => 'Light (nền trắng)',
            'dark'  => 'Dark (nền tối)',
            'primary' => 'Primary (nền xanh)',
        ],
    ]);

    // Cấu trúc menu dạng text nhiều cấp
    $wp_customize->add_setting('fxt_catbar_menu_text', [
        'default'           => fxt_default_catbar_menu_text(),
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    $wp_customize->add_control('fxt_catbar_menu_text', [
        'label'       => 'Cấu trúc Menu (mỗi dòng 1 item)',
        'description' => '# = Menu cha (Level 1). > = Level 2, >> = Level 3, >>> = Level 4. Định dạng: Tiêu đề|URL. '
            . 'Ví dụ: "# Brokers|/brokers" rồi dòng dưới "> Exness|/brokers/exness".',
        'section'     => 'fxt_category_bar',
        'type'        => 'textarea',
    ]);
});

/**
 * Nội dung mặc định cho Customizer textarea (dùng làm ví dụ + fallback)
 */
function fxt_default_catbar_menu_text() {
    return "# Home|/\n"
        . "# Brokers|/brokers\n"
        . "> Exness|/brokers/exness\n"
        . "> IC Markets|/brokers/ic-markets\n"
        . "> Vantage|/brokers/vantage\n"
        . "# Trading Guides|/guides\n"
        . "> Forex Basics|/guides/forex-basics\n"
        . "> Technical Analysis|/guides/technical-analysis\n"
        . "> Risk Management|/guides/risk-management\n"
        . "# Reviews|/reviews\n"
        . "> Exness Review|/reviews/exness\n"
        . "> IC Markets Review|/reviews/ic-markets\n"
        . "> Vantage Review|/reviews/vantage\n"
        . "# About|/about";
}

// ╔═══════════════════════════════════════════════════════════════╗
// ║  RENDER FUNCTION                                              ║
// ╚═══════════════════════════════════════════════════════════════╝

/**
 * Render category bar
 * Gọi trong header.php: <?php fxt_category_bar(); ?>
 */
function fxt_category_bar() {
    if (!get_theme_mod('fxt_catbar_enable', '1')) return;

    $style = get_theme_mod('fxt_catbar_style', 'light');
    ?>
    <div class="catbar catbar-<?php echo esc_attr($style); ?>" id="category-bar">
        <div class="container">
            <nav class="catbar-nav" id="catbar-nav">
                <?php
                // Ưu tiên 1: Dùng WP Menu nếu đã tạo
                if (has_nav_menu('category_bar')):
                    wp_nav_menu([
                        'theme_location' => 'category_bar',
                        'container'      => false,
                        'menu_class'     => 'catbar-menu',
                        'depth'          => 2,
                        'fallback_cb'    => false,
                        'walker'         => new FXT_Category_Bar_Walker(),
                    ]);
                else:
                    // Ưu tiên 2: Dùng cấu trúc menu dạng text từ Customizer
                    fxt_render_catbar_from_text();
                endif;
                ?>
            </nav>
        </div>
    </div>
    <?php
}

/**
 * Parse cấu trúc menu dạng text (#, >, >>, >>>) thành cây (tree) lồng nhau.
 * Mỗi dòng: {#|>|>>|>>>} Title|URL
 */
function fxt_parse_catbar_menu_text($raw_text) {
    $lines = preg_split('/\r\n|\r|\n/', (string) $raw_text);
    $tree  = [];
    $stack = []; // $stack[$depth] = reference đến node ở cấp đó

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        if ($line[0] === '#') {
            $depth = 1;
            $rest  = ltrim($line, '# ');
        } elseif ($line[0] === '>') {
            preg_match('/^>+/', $line, $m);
            $depth = strlen($m[0]) + 1; // 1 dấu > = level 2, 2 dấu >> = level 3...
            $rest  = trim(substr($line, strlen($m[0])));
        } else {
            continue; // dòng không đúng định dạng, bỏ qua
        }

        $parts = array_map('trim', explode('|', $rest, 2));
        $title = $parts[0] ?? '';
        $url   = $parts[1] ?? '';
        if ($title === '') continue;
        if ($url === '') $url = '#';

        $node = ['title' => $title, 'url' => $url, 'children' => []];

        if ($depth <= 1) {
            $tree[] = $node;
            $stack  = [1 => &$tree[array_key_last($tree)]];
            continue;
        }

        // Tìm cấp cha gần nhất đang tồn tại (phòng trường hợp nhảy cóc cấp)
        $parent_depth = $depth - 1;
        while ($parent_depth > 1 && !isset($stack[$parent_depth])) {
            $parent_depth--;
        }

        if (!isset($stack[$parent_depth])) {
            // Không có cấp cha nào phù hợp → coi dòng này như Level 1
            $tree[] = $node;
            $stack  = [1 => &$tree[array_key_last($tree)]];
            continue;
        }

        $stack[$parent_depth]['children'][] = $node;
        $last_key = array_key_last($stack[$parent_depth]['children']);
        $stack[$parent_depth + 1] = &$stack[$parent_depth]['children'][$last_key];

        // Dọn các cấp sâu hơn cũ vì đang chuyển sang nhánh mới
        foreach (array_keys($stack) as $d) {
            if ($d > $parent_depth + 1) unset($stack[$d]);
        }
    }

    return $tree;
}

/**
 * Render cây menu (không giới hạn số cấp) ra HTML catbar
 */
function fxt_render_catbar_menu_tree($items, $depth = 1) {
    if (empty($items)) return;

    if ($depth === 1) {
        echo '<ul class="catbar-menu">';
    } elseif ($depth === 2) {
        echo '<div class="catbar-dropdown"><ul class="catbar-dropdown-list">';
    } else {
        echo '<ul class="catbar-dropdown-sub">';
    }

    foreach ($items as $item) {
        $has_children = !empty($item['children']);

        if ($depth === 1) {
            $li_class = 'catbar-item' . ($has_children ? ' catbar-has-children' : '');
            echo '<li class="' . esc_attr($li_class) . '">';
            echo '<a href="' . esc_url($item['url']) . '" class="catbar-link">';
            echo '<span>' . esc_html($item['title']) . '</span>';
            if ($has_children) echo '<span class="catbar-arrow">▾</span>';
            echo '</a>';
        } else {
            echo '<li' . ($has_children ? ' class="catbar-has-children"' : '') . '>';
            echo '<a href="' . esc_url($item['url']) . '" class="catbar-dropdown-link">';
            echo esc_html($item['title']);
            echo '</a>';
        }

        if ($has_children) {
            fxt_render_catbar_menu_tree($item['children'], $depth + 1);
        }

        echo '</li>';
    }

    if ($depth === 1) {
        echo '</ul>';
    } elseif ($depth === 2) {
        echo '</ul></div>';
    } else {
        echo '</ul>';
    }
}

/**
 * Lấy cấu trúc menu từ Customizer, parse và render
 */
function fxt_render_catbar_from_text() {
    $raw  = get_theme_mod('fxt_catbar_menu_text', fxt_default_catbar_menu_text());
    $tree = fxt_parse_catbar_menu_text($raw);
    fxt_render_catbar_menu_tree($tree, 1);
}

/**
 * Nội dung mặc định cho Footer Cột 2 (Customizer textarea)
 */
function fxt_default_footer_col2_text() {
    return "# Brokers Review|https://test.fxtradingtoday.com/broker-reviews/\n"
        . "> Exness|https://test.fxtradingtoday.com/broker-reviews/exness-review/\n"
        . "> IC Markets|https://test.fxtradingtoday.com/broker-reviews/ic-markets/\n"
        . "> Vantage|https://test.fxtradingtoday.com/broker-reviews/vantage-review/";
}

/**
 * Nội dung mặc định cho Footer Cột 3 (Customizer textarea)
 */
function fxt_default_footer_col3_text() {
    return "# Information|\n"
        . "> About us|https://test.fxtradingtoday.com/about-us/\n"
        . "> Our Standard|https://test.fxtradingtoday.com/about-us/\n"
        . "> Disclaimer|https://test.fxtradingtoday.com/disclaimer/\n"
        . "> Privacy Policy|https://test.fxtradingtoday.com/privacy-policy/";
}

/**
 * Gom toàn bộ con cháu (mọi cấp) thành 1 mảng phẳng — dùng cho Footer
 * vì Footer chỉ hiển thị list phẳng, không có dropdown như mega menu.
 */
function fxt_flatten_menu_children($children, &$out) {
    foreach ($children as $child) {
        $out[] = $child;
        if (!empty($child['children'])) {
            fxt_flatten_menu_children($child['children'], $out);
        }
    }
}

/**
 * Parse + render nội dung Footer (Tiêu đề # + danh sách link phẳng, không dropdown)
 * Hỗ trợ nhiều nhóm "#" trong cùng 1 field, mỗi nhóm là 1 heading + list riêng.
 */
function fxt_render_footer_menu_text($raw_text) {
    $tree = fxt_parse_catbar_menu_text($raw_text);
    if (empty($tree)) return;

    foreach ($tree as $group) {
        echo '<h4 class="footer-widget-title">';
        if (!empty($group['url']) && $group['url'] !== '#') {
            echo '<a href="' . esc_url($group['url']) . '">' . esc_html($group['title']) . '</a>';
        } else {
            echo esc_html($group['title']);
        }
        echo '</h4>';

        if (!empty($group['children'])) {
            $flat = [];
            fxt_flatten_menu_children($group['children'], $flat);
            echo '<ul class="footer-links">';
            foreach ($flat as $item) {
                echo '<li><a href="' . esc_url($item['url']) . '">' . esc_html($item['title']) . '</a></li>';
            }
            echo '</ul>';
        }
    }
}

// ╔═══════════════════════════════════════════════════════════════╗
// ║  CUSTOM WALKER cho WP Menu                                    ║
// ╚═══════════════════════════════════════════════════════════════╝

class FXT_Category_Bar_Walker extends Walker_Nav_Menu {

    function start_lvl(&$output, $depth = 0, $args = null) {
        if ($depth === 0) {
            $output .= '<div class="catbar-dropdown"><ul class="catbar-dropdown-list">';
        } else {
            $output .= '<ul class="catbar-dropdown-sub">';
        }
    }

    function end_lvl(&$output, $depth = 0, $args = null) {
        if ($depth === 0) {
            $output .= '</ul></div>';
        } else {
            $output .= '</ul>';
        }
    }

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $has_children = in_array('menu-item-has-children', $item->classes);
        $is_current = in_array('current-menu-item', $item->classes) || in_array('current-menu-ancestor', $item->classes);

        if ($depth === 0) {
            $classes = 'catbar-item';
            if ($has_children) $classes .= ' catbar-has-children';
            if ($is_current) $classes .= ' catbar-current';
            $output .= '<li class="' . $classes . '">';
            $output .= '<a href="' . esc_url($item->url) . '" class="catbar-link">';
            $output .= '<span>' . esc_html($item->title) . '</span>';
            if ($has_children) $output .= '<span class="catbar-arrow">▾</span>';
            $output .= '</a>';
        } else {
            $output .= '<li>';
            $output .= '<a href="' . esc_url($item->url) . '" class="catbar-dropdown-link">';
            $output .= esc_html($item->title);
            $output .= '</a>';
        }
    }

    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}

// ╔═══════════════════════════════════════════════════════════════╗
// ║  LOAD JS cho category bar (hover/click toggle)               ║
// ╚═══════════════════════════════════════════════════════════════╝

add_action('wp_footer', function () {
    if (!get_theme_mod('fxt_catbar_enable', '1')) return;
    ?>
    <script>
    (function(){
        var items = document.querySelectorAll('.catbar-has-children');
        var isMobile = window.innerWidth < 769;

        items.forEach(function(item) {
            if (isMobile) {
                // Mobile: click to toggle
                var link = item.querySelector('.catbar-link');
                link.addEventListener('click', function(e) {
                    var isOpen = item.classList.contains('catbar-open');
                    // Close all others
                    items.forEach(function(it) { it.classList.remove('catbar-open'); });
                    if (!isOpen) {
                        e.preventDefault();
                        item.classList.add('catbar-open');
                    }
                });
            }
            // Desktop: hover handled by CSS
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.catbar-has-children')) {
                items.forEach(function(it) { it.classList.remove('catbar-open'); });
            }
        });
    })();
    </script>
    <?php
});

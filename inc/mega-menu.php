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
        'description' => 'Cấu hình thanh Category hiển thị dưới header. Nếu đã tạo Menu "Category Bar", menu sẽ được ưu tiên. Nếu không, các categories được chọn bên dưới sẽ hiển thị.',
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

    // Hiển thị icon
    $wp_customize->add_setting('fxt_catbar_show_icons', [
        'default'           => '1',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('fxt_catbar_show_icons', [
        'label'   => 'Hiển thị icon emoji',
        'section' => 'fxt_category_bar',
        'type'    => 'checkbox',
    ]);

    // Custom items (khi không dùng Menu)
    // Mỗi slot: icon + label + type (category/custom URL) + target
    for ($i = 1; $i <= 8; $i++) {
        $wp_customize->add_setting("fxt_catbar_item_{$i}_label", [
            'default' => '', 'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control("fxt_catbar_item_{$i}_label", [
            'label'   => "Item {$i} - Label (để trống = ẩn)",
            'section' => 'fxt_category_bar',
            'type'    => 'text',
        ]);

        $wp_customize->add_setting("fxt_catbar_item_{$i}_icon", [
            'default' => '', 'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control("fxt_catbar_item_{$i}_icon", [
            'label'   => "Item {$i} - Icon (emoji, e.g. 📊)",
            'section' => 'fxt_category_bar',
            'type'    => 'text',
        ]);

        $wp_customize->add_setting("fxt_catbar_item_{$i}_url", [
            'default' => '', 'sanitize_callback' => 'esc_url_raw',
        ]);
        $wp_customize->add_control("fxt_catbar_item_{$i}_url", [
            'label'       => "Item {$i} - URL (để trống = dùng category slug)",
            'section'     => 'fxt_category_bar',
            'type'        => 'url',
        ]);

        $wp_customize->add_setting("fxt_catbar_item_{$i}_cat_slug", [
            'default' => '', 'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control("fxt_catbar_item_{$i}_cat_slug", [
            'label'       => "Item {$i} - Category slug (auto-dropdown con)",
            'description' => 'Nhập slug category → dropdown tự hiện các sub-categories',
            'section'     => 'fxt_category_bar',
            'type'        => 'text',
        ]);
    }
});

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
    $show_icons = get_theme_mod('fxt_catbar_show_icons', '1');
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
                    // Ưu tiên 2: Dùng Customizer items
                    fxt_render_catbar_from_customizer($show_icons);
                endif;
                ?>
            </nav>
        </div>
    </div>
    <?php
}

/**
 * Render từ Customizer settings
 */
function fxt_render_catbar_from_customizer($show_icons) {
    echo '<ul class="catbar-menu">';

    // Item đặc biệt: Broker Reviews (luôn hiện nếu có brokers)
    $broker_count = wp_count_posts('broker');
    if ($broker_count && $broker_count->publish > 0) {
        echo '<li class="catbar-item">';
        echo '<a href="' . get_post_type_archive_link('broker') . '" class="catbar-link">';
        if ($show_icons) echo '<span class="catbar-icon">📊</span>';
        echo '<span>Broker Reviews</span></a>';
        echo '</li>';
    }

    // Custom items từ Customizer
    for ($i = 1; $i <= 8; $i++) {
        $label = get_theme_mod("fxt_catbar_item_{$i}_label");
        if (!$label) continue;

        $icon = get_theme_mod("fxt_catbar_item_{$i}_icon");
        $url = get_theme_mod("fxt_catbar_item_{$i}_url");
        $cat_slug = get_theme_mod("fxt_catbar_item_{$i}_cat_slug");

        // Xác định URL
        $link = '#';
        $children = [];

        if ($cat_slug) {
            $cat = get_category_by_slug($cat_slug);
            if ($cat) {
                $link = get_category_link($cat->term_id);
                // Lấy sub-categories
                $children = get_categories([
                    'parent'     => $cat->term_id,
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'number'     => 10,
                ]);
            }
        } elseif ($url) {
            $link = $url;
        }

        $has_children = !empty($children);
        echo '<li class="catbar-item' . ($has_children ? ' catbar-has-children' : '') . '">';
        echo '<a href="' . esc_url($link) . '" class="catbar-link">';
        if ($show_icons && $icon) echo '<span class="catbar-icon">' . esc_html($icon) . '</span>';
        echo '<span>' . esc_html($label) . '</span>';
        if ($has_children) echo '<span class="catbar-arrow">▾</span>';
        echo '</a>';

        if ($has_children) {
            echo '<div class="catbar-dropdown"><ul class="catbar-dropdown-list">';
            // Link "Xem tất cả"
            echo '<li><a href="' . esc_url($link) . '" class="catbar-dropdown-link catbar-dropdown-all"><strong>Tất cả ' . esc_html($label) . '</strong></a></li>';
            foreach ($children as $child) {
                echo '<li><a href="' . esc_url(get_category_link($child->term_id)) . '" class="catbar-dropdown-link">';
                echo esc_html($child->name);
                if ($child->count > 0) echo ' <span class="catbar-count">(' . $child->count . ')</span>';
                echo '</a></li>';
            }
            echo '</ul></div>';
        }

        echo '</li>';
    }

    echo '</ul>';
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

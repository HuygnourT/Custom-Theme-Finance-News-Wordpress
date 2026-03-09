<?php
/**
 * SEO Helpers - Schema markup, Breadcrumbs, Open Graph
 * 
 * Tự viết SEO cơ bản thay vì dùng plugin nặng.
 * Nếu sau này cài Rank Math/Yoast, có thể tắt file này.
 * 
 * UPDATED: Thêm support cho broker_post (silo breadcrumbs + Article schema)
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

/**
 * Schema Markup: Organization + WebSite (trang chủ)
 */
add_action('wp_head', function () {
    if (!is_front_page()) return;

    $schema = [
        '@context' => 'https://schema.org',
        '@graph'   => [
            [
                '@type' => 'WebSite',
                'name'  => get_bloginfo('name'),
                'url'   => home_url('/'),
                'potentialAction' => [
                    '@type'       => 'SearchAction',
                    'target'      => home_url('/?s={search_term_string}'),
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            [
                '@type' => 'Organization',
                'name'  => get_bloginfo('name'),
                'url'   => home_url('/'),
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => get_theme_mod('custom_logo') ? wp_get_attachment_url(get_theme_mod('custom_logo')) : '',
                ],
            ],
        ],
    ];

    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
});

/**
 * Schema Markup: Article (bài viết đơn + broker_post)
 */
add_action('wp_head', function () {
    if (!is_singular('post') && !is_singular('broker_post')) return;

    global $post;

    $schema = [
        '@context'      => 'https://schema.org',
        '@type'         => 'Article',
        'headline'      => get_the_title(),
        'datePublished' => get_the_date('c'),
        'dateModified'  => get_the_modified_date('c'),
        'author'        => [
            '@type' => 'Person',
            'name'  => get_the_author(),
        ],
        'publisher'     => [
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
        ],
        'mainEntityOfPage' => get_permalink(),
    ];

    // Cho broker_post: thêm about (broker cha) để Google hiểu silo
    if (is_singular('broker_post')) {
        $parent = fxt_get_parent_broker($post->ID);
        if ($parent) {
            $schema['about'] = [
                '@type' => 'FinancialService',
                'name'  => $parent['title'],
                'url'   => $parent['permalink'],
            ];
        }
    }

    if (has_post_thumbnail()) {
        $img = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
        if ($img) {
            $schema['image'] = [
                '@type'  => 'ImageObject',
                'url'    => $img[0],
                'width'  => $img[1],
                'height' => $img[2],
            ];
        }
    }

    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
});

/**
 * Schema Markup: Review (cho broker post type)
 */
add_action('wp_head', function () {
    if (!is_singular('broker')) return;

    global $post;
    $meta = fxt_get_broker_meta($post->ID);

    if (empty($meta['rating'])) return;

    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Review',
        'itemReviewed' => [
            '@type' => 'FinancialService',
            'name'  => get_the_title(),
            'url'   => $meta['website_url'] ?: '',
        ],
        'reviewRating' => [
            '@type'       => 'Rating',
            'ratingValue' => $meta['rating'],
            'bestRating'  => '10',
            'worstRating' => '0',
        ],
        'author' => [
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
        ],
        'datePublished' => get_the_date('c'),
        'dateModified'  => get_the_modified_date('c'),
    ];

    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
});

/**
 * Breadcrumbs - Đường dẫn điều hướng
 * Hiển thị: Home > Category > Post
 * Gọi trong template: fxt_breadcrumbs();
 */
function fxt_breadcrumbs() {
    if (is_front_page()) return;

    $sep = '<span class="breadcrumb-sep">›</span>';
    $home_text = esc_html(get_theme_mod('fxt_breadcrumb_home', 'Home'));
    $broker_archive_text = esc_html(get_theme_mod('fxt_breadcrumb_broker_archive', 'Broker Reviews'));
    $search_prefix = esc_html(get_theme_mod('fxt_breadcrumb_search_prefix', 'Search: '));

    echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
    echo '<a href="' . home_url('/') . '">' . $home_text . '</a>';

    if (is_singular('post')) {
        $categories = get_the_category();
        if ($categories) {
            echo $sep . '<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '">' . esc_html($categories[0]->name) . '</a>';
        }
        echo $sep . '<span class="breadcrumb-current">' . get_the_title() . '</span>';

    } elseif (is_singular('broker')) {
        echo $sep . '<a href="' . get_post_type_archive_link('broker') . '">' . $broker_archive_text . '</a>';
        echo $sep . '<span class="breadcrumb-current">' . get_the_title() . '</span>';

    } elseif (is_singular('broker_post')) {
        // Silo breadcrumb: Home > Broker Reviews > Exness > Bài phụ
        // Handled by fxt_broker_post_breadcrumbs() — fallback here
        $parent = fxt_get_parent_broker(get_the_ID());
        echo $sep . '<a href="' . get_post_type_archive_link('broker') . '">' . $broker_archive_text . '</a>';
        if ($parent) {
            echo $sep . '<a href="' . esc_url($parent['permalink']) . '">' . esc_html($parent['title']) . '</a>';
        }
        echo $sep . '<span class="breadcrumb-current">' . get_the_title() . '</span>';

    } elseif (is_category()) {
        echo $sep . '<span class="breadcrumb-current">' . single_cat_title('', false) . '</span>';

    } elseif (is_tag()) {
        echo $sep . '<span class="breadcrumb-current">' . single_tag_title('', false) . '</span>';

    } elseif (is_search()) {
        echo $sep . '<span class="breadcrumb-current">' . $search_prefix . get_search_query() . '</span>';

    } elseif (is_page()) {
        echo $sep . '<span class="breadcrumb-current">' . get_the_title() . '</span>';

    } elseif (is_post_type_archive('broker')) {
        echo $sep . '<span class="breadcrumb-current">' . $broker_archive_text . '</span>';

    } elseif (is_archive()) {
        echo $sep . '<span class="breadcrumb-current">' . get_the_archive_title() . '</span>';
    }

    echo '</nav>';
}

/**
 * Breadcrumbs riêng cho Broker Post (silo structure)
 * Home > Broker Reviews > Exness > Hướng dẫn nạp tiền
 */
function fxt_broker_post_breadcrumbs() {
    $sep = '<span class="breadcrumb-sep">›</span>';
    $home_text = esc_html(get_theme_mod('fxt_breadcrumb_home', 'Home'));
    $broker_archive_text = esc_html(get_theme_mod('fxt_breadcrumb_broker_archive', 'Broker Reviews'));

    $parent = fxt_get_parent_broker(get_the_ID());

    echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
    echo '<a href="' . home_url('/') . '">' . $home_text . '</a>';
    echo $sep . '<a href="' . get_post_type_archive_link('broker') . '">' . $broker_archive_text . '</a>';

    if ($parent) {
        echo $sep . '<a href="' . esc_url($parent['permalink']) . '">' . esc_html($parent['title']) . '</a>';
    }

    echo $sep . '<span class="breadcrumb-current">' . get_the_title() . '</span>';
    echo '</nav>';
}

/**
 * Open Graph meta tags (chia sẻ Facebook, Zalo)
 */
add_action('wp_head', function () {
    if (is_singular()) {
        global $post;
        $title = get_the_title();
        $desc  = has_excerpt() ? get_the_excerpt() : wp_trim_words(strip_tags($post->post_content), 30);
        $url   = get_permalink();
        $image = has_post_thumbnail() ? get_the_post_thumbnail_url($post->ID, 'fxt-hero') : '';
    } else {
        $title = get_bloginfo('name');
        $desc  = get_bloginfo('description');
        $url   = home_url('/');
        $image = '';
    }
    ?>
    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($desc); ?>">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta property="og:type" content="<?php echo is_singular() ? 'article' : 'website'; ?>">
    <meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
    <?php if ($image): ?>
    <meta property="og:image" content="<?php echo esc_url($image); ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <?php
});

/**
 * Breadcrumb Schema (JSON-LD)
 * UPDATED: Thêm support cho broker_post silo breadcrumb
 */
add_action('wp_head', function () {
    if (is_front_page()) return;

    $items = [];
    $position = 1;
    $home_text = get_theme_mod('fxt_breadcrumb_home', 'Home');
    $broker_archive_text = get_theme_mod('fxt_breadcrumb_broker_archive', 'Broker Reviews');

    $items[] = [
        '@type'    => 'ListItem',
        'position' => $position++,
        'name'     => $home_text,
        'item'     => home_url('/'),
    ];

    if (is_singular('post')) {
        $cats = get_the_category();
        if ($cats) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $cats[0]->name,
                'item'     => get_category_link($cats[0]->term_id),
            ];
        }
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => get_the_title(),
        ];

    } elseif (is_singular('broker')) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => $broker_archive_text,
            'item'     => get_post_type_archive_link('broker'),
        ];
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => get_the_title(),
        ];

    } elseif (is_singular('broker_post')) {
        // Silo breadcrumb schema: Home > Broker Reviews > Exness > Sub Post
        $parent = fxt_get_parent_broker(get_the_ID());

        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => $broker_archive_text,
            'item'     => get_post_type_archive_link('broker'),
        ];

        if ($parent) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $parent['title'],
                'item'     => $parent['permalink'],
            ];
        }

        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => get_the_title(),
        ];
    }

    if (count($items) > 1) {
        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }
});

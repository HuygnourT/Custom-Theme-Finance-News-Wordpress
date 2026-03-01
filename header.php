<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo is_singular() ? esc_attr(get_the_excerpt()) : esc_attr(get_bloginfo('description')); ?>">
    <?php wp_head(); // WP sẽ inject CSS, JS, meta tags ở đây ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ═══ HEADER ═══ -->
<header class="site-header" id="site-header">
    <div class="container header-inner">

        <!-- Logo -->
        <div class="site-logo">
            <?php if (has_custom_logo()): ?>
                <?php the_custom_logo(); ?>
            <?php else: ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="site-title-link">
                    <span class="site-title-fx">FX</span>
                    <span class="site-title-text">Trading Today</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Navigation chính -->
        <nav class="main-nav" id="main-nav" aria-label="Menu chính">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'nav-menu',
                'fallback_cb'    => false,
                'depth'          => 2,
            ]);
            ?>
        </nav>

        <!-- CTA Button + Search -->
        <div class="header-actions">
            <button class="search-toggle" id="search-toggle" aria-label="Tìm kiếm">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>

            <?php
            $cta_link = get_theme_mod('fxt_default_affiliate_link', '#');
            $cta_text = get_theme_mod('fxt_cta_text', 'Mở tài khoản');
            if ($cta_link && $cta_link !== '#'): ?>
            <a href="<?php echo esc_url($cta_link); ?>" class="btn btn-cta header-cta" target="_blank" rel="noopener nofollow">
                <?php echo esc_html($cta_text); ?>
            </a>
            <?php endif; ?>

            <!-- Mobile menu toggle -->
            <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Menu">
                <span class="hamburger"></span>
            </button>
        </div>
    </div>

    <!-- Search overlay -->
    <div class="search-overlay" id="search-overlay">
        <div class="container">
            <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" class="search-input" name="s"
                       placeholder="Tìm kiếm bài viết, broker..."
                       value="<?php echo get_search_query(); ?>"
                       autocomplete="off">
                <button type="submit" class="search-submit">Tìm</button>
            </form>
        </div>
    </div>
</header>

<!-- ═══ MOBILE MENU OVERLAY ═══ -->
<div class="mobile-menu-overlay" id="mobile-menu-overlay">
    <div class="mobile-menu-inner">
        <div class="mobile-menu-header">
            <span class="site-title-fx">FX</span><span class="site-title-text">Trading Today</span>
            <button class="mobile-menu-close" id="mobile-menu-close" aria-label="Đóng menu">✕</button>
        </div>
        <?php
        wp_nav_menu([
            'theme_location' => 'mobile',
            'container'      => false,
            'menu_class'     => 'mobile-nav-menu',
            'fallback_cb'    => function() {
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'mobile-nav-menu',
                ]);
            },
            'depth'          => 2,
        ]);
        ?>
    </div>
</div>

<!-- ═══ MAIN CONTENT ═══ -->
<main class="site-main" id="main-content">

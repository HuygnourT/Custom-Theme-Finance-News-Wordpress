<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" id="site-header">
    <div class="container header-inner">
        <div class="site-logo">
            <?php if (has_custom_logo()): the_custom_logo(); else: ?>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-title-link">
                <span class="site-title-fx">FX</span><span class="site-title-text">Trading Today</span>
            </a>
            <?php endif; ?>
        </div>
        <nav class="main-nav" id="main-nav"><?php wp_nav_menu(['theme_location'=>'primary','container'=>false,'menu_class'=>'nav-menu','fallback_cb'=>false,'depth'=>2]); ?></nav>
        <div class="header-actions">
            <button class="search-toggle" id="search-toggle" aria-label="Search">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
            <?php $cta_link = get_theme_mod('fxt_default_affiliate_link',''); $cta_text = get_theme_mod('fxt_cta_text','Open Account'); if($cta_link): ?>
            <a href="<?php echo esc_url($cta_link); ?>" class="btn btn-cta btn-sm header-cta" target="_blank" rel="noopener nofollow"><?php echo esc_html($cta_text); ?></a>
            <?php endif; ?>
            <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Menu"><span class="hamburger"></span></button>
        </div>
    </div>
    <div class="search-overlay" id="search-overlay">
        <div class="container">
            <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" class="search-input" name="s" placeholder="<?php echo esc_attr(get_theme_mod('fxt_label_search_placeholder', 'Search articles, brokers...')); ?>" value="<?php echo get_search_query(); ?>">
                <button type="submit" class="search-submit"><?php echo esc_html(get_theme_mod('fxt_label_search_btn', 'Search')); ?></button>
            </form>
        </div>
    </div>
</header>

<?php
// ═══ CATEGORY BAR — hiển thị ngay dưới header ═══
if (function_exists('fxt_category_bar')) {
    fxt_category_bar();
}
?>

<div class="mobile-menu-overlay" id="mobile-menu-overlay">
    <div class="mobile-menu-inner">
        <div class="mobile-menu-header">
            <span class="site-title-fx">FX</span><span class="site-title-text">Trading Today</span>
            <button class="mobile-menu-close" id="mobile-menu-close">✕</button>
        </div>
        <?php wp_nav_menu(['theme_location'=>'mobile','container'=>false,'menu_class'=>'mobile-nav-menu','fallback_cb'=>function(){wp_nav_menu(['theme_location'=>'primary','container'=>false,'menu_class'=>'mobile-nav-menu']);},'depth'=>2]); ?>
    </div>
</div>

<main class="site-main" id="main-content">

<?php
/**
 * Search Results Template
 * TẤT CẢ text lấy từ Customizer
 * @package FXTradingToday
 */
get_header();
?>

<div class="container layout-with-sidebar">
    <div class="content-area">
        <?php fxt_breadcrumbs(); ?>
        <h1 class="page-title"><?php
            $search_title_tpl = get_theme_mod('fxt_label_search_results_title', 'Search results: "{query}"');
            echo esc_html(str_replace('{query}', get_search_query(), $search_title_tpl));
        ?></h1>

        <?php if (have_posts()): ?>
            <p class="search-count"><?php
                $count_tpl = get_theme_mod('fxt_label_search_count', 'Found {count} results');
                echo esc_html(str_replace('{count}', $wp_query->found_posts, $count_tpl));
            ?></p>
            <div class="posts-grid">
                <?php while (have_posts()): the_post(); ?>
                    <?php get_template_part('template-parts/content', 'card'); ?>
                <?php endwhile; ?>
            </div>
            <?php fxt_pagination(); ?>
        <?php else: ?>
            <?php get_template_part('template-parts/content', 'none'); ?>
        <?php endif; ?>
    </div>

    <aside class="sidebar" role="complementary">
        <?php get_sidebar(); ?>
    </aside>
</div>

<?php get_footer(); ?>

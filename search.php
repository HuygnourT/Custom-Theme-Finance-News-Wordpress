<?php
/**
 * Search Results Template
 * @package FXTradingToday
 */
get_header();
?>

<div class="container layout-with-sidebar">
    <div class="content-area">
        <?php fxt_breadcrumbs(); ?>
        <h1 class="page-title">Kết quả tìm kiếm: "<?php echo get_search_query(); ?>"</h1>

        <?php if (have_posts()): ?>
            <p class="search-count"><?php printf('Tìm thấy %d kết quả', $wp_query->found_posts); ?></p>
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

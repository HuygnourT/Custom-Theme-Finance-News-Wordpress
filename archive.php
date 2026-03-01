<?php
/**
 * Archive Template - Danh mục, tag, broker archive
 * @package FXTradingToday
 */
get_header();
?>

<div class="container layout-with-sidebar">
    <div class="content-area">

        <?php fxt_breadcrumbs(); ?>

        <header class="archive-header">
            <h1 class="archive-title"><?php the_archive_title(); ?></h1>
            <?php if (get_the_archive_description()): ?>
                <div class="archive-desc"><?php the_archive_description(); ?></div>
            <?php endif; ?>
        </header>

        <?php if (have_posts()): ?>
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

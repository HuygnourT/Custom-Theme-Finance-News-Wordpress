<?php get_header(); ?>
<div class="container layout-with-sidebar"><div class="content-area">
    <?php if (is_home() && !is_front_page()): ?>
        <h1 class="page-title"><?php echo esc_html(get_theme_mod('fxt_label_latest_posts', 'Latest Articles')); ?></h1>
    <?php endif; ?>
    <?php if (have_posts()): ?>
        <div class="posts-grid posts-grid-2"><?php while(have_posts()): the_post(); get_template_part('template-parts/content-card'); endwhile; ?></div>
        <?php fxt_pagination(); ?>
    <?php else: get_template_part('template-parts/content-none'); endif; ?>
</div><aside class="sidebar"><?php get_sidebar(); ?></aside></div>
<?php get_footer(); ?>

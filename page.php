<?php
/**
 * Page Template - Trang tĩnh
 * @package FXTradingToday
 */
get_header();
?>

<div class="container">
    <?php fxt_breadcrumbs(); ?>

    <?php while (have_posts()): the_post(); ?>
    <article class="single-page">
        <h1 class="page-title"><?php the_title(); ?></h1>
        <div class="entry-content">
            <?php the_content(); ?>
        </div>
    </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>

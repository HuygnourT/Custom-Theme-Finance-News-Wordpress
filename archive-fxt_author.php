<?php
/**
 * Archive Template - Danh sách tất cả Tác giả
 * @package FXTradingToday
 */
get_header();
?>

<div class="container">

    <?php fxt_breadcrumbs(); ?>

    <header class="archive-header">
        <h1 class="archive-title"><?php echo esc_html(get_theme_mod('fxt_authors_archive_title', 'Our Authors')); ?></h1>
        <?php $desc = get_theme_mod('fxt_authors_archive_desc', ''); if ($desc): ?>
            <div class="archive-desc"><?php echo esc_html($desc); ?></div>
        <?php endif; ?>
    </header>

    <?php if (have_posts()): ?>
    <div class="author-grid">
        <?php while (have_posts()): the_post();
            $job_title  = get_post_meta(get_the_ID(), '_fxt_author_job_title', true);
            $short_desc = get_post_meta(get_the_ID(), '_fxt_author_short_desc', true);
            $avatar     = get_the_post_thumbnail_url(get_the_ID(), 'medium');
        ?>
        <a href="<?php the_permalink(); ?>" class="author-card">
            <span class="author-card-avatar">
                <?php if ($avatar): ?>
                    <img src="<?php echo esc_url($avatar); ?>" alt="<?php the_title_attribute(); ?>" width="88" height="88" loading="lazy">
                <?php else: ?>
                    <span class="author-initial"><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
                <?php endif; ?>
            </span>
            <h2 class="author-card-name"><?php the_title(); ?></h2>
            <?php if ($job_title): ?><p class="author-card-title"><?php echo esc_html($job_title); ?></p><?php endif; ?>
            <?php if ($short_desc): ?><p class="author-card-desc"><?php echo esc_html($short_desc); ?></p><?php endif; ?>
        </a>
        <?php endwhile; ?>
    </div>

    <?php fxt_pagination(); ?>

    <?php else: ?>
        <p><?php echo esc_html(get_theme_mod('fxt_label_notfound', 'No Content Found')); ?></p>
    <?php endif; ?>

</div>

<?php get_footer(); ?>

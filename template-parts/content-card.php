<?php
/**
 * Template Part: Post Card - Card bài viết dạng dọc
 * Gọi bằng: get_template_part('template-parts/content', 'card');
 * 
 * @package FXTradingToday
 */
?>

<article class="post-card" id="post-<?php the_ID(); ?>">
    <?php if (has_post_thumbnail()): ?>
    <a href="<?php the_permalink(); ?>" class="post-card-image">
        <?php the_post_thumbnail('fxt-card', ['loading' => 'lazy']); ?>
    </a>
    <?php endif; ?>

    <div class="post-card-body">
        <?php
        $categories = get_the_category();
        if ($categories): ?>
        <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>" class="post-card-cat">
            <?php echo esc_html($categories[0]->name); ?>
        </a>
        <?php endif; ?>

        <h3 class="post-card-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <p class="post-card-excerpt"><?php echo get_the_excerpt(); ?></p>

        <div class="post-card-footer">
            <span class="post-card-date"><?php echo get_the_date(); ?></span>
            <span class="post-card-reading"><?php echo fxt_reading_time(); ?></span>
        </div>
    </div>
</article>

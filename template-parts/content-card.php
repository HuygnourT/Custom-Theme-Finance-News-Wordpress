<article class="post-card">
    <?php if (has_post_thumbnail()): ?>
    <a href="<?php the_permalink(); ?>" class="post-card-image"><?php the_post_thumbnail('fxt-card'); ?></a>
    <?php endif; ?>
    <div class="post-card-body">
        <?php $cats = get_the_category(); if($cats): ?>
        <a href="<?php echo get_category_link($cats[0]->term_id); ?>" class="post-card-cat"><?php echo esc_html($cats[0]->name); ?></a>
        <?php endif; ?>
        <h3 class="post-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="post-card-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
        <div class="post-card-footer">
            <span><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ' . esc_html(get_theme_mod('fxt_label_ago', 'previous')); ?></span>
            <span><?php echo esc_html(fxt_reading_time()); ?></span>
        </div>
    </div>
</article>

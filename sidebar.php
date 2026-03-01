<div class="sidebar-sticky">
<?php if (is_active_sidebar('main-sidebar')): dynamic_sidebar('main-sidebar'); else: ?>
    <div class="sidebar-widget">
        <h3 class="widget-title"><?php echo esc_html(get_theme_mod('fxt_sidebar_search', '🔍 Search')); ?></h3>
        <?php get_search_form(); ?>
    </div>
    <div class="sidebar-widget">
        <h3 class="widget-title"><?php echo esc_html(get_theme_mod('fxt_sidebar_brokers', '🏆 Top Broker')); ?></h3>
        <div class="sidebar-broker-list">
        <?php $top = new WP_Query(['post_type'=>'broker','posts_per_page'=>5,'meta_key'=>'_fxt_rating','orderby'=>'meta_value_num','order'=>'DESC']);
        if($top->have_posts()): while($top->have_posts()): $top->the_post(); $r = get_post_meta(get_the_ID(),'_fxt_rating',true); ?>
            <a href="<?php the_permalink(); ?>" class="sidebar-broker-item"><span><?php the_title(); ?></span><span class="sidebar-broker-rating"><?php echo esc_html($r); ?>/10</span></a>
        <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>
    </div>
    <div class="sidebar-widget">
        <h3 class="widget-title"><?php echo esc_html(get_theme_mod('fxt_sidebar_popular', '📈 Popular Articles')); ?></h3>
        <ul class="sidebar-post-list">
        <?php $pop = new WP_Query(['posts_per_page'=>5,'orderby'=>'comment_count','order'=>'DESC','ignore_sticky_posts'=>true]);
        if($pop->have_posts()): while($pop->have_posts()): $pop->the_post(); ?>
            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
        <?php endwhile; wp_reset_postdata(); endif; ?>
        </ul>
    </div>
<?php endif; ?>
</div>

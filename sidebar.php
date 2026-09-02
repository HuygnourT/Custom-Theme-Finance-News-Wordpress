<div class="sidebar-sticky">
<?php if (is_active_sidebar('main-sidebar')): dynamic_sidebar('main-sidebar'); else: ?>
    <?php if (!is_singular('generic_post')): ?>
    <div class="sidebar-widget">
        <h3 class="widget-title"><?php echo esc_html(get_theme_mod('fxt_sidebar_search', '🔍 Search')); ?></h3>
        <?php get_search_form(); ?>
    </div>
    <?php endif; ?>
    <div class="sidebar-widget">
        <h3 class="widget-title"><?php echo esc_html(get_theme_mod('fxt_sidebar_brokers', '🏆 Top Broker')); ?></h3>
        <div class="sidebar-broker-list">
        <?php $top = new WP_Query(['post_type'=>'broker','posts_per_page'=>5,'meta_key'=>'_fxt_rating','orderby'=>'meta_value_num','order'=>'DESC']);
        if($top->have_posts()): while($top->have_posts()): $top->the_post(); $r = get_post_meta(get_the_ID(),'_fxt_rating',true); ?>
            <a href="<?php the_permalink(); ?>" class="sidebar-broker-item"><span><?php echo esc_html(fxt_get_broker_name(get_the_ID())); ?></span><span class="sidebar-broker-rating"><?php echo esc_html($r); ?>/10</span></a>
        <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>
    </div>

    <?php
    // ═══ CUSTOM RELATED POSTS — chỉ hiển thị trên broker_post ═══
    $show_related_widget = false;
    $rel_data = null;
    if (is_singular('broker_post') && function_exists('fxt_get_sub_post_related_data')) {
        $rel_data = fxt_get_sub_post_related_data(get_the_ID());
        if (empty($rel_data['hidden'])) {
            $show_related_widget = true;
        }
    }
    ?>

    <?php if ($show_related_widget): ?>
    <div class="sidebar-widget sidebar-related-posts">
        <h3 class="widget-title"><?php echo esc_html($rel_data['title']); ?></h3>
        <ul class="sidebar-post-list sidebar-related-list">
            <?php if (!empty($rel_data['show_pillar']) && !empty($rel_data['parent'])): ?>
            <li class="sidebar-related-pillar">
                <a href="<?php echo esc_url($rel_data['parent']['permalink']); ?>">
                    <span class="sidebar-related-icon">⭐</span>
                    <strong><?php echo esc_html(get_theme_mod('fxt_broker_review_prefix', 'Review')); ?> <?php echo esc_html($rel_data['parent']['title']); ?></strong>
                </a>
            </li>
            <?php endif; ?>

            <?php foreach ($rel_data['posts'] as $p): ?>
            <li>
                <a href="<?php echo get_permalink($p->ID); ?>">
                    <span class="sidebar-related-icon">📝</span>
                    <?php echo esc_html($p->post_title); ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php else: ?>
    <!-- Default Popular Articles widget — hiện trên các trang khác -->
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

<?php endif; ?>
</div>
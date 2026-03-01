<?php
/**
 * Sidebar Template
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;
?>

<div class="sidebar-inner">

    <?php if (is_active_sidebar('main-sidebar')): ?>
        <?php dynamic_sidebar('main-sidebar'); ?>
    <?php else: ?>

        <!-- Default widgets nếu chưa config trong admin -->

        <!-- Search -->
        <div class="sidebar-widget">
            <h3 class="widget-title">Tìm kiếm</h3>
            <?php get_search_form(); ?>
        </div>

        <!-- Top Brokers -->
        <?php
        $top_brokers = new WP_Query([
            'post_type'      => 'broker',
            'posts_per_page' => 3,
            'meta_key'       => '_fxt_rating',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ]);
        if ($top_brokers->have_posts()):
        ?>
        <div class="sidebar-widget widget-top-brokers">
            <h3 class="widget-title">🏆 Top Broker</h3>
            <div class="sidebar-broker-list">
                <?php while ($top_brokers->have_posts()): $top_brokers->the_post();
                    $meta = fxt_get_broker_meta(get_the_ID());
                ?>
                <a href="<?php the_permalink(); ?>" class="sidebar-broker-item">
                    <span class="sidebar-broker-name"><?php the_title(); ?></span>
                    <span class="sidebar-broker-rating"><?php echo esc_html($meta['rating']); ?>/10</span>
                </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bài viết phổ biến -->
        <div class="sidebar-widget">
            <h3 class="widget-title">📈 Bài viết phổ biến</h3>
            <ul class="sidebar-post-list">
                <?php
                $popular = new WP_Query([
                    'posts_per_page' => 5,
                    'orderby'        => 'comment_count',
                    'order'          => 'DESC',
                ]);
                while ($popular->have_posts()): $popular->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
        </div>

    <?php endif; ?>

</div>

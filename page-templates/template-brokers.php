<?php
/**
 * Template Name: So sánh Broker
 * 
 * Custom page template - chọn trong WP Admin > Page > Template
 * Hiển thị bảng so sánh tất cả broker
 * 
 * TẤT CẢ text lấy từ Customizer
 * 
 * @package FXTradingToday
 */

get_header();
?>

<div class="container">

    <?php fxt_breadcrumbs(); ?>

    <div class="broker-compare-header">
        <h1 class="page-title"><?php echo esc_html(str_replace('{year}', date('Y'), get_theme_mod('fxt_compare_title', 'Best Forex Brokers Comparison {year}'))); ?></h1>
        <p class="page-desc"><?php echo esc_html(get_theme_mod('fxt_compare_desc', 'Detailed comparison of spreads, leverage, regulation, and features of the top forex brokers.')); ?></p>
    </div>

    <!-- Bộ lọc -->
    <div class="broker-filter" id="broker-filter-form">
        <input type="text" id="broker-search" class="search-input" placeholder="<?php echo esc_attr(get_theme_mod('fxt_compare_search_placeholder', 'Search brokers...')); ?>">
        <select id="broker-sort" class="broker-sort-select">
            <option value="rating"><?php echo esc_html(get_theme_mod('fxt_compare_sort_rating', 'Sort: Highest Rating')); ?></option>
            <option value="spread"><?php echo esc_html(get_theme_mod('fxt_compare_sort_spread', 'Sort: Lowest Spread')); ?></option>
            <option value="deposit"><?php echo esc_html(get_theme_mod('fxt_compare_sort_deposit', 'Sort: Lowest Minimum Deposit')); ?></option>
        </select>
    </div>

    <!-- Bảng broker -->
    <div class="broker-compare-table" id="broker-list">
        <?php
        $brokers = new WP_Query([
            'post_type'      => 'broker',
            'posts_per_page' => -1,
            'meta_key'       => '_fxt_rating',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ]);

        // Lấy labels từ Customizer (dùng chung với front-page & single-broker)
        $lbl_spread     = esc_html(get_theme_mod('fxt_label_spread', 'Spread'));
        $lbl_leverage   = esc_html(get_theme_mod('fxt_label_leverage', 'Leverage'));
        $lbl_deposit    = esc_html(get_theme_mod('fxt_label_deposit', 'Minimum Deposit'));
        $lbl_regulation = esc_html(get_theme_mod('fxt_label_regulation', 'Regulation'));
        $lbl_review     = esc_html(get_theme_mod('fxt_broker_read_review', 'Read Review'));
        $lbl_open       = esc_html(get_theme_mod('fxt_broker_open_account', 'Open Account'));

        if ($brokers->have_posts()):
            $rank = 1;
            while ($brokers->have_posts()): $brokers->the_post();
                $meta = fxt_get_broker_meta(get_the_ID());
                $spread_num = floatval(preg_replace('/[^0-9.]/', '', $meta['spread']));
                $deposit_num = floatval(preg_replace('/[^0-9.]/', '', $meta['min_deposit']));
        ?>
        <div class="broker-row"
             data-name="<?php echo esc_attr(strtolower(get_the_title())); ?>"
             data-rating="<?php echo esc_attr($meta['rating']); ?>"
             data-spread="<?php echo esc_attr($spread_num); ?>"
             data-deposit="<?php echo esc_attr($deposit_num); ?>">

            <div class="broker-row-rank">#<?php echo $rank++; ?></div>

            <div class="broker-row-main">
                <?php if (has_post_thumbnail()): ?>
                <div class="broker-row-logo">
                    <?php the_post_thumbnail('fxt-broker-logo'); ?>
                </div>
                <?php endif; ?>
                <div>
                    <h3 class="broker-row-name">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    <?php echo fxt_star_rating($meta['rating']); ?>
                </div>
            </div>

            <div class="broker-row-specs">
                <div class="broker-row-spec">
                    <span class="spec-label"><?php echo $lbl_spread; ?></span>
                    <span class="spec-value"><?php echo esc_html($meta['spread'] ?: 'N/A'); ?></span>
                </div>
                <div class="broker-row-spec">
                    <span class="spec-label"><?php echo $lbl_leverage; ?></span>
                    <span class="spec-value"><?php echo esc_html($meta['leverage'] ?: 'N/A'); ?></span>
                </div>
                <div class="broker-row-spec">
                    <span class="spec-label"><?php echo $lbl_deposit; ?></span>
                    <span class="spec-value"><?php echo esc_html($meta['min_deposit'] ?: 'N/A'); ?></span>
                </div>
                <div class="broker-row-spec">
                    <span class="spec-label"><?php echo $lbl_regulation; ?></span>
                    <span class="spec-value"><?php echo esc_html($meta['regulation'] ?: 'N/A'); ?></span>
                </div>
            </div>

            <div class="broker-row-actions">
                <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-sm"><?php echo $lbl_review; ?></a>
                <?php if ($meta['affiliate_link']): ?>
                <a href="<?php echo esc_url($meta['affiliate_link']); ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener nofollow">
                    <?php echo $lbl_open; ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
            endwhile;
            wp_reset_postdata();
        else: ?>
            <p class="content-none"><?php echo esc_html(get_theme_mod('fxt_compare_no_brokers', 'No brokers have been added yet.')); ?></p>
        <?php endif; ?>
    </div>

</div>

<?php get_footer(); ?>

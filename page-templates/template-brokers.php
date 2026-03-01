<?php
/**
 * Template Name: So sánh Broker
 * 
 * Custom page template - chọn trong WP Admin > Page > Template
 * Hiển thị bảng so sánh tất cả broker
 * 
 * @package FXTradingToday
 */

get_header();
?>

<div class="container">

    <?php fxt_breadcrumbs(); ?>

    <div class="broker-compare-header">
        <h1 class="page-title">So sánh sàn Forex tốt nhất <?php echo date('Y'); ?></h1>
        <p class="page-desc">So sánh chi tiết spread, leverage, regulation và các tính năng của các broker forex hàng đầu.</p>
    </div>

    <!-- Bộ lọc -->
    <div class="broker-filter" id="broker-filter-form">
        <input type="text" id="broker-search" class="search-input" placeholder="Tìm broker...">
        <select id="broker-sort" class="broker-sort-select">
            <option value="rating">Sắp xếp: Đánh giá cao nhất</option>
            <option value="spread">Sắp xếp: Spread thấp nhất</option>
            <option value="deposit">Sắp xếp: Nạp tối thiểu thấp nhất</option>
        </select>
    </div>

    <!-- Bảng broker -->
    <div class="broker-compare-table" id="broker-list">
        <?php
        $brokers = new WP_Query([
            'post_type'      => 'broker',
            'posts_per_page' => -1, // Lấy tất cả
            'meta_key'       => '_fxt_rating',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ]);

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
                    <span class="spec-label">Spread</span>
                    <span class="spec-value"><?php echo esc_html($meta['spread'] ?: 'N/A'); ?></span>
                </div>
                <div class="broker-row-spec">
                    <span class="spec-label">Đòn bẩy</span>
                    <span class="spec-value"><?php echo esc_html($meta['leverage'] ?: 'N/A'); ?></span>
                </div>
                <div class="broker-row-spec">
                    <span class="spec-label">Nạp tối thiểu</span>
                    <span class="spec-value"><?php echo esc_html($meta['min_deposit'] ?: 'N/A'); ?></span>
                </div>
                <div class="broker-row-spec">
                    <span class="spec-label">Giấy phép</span>
                    <span class="spec-value"><?php echo esc_html($meta['regulation'] ?: 'N/A'); ?></span>
                </div>
            </div>

            <div class="broker-row-actions">
                <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-sm">Đánh giá</a>
                <?php if ($meta['affiliate_link']): ?>
                <a href="<?php echo esc_url($meta['affiliate_link']); ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener nofollow">
                    Mở TK
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
            endwhile;
            wp_reset_postdata();
        else: ?>
            <p class="content-none">Chưa có broker nào được thêm.</p>
        <?php endif; ?>
    </div>

</div>

<?php get_footer(); ?>

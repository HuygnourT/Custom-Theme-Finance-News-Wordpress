<?php
/**
 * Single Broker Template - Đánh giá broker chi tiết
 * 
 * WP tự động dùng file này cho post type 'broker'
 * (single-{post_type}.php)
 * 
 * @package FXTradingToday
 */

get_header();
?>

<?php while (have_posts()): the_post();
    $meta = fxt_get_broker_meta(get_the_ID());
?>

<article class="single-broker" id="broker-<?php the_ID(); ?>">

    <!-- Broker Header -->
    <div class="broker-hero">
        <div class="container">
            <?php fxt_breadcrumbs(); ?>

            <div class="broker-hero-inner">
                <!-- Logo + Tên -->
                <div class="broker-hero-info">
                    <?php if (has_post_thumbnail()): ?>
                    <div class="broker-hero-logo">
                        <?php the_post_thumbnail('fxt-broker-logo'); ?>
                    </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="broker-hero-title">Đánh giá <?php the_title(); ?> <?php echo date('Y'); ?></h1>
                        <p class="broker-hero-excerpt"><?php the_excerpt(); ?></p>
                    </div>
                </div>

                <!-- Rating Box -->
                <div class="broker-rating-box">
                    <?php if ($meta['rating']): ?>
                    <div class="rating-big">
                        <span class="rating-number-big"><?php echo esc_html($meta['rating']); ?></span>
                        <span class="rating-max">/10</span>
                    </div>
                    <?php echo fxt_star_rating($meta['rating']); ?>
                    <?php endif; ?>

                    <?php if ($meta['affiliate_link']): ?>
                    <a href="<?php echo esc_url($meta['affiliate_link']); ?>"
                       class="btn btn-primary btn-lg btn-block"
                       target="_blank" rel="noopener nofollow">
                        Mở tài khoản <?php the_title(); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container layout-with-sidebar">
        <div class="content-area">

            <!-- Thông tin nhanh -->
            <div class="broker-specs-table">
                <h2 class="specs-title">Thông tin tổng quan</h2>
                <table class="specs-table">
                    <tbody>
                        <?php if ($meta['regulation']): ?>
                        <tr>
                            <th>🏛 Giấy phép</th>
                            <td><?php echo esc_html($meta['regulation']); ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($meta['spread']): ?>
                        <tr>
                            <th>📊 Spread</th>
                            <td><?php echo esc_html($meta['spread']); ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($meta['leverage']): ?>
                        <tr>
                            <th>📈 Đòn bẩy tối đa</th>
                            <td><?php echo esc_html($meta['leverage']); ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($meta['min_deposit']): ?>
                        <tr>
                            <th>💰 Nạp tối thiểu</th>
                            <td><?php echo esc_html($meta['min_deposit']); ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($meta['platforms']): ?>
                        <tr>
                            <th>🖥 Nền tảng</th>
                            <td><?php echo esc_html($meta['platforms']); ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($meta['founded']): ?>
                        <tr>
                            <th>📅 Năm thành lập</th>
                            <td><?php echo esc_html($meta['founded']); ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($meta['website_url']): ?>
                        <tr>
                            <th>🌐 Website</th>
                            <td><a href="<?php echo esc_url($meta['website_url']); ?>" target="_blank" rel="noopener"><?php echo esc_html($meta['website_url']); ?></a></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Ưu / Nhược điểm -->
            <?php if (!empty($meta['pros']) || !empty($meta['cons'])): ?>
            <div class="broker-pros-cons">
                <?php if (!empty($meta['pros'])): ?>
                <div class="pros-box">
                    <h3 class="pros-title">✅ Ưu điểm</h3>
                    <ul>
                        <?php foreach ($meta['pros'] as $pro): ?>
                        <li><?php echo esc_html(trim($pro)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($meta['cons'])): ?>
                <div class="cons-box">
                    <h3 class="cons-title">❌ Nhược điểm</h3>
                    <ul>
                        <?php foreach ($meta['cons'] as $con): ?>
                        <li><?php echo esc_html(trim($con)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- CTA giữa bài -->
            <?php if ($meta['affiliate_link']): ?>
            <div class="inline-cta">
                <a href="<?php echo esc_url($meta['affiliate_link']); ?>"
                   class="btn btn-primary btn-lg"
                   target="_blank" rel="noopener nofollow">
                    Mở tài khoản <?php the_title(); ?> ngay →
                </a>
            </div>
            <?php endif; ?>

            <!-- Nội dung review chi tiết -->
            <?php
            $content = get_the_content();
            $toc = fxt_table_of_contents($content);
            if ($toc) echo $toc;
            ?>

            <div class="single-content entry-content">
                <?php the_content(); ?>
            </div>

            <!-- CTA cuối bài -->
            <?php if ($meta['affiliate_link']): ?>
            <div class="bottom-cta-box">
                <h3>Bạn đã sẵn sàng giao dịch với <?php the_title(); ?>?</h3>
                <p>Mở tài khoản chỉ trong vài phút và bắt đầu giao dịch ngay hôm nay.</p>
                <a href="<?php echo esc_url($meta['affiliate_link']); ?>"
                   class="btn btn-primary btn-lg"
                   target="_blank" rel="noopener nofollow">
                    Đăng ký <?php the_title(); ?> miễn phí →
                </a>
            </div>
            <?php endif; ?>

            <!-- Share -->
            <?php fxt_share_buttons(); ?>

        </div>

        <!-- Sidebar -->
        <aside class="sidebar sidebar-sticky" role="complementary">
            <?php
            if (is_active_sidebar('broker-sidebar')) {
                dynamic_sidebar('broker-sidebar');
            } else {
                get_sidebar();
            }
            ?>
        </aside>
    </div>

</article>

<?php endwhile; ?>

<?php get_footer(); ?>

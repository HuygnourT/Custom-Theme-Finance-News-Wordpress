<?php
/**
 * Front Page Template - Trang chủ
 * 
 * WP ưu tiên file này cho trang chủ.
 * Layout: Hero → Top Brokers → Bài viết mới → Kiến thức
 * 
 * @package FXTradingToday
 */

get_header();
?>

<!-- ═══ HERO SECTION ═══ -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">
                Đánh giá sàn Forex <span class="text-accent">uy tín</span> cho nhà đầu tư Việt Nam
            </h1>
            <p class="hero-desc">
                So sánh chi tiết các sàn giao dịch Forex hàng đầu. Đánh giá khách quan về spread, leverage, regulation và trải nghiệm thực tế.
            </p>
            <div class="hero-actions">
                <a href="<?php echo get_post_type_archive_link('broker'); ?>" class="btn btn-primary btn-lg">
                    Xem đánh giá sàn
                </a>
                <a href="#latest-posts" class="btn btn-outline btn-lg">
                    Kiến thức Forex
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══ TOP BROKERS ═══ -->
<?php
$brokers = new WP_Query([
    'post_type'      => 'broker',
    'posts_per_page' => 5,
    'meta_key'       => '_fxt_rating',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
]);

if ($brokers->have_posts()):
?>
<section class="section top-brokers">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">🏆 Top Broker được đề xuất</h2>
            <a href="<?php echo get_post_type_archive_link('broker'); ?>" class="section-link">Xem tất cả →</a>
        </div>

        <div class="broker-cards">
            <?php $rank = 1; while ($brokers->have_posts()): $brokers->the_post();
                $meta = fxt_get_broker_meta(get_the_ID());
            ?>
            <div class="broker-card">
                <div class="broker-card-rank">#<?php echo $rank++; ?></div>

                <div class="broker-card-header">
                    <?php if (has_post_thumbnail()): ?>
                        <div class="broker-card-logo">
                            <?php the_post_thumbnail('fxt-broker-logo'); ?>
                        </div>
                    <?php endif; ?>
                    <div class="broker-card-info">
                        <h3 class="broker-card-name">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <?php echo fxt_star_rating($meta['rating']); ?>
                    </div>
                </div>

                <div class="broker-card-specs">
                    <?php if ($meta['spread']): ?>
                    <div class="broker-spec">
                        <span class="spec-label">Spread</span>
                        <span class="spec-value"><?php echo esc_html($meta['spread']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($meta['leverage']): ?>
                    <div class="broker-spec">
                        <span class="spec-label">Đòn bẩy</span>
                        <span class="spec-value"><?php echo esc_html($meta['leverage']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($meta['min_deposit']): ?>
                    <div class="broker-spec">
                        <span class="spec-label">Nạp tối thiểu</span>
                        <span class="spec-value"><?php echo esc_html($meta['min_deposit']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($meta['regulation']): ?>
                    <div class="broker-spec">
                        <span class="spec-label">Giấy phép</span>
                        <span class="spec-value"><?php echo esc_html($meta['regulation']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="broker-card-actions">
                    <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-sm">Đọc đánh giá</a>
                    <?php if ($meta['affiliate_link']): ?>
                    <a href="<?php echo esc_url($meta['affiliate_link']); ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener nofollow">
                        Mở tài khoản
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php wp_reset_postdata(); endif; ?>

<!-- ═══ BÀI VIẾT MỚI NHẤT ═══ -->
<section class="section latest-posts" id="latest-posts">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">📝 Bài viết mới nhất</h2>
            <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>" class="section-link">Xem tất cả →</a>
        </div>

        <div class="posts-grid posts-grid-3">
            <?php
            $latest = new WP_Query([
                'post_type'      => 'post',
                'posts_per_page' => 6,
                'post_status'    => 'publish',
            ]);

            while ($latest->have_posts()): $latest->the_post();
                get_template_part('template-parts/content', 'card');
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>

<!-- ═══ KIẾN THỨC FOREX ═══ -->
<?php
$knowledge_cat = get_category_by_slug('kien-thuc-forex');
if ($knowledge_cat):
    $knowledge = new WP_Query([
        'category__in'   => [$knowledge_cat->term_id],
        'posts_per_page' => 4,
        'post_status'    => 'publish',
    ]);

    if ($knowledge->have_posts()):
?>
<section class="section knowledge-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">📚 Kiến thức Forex</h2>
            <a href="<?php echo get_category_link($knowledge_cat->term_id); ?>" class="section-link">Xem tất cả →</a>
        </div>

        <div class="posts-grid posts-grid-2">
            <?php while ($knowledge->have_posts()): $knowledge->the_post(); ?>
                <?php get_template_part('template-parts/content', 'card-horizontal'); ?>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php
    wp_reset_postdata();
    endif;
endif;
?>

<!-- ═══ CTA SECTION ═══ -->
<section class="section cta-section">
    <div class="container">
        <div class="cta-box">
            <h2>Bắt đầu giao dịch Forex ngay hôm nay</h2>
            <p>So sánh và chọn sàn giao dịch phù hợp nhất với nhu cầu của bạn</p>
            <a href="<?php echo get_post_type_archive_link('broker'); ?>" class="btn btn-primary btn-lg">
                So sánh các sàn Forex →
            </a>
        </div>
    </div>
</section>

<?php get_footer(); ?>

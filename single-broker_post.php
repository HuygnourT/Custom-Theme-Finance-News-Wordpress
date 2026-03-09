<?php
/**
 * Single Broker Post Template — Bài phụ hỗ trợ broker pillar
 * 
 * URL: /broker-review/exness/huong-dan-nap-tien/
 * 
 * Layout: Giống single.php nhưng có:
 * - Breadcrumb silo: Home > Broker Reviews > Exness > Bài phụ
 * - Thông tin broker cha ở sidebar/header
 * - Affiliate link kế thừa từ broker cha
 * - Internal links đến bài pillar và các bài phụ khác
 * 
 * @package FXTradingToday
 */

get_header();

if (have_posts()): the_post();

// Lấy thông tin broker cha
$parent = fxt_get_parent_broker(get_the_ID());
$aff = '';
if ($parent) {
    $aff = $parent['affiliate_link'];
}
if (!$aff) {
    $aff = get_theme_mod('fxt_default_affiliate_link', '#');
}

$lbl_open = get_theme_mod('fxt_broker_open_account', 'Open Account');
?>

<article class="single-post single-broker-post" id="post-<?php the_ID(); ?>">

    <!-- Hero / Header -->
    <div class="single-hero">
        <div class="container">
            <?php fxt_broker_post_breadcrumbs(); ?>
            <h1 class="single-title"><?php the_title(); ?></h1>
            <?php fxt_post_meta(); ?>
        </div>
    </div>

    <!-- Nội dung -->
    <div class="container layout-with-sidebar">
        <div class="content-area">

            <!-- Featured Image -->
            <?php if (has_post_thumbnail()): ?>
            <div class="single-featured-image">
                <?php the_post_thumbnail('fxt-hero', ['loading' => 'eager']); ?>
            </div>
            <?php endif; ?>

            <!-- Broker Info Box (compact) -->
            <?php if ($parent): ?>
            <div class="broker-post-parent-box">
                <div class="broker-post-parent-info">
                    <span class="broker-post-parent-label">📊 Bài viết về</span>
                    <a href="<?php echo esc_url($parent['permalink']); ?>" class="broker-post-parent-link">
                        <strong><?php echo esc_html($parent['title']); ?></strong>
                    </a>
                    <?php if ($parent['meta']['rating']): ?>
                        <?php echo fxt_star_rating($parent['meta']['rating']); ?>
                    <?php endif; ?>
                </div>
                <?php if ($aff && $aff !== '#'): ?>
                <a href="<?php echo esc_url($aff); ?>" class="btn btn-cta btn-sm" target="_blank" rel="noopener nofollow">
                    <?php echo esc_html($lbl_open); ?> <?php echo esc_html($parent['title']); ?>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Table of Contents -->
            <?php
            $content = get_the_content();
            $content = apply_filters('the_content', $content);
            $toc = fxt_table_of_contents($content);
            if ($toc) echo $toc;
            ?>

            <!-- Nội dung bài viết -->
            <div class="single-content entry-content">
                <?php the_content(); ?>
            </div>

            <!-- Tags -->
            <?php
            $tags = get_the_tags();
            if ($tags): ?>
            <div class="single-tags">
                <span class="tags-label"><?php echo esc_html(get_theme_mod('fxt_label_tags', 'Tags:')); ?></span>
                <?php foreach ($tags as $tag): ?>
                    <a href="<?php echo get_tag_link($tag->term_id); ?>" class="tag-link"><?php echo esc_html($tag->name); ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Share buttons -->
            <?php fxt_share_buttons(); ?>

            <!-- CTA Box trước internal links -->
            <?php if ($parent && $aff && $aff !== '#'): ?>
            <div class="bottom-cta-box">
                <h3><?php echo esc_html(str_replace('{name}', $parent['title'], get_theme_mod('fxt_broker_cta_ready', 'Are you ready to trade with {name}?'))); ?></h3>
                <p><?php echo esc_html(get_theme_mod('fxt_broker_cta_desc', 'Open an account in just a few minutes and start trading today.')); ?></p>
                <a href="<?php echo esc_url($aff); ?>" class="btn btn-cta btn-lg" target="_blank" rel="noopener nofollow"><?php echo esc_html(get_theme_mod('fxt_broker_cta_btn', 'Get Started →')); ?></a>
            </div>
            <?php endif; ?>

            <!-- Internal Links: Bài pillar + các bài phụ khác -->
            <?php if ($parent):
                $siblings = fxt_get_broker_sub_posts($parent['ID'], get_the_ID());
            ?>
            <div class="broker-post-related-silo">
                <h3 class="section-title">📚 Thêm về <?php echo esc_html($parent['title']); ?></h3>
                <div class="silo-links">
                    <!-- Link về bài pillar -->
                    <a href="<?php echo esc_url($parent['permalink']); ?>" class="silo-link silo-link-pillar">
                        <span class="silo-link-icon">⭐</span>
                        <span class="silo-link-text">
                            <strong><?php echo esc_html(get_theme_mod('fxt_broker_review_prefix', 'Review')); ?> <?php echo esc_html($parent['title']); ?></strong>
                            <small>Bài đánh giá tổng hợp</small>
                        </span>
                        <span class="silo-link-arrow">→</span>
                    </a>

                    <!-- Links đến các bài phụ khác -->
                    <?php foreach ($siblings as $sib): ?>
                    <a href="<?php echo get_permalink($sib->ID); ?>" class="silo-link">
                        <span class="silo-link-icon">📝</span>
                        <span class="silo-link-text">
                            <strong><?php echo esc_html($sib->post_title); ?></strong>
                            <?php if (has_excerpt($sib->ID)): ?>
                            <small><?php echo esc_html(wp_trim_words(get_the_excerpt($sib->ID), 12)); ?></small>
                            <?php endif; ?>
                        </span>
                        <span class="silo-link-arrow">→</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Author box -->
            <div class="author-box">
                <div class="author-avatar">
                    <?php echo get_avatar(get_the_author_meta('ID'), 64); ?>
                </div>
                <div class="author-info">
                    <h4 class="author-name"><?php the_author(); ?></h4>
                    <p class="author-bio"><?php echo get_the_author_meta('description'); ?></p>
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <aside class="sidebar sidebar-sticky" role="complementary">
            <?php get_sidebar(); ?>
        </aside>
    </div>

</article>

<?php endif; get_footer(); ?>

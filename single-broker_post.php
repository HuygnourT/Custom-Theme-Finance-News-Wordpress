<?php
/**
 * Single Broker Post Template — Bài phụ hỗ trợ broker pillar
 * 
 * URL: /broker-review/exness/huong-dan-nap-tien/
 * 
 * UPDATED: Thêm Intro/Outro text, Custom Author Override
 * UPDATED: Xóa TOC, thêm CTA Buttons, Pros/Cons, Collapsible Sections
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

// Lấy custom meta data cho bài phụ
$post_cta_buttons = get_post_meta(get_the_ID(), '_fxt_sub_cta_buttons', true);
if (!is_array($post_cta_buttons)) $post_cta_buttons = [];
$post_pros = get_post_meta(get_the_ID(), '_fxt_sub_pros', true);
$post_cons = get_post_meta(get_the_ID(), '_fxt_sub_cons', true);
$post_pros_arr = array_filter(array_map('trim', explode("\n", $post_pros ?: '')));
$post_cons_arr = array_filter(array_map('trim', explode("\n", $post_cons ?: '')));
$post_sections = get_post_meta(get_the_ID(), '_fxt_sub_sections', true);
if (!is_array($post_sections)) $post_sections = [];

// NEW: Intro & Outro text
$intro_text = get_post_meta(get_the_ID(), '_fxt_sub_intro_text', true);
$outro_text = get_post_meta(get_the_ID(), '_fxt_sub_outro_text', true);

// NEW: Custom author
$custom_author = function_exists('fxt_get_custom_author') ? fxt_get_custom_author(get_the_ID()) : null;

$default_show = get_theme_mod('fxt_broker_section_show', '▼ Show details');
$default_hide = get_theme_mod('fxt_broker_section_hide', '▲ Hide details');
?>

<article class="single-post single-broker-post" id="post-<?php the_ID(); ?>">

    <!-- Hero / Header -->
    <div class="single-hero">
        <div class="container">
            <?php fxt_broker_post_breadcrumbs(); ?>
            <h1 class="single-title"><?php the_title(); ?></h1>
            <?php
            // Custom post meta: dùng custom author name nếu có
            if ($custom_author): ?>
            <div class="post-meta">
                <?php $categories = get_the_category(); if ($categories): ?>
                <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>" class="post-cat-link"><?php echo esc_html($categories[0]->name); ?></a>
                <?php endif; ?>
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <?php echo get_the_date(); ?>
                </span>
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <?php echo esc_html($custom_author['name']); ?>
                </span>
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?php echo esc_html(fxt_reading_time()); ?>
                </span>
            </div>
            <?php else:
                fxt_post_meta();
            endif; ?>
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

            <!-- ═══ NEW: INTRO TEXT (đoạn mở đầu custom) ═══ -->
            <?php if (!empty($intro_text)): ?>
            <div class="sub-post-intro entry-content">
                <?php echo apply_filters('the_content', $intro_text); ?>
            </div>
            <?php endif; ?>

            <!-- CTA Buttons (custom) -->
            <?php if (!empty($post_cta_buttons)): ?>
            <div class="sub-post-cta-buttons">
                <?php foreach ($post_cta_buttons as $cta):
                    if (empty($cta['text']) || empty($cta['url'])) continue;
                    $style = !empty($cta['style']) ? $cta['style'] : 'primary';
                    $new_tab = !empty($cta['new_tab']) ? ' target="_blank" rel="noopener nofollow"' : '';
                ?>
                <a href="<?php echo esc_url($cta['url']); ?>" class="btn btn-<?php echo esc_attr($style); ?> btn-lg"<?php echo $new_tab; ?>>
                    <?php echo esc_html($cta['text']); ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Pros & Cons -->
            <?php if ($post_pros_arr || $post_cons_arr): ?>
            <div class="broker-pros-cons">
                <?php if ($post_pros_arr): ?>
                <div class="pros-box">
                    <h3 class="pros-title"><?php echo esc_html(get_theme_mod('fxt_broker_pros_title', '✅ Pros')); ?></h3>
                    <ul>
                        <?php foreach ($post_pros_arr as $p): if (trim($p)): ?>
                        <li><?php echo esc_html(trim($p)); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <?php if ($post_cons_arr): ?>
                <div class="cons-box">
                    <h3 class="cons-title"><?php echo esc_html(get_theme_mod('fxt_broker_cons_title', '❌ Cons')); ?></h3>
                    <ul>
                        <?php foreach ($post_cons_arr as $c): if (trim($c)): ?>
                        <li><?php echo esc_html(trim($c)); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Nội dung bài viết (main editor) -->
            <div class="single-content entry-content">
                <?php the_content(); ?>
            </div>

            <!-- Collapsible Sections -->
            <?php if (!empty($post_sections)):
                foreach ($post_sections as $si => $sec):
                    if (empty($sec['title']) && empty($sec['content'])) continue;
                    $show_text = !empty($sec['show_text']) ? $sec['show_text'] : $default_show;
                    $hide_text = !empty($sec['hide_text']) ? $sec['hide_text'] : $default_hide;
                    $sec_pros = array_filter(array_map('trim', explode("\n", $sec['pros'] ?? '')));
                    $sec_cons = array_filter(array_map('trim', explode("\n", $sec['cons'] ?? '')));
            ?>
            <div class="broker-section sub-post-section" id="sub-section-<?php echo $si; ?>">
                <?php if (!empty($sec['title'])): ?>
                <h2 class="broker-section-title"><?php echo esc_html($sec['title']); ?></h2>
                <?php endif; ?>

                <?php if (!empty($sec['content'])): ?>
                <div class="broker-section-content entry-content">
                    <?php echo apply_filters('the_content', $sec['content']); ?>
                </div>
                <?php endif; ?>

                <!-- Section CTA Buttons -->
                <?php if (!empty($sec['cta_buttons']) && is_array($sec['cta_buttons'])):
                    $has_cta = false;
                    foreach ($sec['cta_buttons'] as $cta) {
                        if (!empty($cta['text']) && !empty($cta['url'])) { $has_cta = true; break; }
                    }
                    if ($has_cta):
                ?>
                <div class="sub-post-cta-buttons section-cta-buttons">
                    <?php foreach ($sec['cta_buttons'] as $cta):
                        if (empty($cta['text']) || empty($cta['url'])) continue;
                        $style = !empty($cta['style']) ? $cta['style'] : 'primary';
                        $new_tab = !empty($cta['new_tab']) ? ' target="_blank" rel="noopener nofollow"' : '';
                    ?>
                    <a href="<?php echo esc_url($cta['url']); ?>" class="btn btn-<?php echo esc_attr($style); ?>"<?php echo $new_tab; ?>>
                        <?php echo esc_html($cta['text']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; endif; ?>

                <!-- Section Pros/Cons -->
                <?php if (!empty($sec['show_proscons']) && ($sec_pros || $sec_cons)): ?>
                <div class="broker-pros-cons broker-section-proscons">
                    <?php if ($sec_pros): ?>
                    <div class="pros-box">
                        <h3 class="pros-title"><?php echo esc_html(get_theme_mod('fxt_broker_pros_title', '✅ Pros')); ?></h3>
                        <ul><?php foreach ($sec_pros as $p): if (trim($p)): ?><li><?php echo esc_html(trim($p)); ?></li><?php endif; endforeach; ?></ul>
                    </div>
                    <?php endif; ?>
                    <?php if ($sec_cons): ?>
                    <div class="cons-box">
                        <h3 class="cons-title"><?php echo esc_html(get_theme_mod('fxt_broker_cons_title', '❌ Cons')); ?></h3>
                        <ul><?php foreach ($sec_cons as $c): if (trim($c)): ?><li><?php echo esc_html(trim($c)); ?></li><?php endif; endforeach; ?></ul>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Collapsible detail -->
                <?php if (!empty($sec['collapsible']) && !empty($sec['collapse_detail'])): ?>
                <div class="broker-section-collapsible">
                    <div class="broker-section-detail" style="display:none;">
                        <div class="broker-section-detail-content entry-content">
                            <?php echo apply_filters('the_content', $sec['collapse_detail']); ?>
                        </div>
                    </div>
                    <button type="button" class="broker-toggle-detail"
                            data-show="<?php echo esc_attr($show_text); ?>"
                            data-hide="<?php echo esc_attr($hide_text); ?>">
                        <?php echo esc_html($show_text); ?>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; endif; ?>

            <!-- ═══ NEW: OUTRO TEXT (đoạn kết thúc custom) ═══ -->
            <?php if (!empty($outro_text)): ?>
            <div class="sub-post-outro entry-content">
                <?php echo apply_filters('the_content', $outro_text); ?>
            </div>
            <?php endif; ?>

            <!-- Tags -->
            <?php $tags = get_the_tags();
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

            <!-- CTA Box -->
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
                    <a href="<?php echo esc_url($parent['permalink']); ?>" class="silo-link silo-link-pillar">
                        <span class="silo-link-icon">⭐</span>
                        <span class="silo-link-text">
                            <strong><?php echo esc_html(get_theme_mod('fxt_broker_review_prefix', 'Review')); ?> <?php echo esc_html($parent['title']); ?></strong>
                            <small>Bài đánh giá tổng hợp</small>
                        </span>
                        <span class="silo-link-arrow">→</span>
                    </a>
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

            <!-- ═══ AUTHOR BOX: Custom author hoặc WP default ═══ -->
            <?php if ($custom_author): ?>
            <div class="author-box">
                <div class="author-avatar">
                    <?php if (!empty($custom_author['avatar'])): ?>
                        <img src="<?php echo esc_url($custom_author['avatar']); ?>" alt="<?php echo esc_attr($custom_author['name']); ?>" width="64" height="64" style="border-radius:50%">
                    <?php else: ?>
                        <?php echo get_avatar(get_the_author_meta('ID'), 64); ?>
                    <?php endif; ?>
                </div>
                <div class="author-info">
                    <h4 class="author-name"><?php echo esc_html($custom_author['name']); ?></h4>
                    <?php if (!empty($custom_author['title'])): ?>
                    <p class="author-title" style="font-size:.8rem;color:var(--c-primary);font-weight:600;margin-bottom:4px;"><?php echo esc_html($custom_author['title']); ?></p>
                    <?php endif; ?>
                    <p class="author-bio"><?php
                        if (!empty($custom_author['bio'])) {
                            echo esc_html($custom_author['bio']);
                        } else {
                            echo get_the_author_meta('description');
                        }
                    ?></p>
                </div>
            </div>
            <?php else: ?>
            <div class="author-box">
                <div class="author-avatar"><?php echo get_avatar(get_the_author_meta('ID'), 64); ?></div>
                <div class="author-info">
                    <h4 class="author-name"><?php the_author(); ?></h4>
                    <p class="author-bio"><?php echo get_the_author_meta('description'); ?></p>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <aside class="sidebar sidebar-sticky" role="complementary">
            <?php get_sidebar(); ?>
        </aside>
    </div>

</article>

<?php endif; get_footer(); ?>

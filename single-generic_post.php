<?php
/**
 * Single Generic Post Template — Bài phụ đa chủ đề
 * 
 * Tính năng: CTA Buttons, Pros/Cons, Collapsible Sections
 * 
 * @package FXTradingToday
 */

get_header();

if (have_posts()): the_post();

$parent = fxt_get_generic_parent(get_the_ID());
$default_aff = get_theme_mod('fxt_default_affiliate_link', '#');

$post_cta_buttons = get_post_meta(get_the_ID(), '_fxt_sub_cta_buttons', true);
if (!is_array($post_cta_buttons)) $post_cta_buttons = [];
$post_pros = get_post_meta(get_the_ID(), '_fxt_sub_pros', true);
$post_cons = get_post_meta(get_the_ID(), '_fxt_sub_cons', true);
$post_pros_arr = array_filter(array_map('trim', explode("\n", $post_pros ?: '')));
$post_cons_arr = array_filter(array_map('trim', explode("\n", $post_cons ?: '')));
$post_sections = get_post_meta(get_the_ID(), '_fxt_sub_sections', true);
if (!is_array($post_sections)) $post_sections = [];

$default_show = get_theme_mod('fxt_broker_section_show', '▼ Show details');
$default_hide = get_theme_mod('fxt_broker_section_hide', '▲ Hide details');
?>

<article class="single-post single-generic-post" id="post-<?php the_ID(); ?>">

    <!-- Hero -->
    <div class="single-hero">
        <div class="container">
            <?php // Breadcrumbs: Home > Parent > Title ?>
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo home_url('/'); ?>"><?php echo esc_html(get_theme_mod('fxt_breadcrumb_home', 'Home')); ?></a>
                <?php if ($parent): ?>
                <span class="breadcrumb-sep">›</span>
                <a href="<?php echo esc_url($parent['permalink']); ?>"><?php echo esc_html($parent['title']); ?></a>
                <?php endif; ?>
                <span class="breadcrumb-sep">›</span>
                <span class="breadcrumb-current"><?php the_title(); ?></span>
            </nav>
            <h1 class="single-title"><?php the_title(); ?></h1>
            <?php fxt_post_meta(); ?>
        </div>
    </div>

    <div class="container layout-with-sidebar">
        <div class="content-area">

            <!-- Featured Image -->
            <?php if (has_post_thumbnail()): ?>
            <div class="single-featured-image">
                <?php the_post_thumbnail('fxt-hero', ['loading' => 'eager']); ?>
            </div>
            <?php endif; ?>

            <!-- Parent Info Box -->
            <?php if ($parent): ?>
            <div class="broker-post-parent-box">
                <div class="broker-post-parent-info">
                    <span class="broker-post-parent-label">📂 Chủ đề:</span>
                    <a href="<?php echo esc_url($parent['permalink']); ?>" class="broker-post-parent-link">
                        <strong><?php echo esc_html($parent['title']); ?></strong>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- CTA Buttons -->
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
                    <ul><?php foreach ($post_pros_arr as $p): if (trim($p)): ?><li><?php echo esc_html(trim($p)); ?></li><?php endif; endforeach; ?></ul>
                </div>
                <?php endif; ?>
                <?php if ($post_cons_arr): ?>
                <div class="cons-box">
                    <h3 class="cons-title"><?php echo esc_html(get_theme_mod('fxt_broker_cons_title', '❌ Cons')); ?></h3>
                    <ul><?php foreach ($post_cons_arr as $c): if (trim($c)): ?><li><?php echo esc_html(trim($c)); ?></li><?php endif; endforeach; ?></ul>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Main Content -->
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

                <?php if (!empty($sec['cta_buttons']) && is_array($sec['cta_buttons'])):
                    $has_cta = false;
                    foreach ($sec['cta_buttons'] as $cta) { if (!empty($cta['text']) && !empty($cta['url'])) { $has_cta = true; break; } }
                    if ($has_cta): ?>
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

                <?php if (!empty($sec['show_proscons']) && ($sec_pros || $sec_cons)): ?>
                <div class="broker-pros-cons broker-section-proscons">
                    <?php if ($sec_pros): ?>
                    <div class="pros-box"><h3 class="pros-title"><?php echo esc_html(get_theme_mod('fxt_broker_pros_title', '✅ Pros')); ?></h3><ul><?php foreach ($sec_pros as $p): if (trim($p)): ?><li><?php echo esc_html(trim($p)); ?></li><?php endif; endforeach; ?></ul></div>
                    <?php endif; ?>
                    <?php if ($sec_cons): ?>
                    <div class="cons-box"><h3 class="cons-title"><?php echo esc_html(get_theme_mod('fxt_broker_cons_title', '❌ Cons')); ?></h3><ul><?php foreach ($sec_cons as $c): if (trim($c)): ?><li><?php echo esc_html(trim($c)); ?></li><?php endif; endforeach; ?></ul></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

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

            <!-- Tags -->
            <?php $tags = get_the_tags(); if ($tags): ?>
            <div class="single-tags">
                <span class="tags-label"><?php echo esc_html(get_theme_mod('fxt_label_tags', 'Tags:')); ?></span>
                <?php foreach ($tags as $tag): ?>
                    <a href="<?php echo get_tag_link($tag->term_id); ?>" class="tag-link"><?php echo esc_html($tag->name); ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php fxt_share_buttons(); ?>

            <!-- Sibling posts -->
            <?php
            $siblings = fxt_get_generic_siblings(get_the_ID());
            if ($parent && ($siblings || $parent)):
            ?>
            <div class="broker-post-related-silo">
                <h3 class="section-title">📚 Bài viết liên quan: <?php echo esc_html($parent['title']); ?></h3>
                <div class="silo-links">
                    <?php if (!empty($parent['ID'])): ?>
                    <a href="<?php echo esc_url($parent['permalink']); ?>" class="silo-link silo-link-pillar">
                        <span class="silo-link-icon">⭐</span>
                        <span class="silo-link-text"><strong><?php echo esc_html($parent['title']); ?></strong><small>Bài viết chính</small></span>
                        <span class="silo-link-arrow">→</span>
                    </a>
                    <?php endif; ?>
                    <?php foreach ($siblings as $sib): ?>
                    <a href="<?php echo get_permalink($sib->ID); ?>" class="silo-link">
                        <span class="silo-link-icon">📝</span>
                        <span class="silo-link-text"><strong><?php echo esc_html($sib->post_title); ?></strong></span>
                        <span class="silo-link-arrow">→</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Author -->
            <div class="author-box">
                <div class="author-avatar"><?php echo get_avatar(get_the_author_meta('ID'), 64); ?></div>
                <div class="author-info">
                    <h4 class="author-name"><?php the_author(); ?></h4>
                    <p class="author-bio"><?php echo get_the_author_meta('description'); ?></p>
                </div>
            </div>

        </div>

        <aside class="sidebar sidebar-sticky" role="complementary">
            <?php get_sidebar(); ?>
        </aside>
    </div>

</article>

<?php endif; get_footer(); ?>

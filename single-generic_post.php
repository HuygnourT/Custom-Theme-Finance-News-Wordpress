<?php
/**
 * Single Generic Post Template — Bài phụ đa chủ đề
 *
 * Nội dung chính dùng Gutenberg mặc định (the_content).
 *
 * @package FXTradingToday
 */

get_header();

if (have_posts()): the_post();

$parent = fxt_get_generic_parent(get_the_ID());
$default_aff = get_theme_mod('fxt_default_affiliate_link', '#');
$post_authors = fxt_get_post_authors(get_the_ID());
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
            <?php if (!empty($post_authors)): ?>
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
                    <?php fxt_render_author_byline(get_the_ID()); ?>
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
                    <span class="broker-post-parent-label">📂 Topic:</span>
                    <a href="<?php echo esc_url($parent['permalink']); ?>" class="broker-post-parent-link">
                        <strong><?php echo esc_html($parent['title']); ?></strong>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Nội dung bài viết (Gutenberg mặc định) -->
            <div class="single-content entry-content">
                <?php the_content(); ?>
            </div>

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
                <h3 class="section-title">📚 Related Post: <?php echo esc_html($parent['title']); ?></h3>
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
            <?php if (!empty($post_authors)):
                fxt_render_author_box(get_the_ID());
            else: ?>
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

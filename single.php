<?php
/**
 * Single Post Template - Bài viết chi tiết
 * TẤT CẢ text lấy từ Customizer
 * 
 * @package FXTradingToday
 */

get_header();
?>

<?php while (have_posts()): the_post(); ?>

<article class="single-post" id="post-<?php the_ID(); ?>">

    <!-- Hero / Header bài viết -->
    <div class="single-hero">
        <div class="container">
            <?php fxt_breadcrumbs(); ?>
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

            <!-- Table of Contents -->
            <?php
            $content = get_the_content();
            $content = apply_filters('the_content', $content);
            $toc = fxt_table_of_contents($content);

            if ($toc) {
                echo $toc;
                // Cần re-apply TOC vì function đã thêm IDs vào headings
                $content = apply_filters('the_content', get_the_content());
                $toc_with_content = fxt_table_of_contents($content);
            }
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

            <!-- Related posts -->
            <?php fxt_related_posts(4); ?>

        </div>

        <!-- Sidebar -->
        <aside class="sidebar sidebar-sticky" role="complementary">
            <?php get_sidebar(); ?>
        </aside>
    </div>

</article>

<?php endwhile; ?>

<?php get_footer(); ?>

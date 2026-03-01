<?php
/**
 * Index Template - Template mặc định / danh sách bài viết
 * 
 * Đây là file cuối cùng trong Template Hierarchy.
 * Nếu WP không tìm thấy template cụ thể nào, sẽ dùng file này.
 * 
 * @package FXTradingToday
 */

get_header(); // Load header.php
?>

<div class="container layout-with-sidebar">

    <!-- Nội dung chính -->
    <div class="content-area">

        <?php fxt_breadcrumbs(); ?>

        <?php if (is_home() && !is_front_page()): ?>
            <h1 class="page-title">Bài viết mới nhất</h1>
        <?php endif; ?>

        <?php if (have_posts()): ?>

            <div class="posts-grid">
                <?php while (have_posts()): the_post(); ?>
                    <?php get_template_part('template-parts/content', 'card'); ?>
                <?php endwhile; ?>
            </div>

            <?php fxt_pagination(); ?>

        <?php else: ?>
            <?php get_template_part('template-parts/content', 'none'); ?>
        <?php endif; ?>

    </div>

    <!-- Sidebar -->
    <aside class="sidebar" role="complementary">
        <?php get_sidebar(); ?>
    </aside>

</div>

<?php get_footer(); // Load footer.php ?>

<?php
/**
 * Template Part: Content None - Hiển thị khi không có bài viết
 * 
 * @package FXTradingToday
 */
?>

<div class="content-none">
    <h2>Không tìm thấy nội dung</h2>

    <?php if (is_search()): ?>
        <p>Không tìm thấy kết quả cho "<strong><?php echo get_search_query(); ?></strong>". Hãy thử từ khóa khác.</p>
    <?php else: ?>
        <p>Chưa có bài viết nào. Hãy quay lại sau nhé!</p>
    <?php endif; ?>

    <div class="content-none-search">
        <?php get_search_form(); ?>
    </div>
</div>

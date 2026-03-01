<?php
/**
 * 404 Template - Trang không tìm thấy
 * @package FXTradingToday
 */
get_header();
?>

<div class="container">
    <div class="page-404">
        <h1 class="error-code">404</h1>
        <h2>Trang không tồn tại</h2>
        <p>Trang bạn đang tìm kiếm đã bị xóa, đổi tên hoặc tạm thời không khả dụng.</p>
        <div class="error-actions">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">Về trang chủ</a>
        </div>
        <div class="error-search">
            <p>Hoặc thử tìm kiếm:</p>
            <?php get_search_form(); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>

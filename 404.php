<?php get_header(); ?>
<div class="container">
    <div class="page-404">
        <div class="error-code">404</div>
        <h2><?php echo esc_html(get_theme_mod('fxt_label_404_title', 'Page Not Found')); ?></h2>
        <p><?php echo esc_html(get_theme_mod('fxt_label_404_desc', 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.')); ?></p>
        <a href="<?php echo home_url('/'); ?>" class="btn btn-primary"><?php echo esc_html(get_theme_mod('fxt_label_back_home', 'Back to Homepage')); ?></a>
    </div>
</div>
<?php get_footer(); ?>

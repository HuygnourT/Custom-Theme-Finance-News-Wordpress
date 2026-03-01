<div class="content-none">
    <h2><?php echo esc_html(get_theme_mod('fxt_label_notfound', 'No Content Found')); ?></h2>
    <?php if (is_search()):
        $tpl = get_theme_mod('fxt_label_notfound_search', 'No results found for "{query}". Please try different keywords.');
        echo '<p>' . esc_html(str_replace('{query}', get_search_query(), $tpl)) . '</p>';
    else: ?>
        <p>No articles yet.</p>
    <?php endif; ?>
    <?php get_search_form(); ?>
</div>

</main>
<footer class="site-footer">
    <div class="footer-top"><div class="container footer-grid">
        <div>
            <div class="footer-logo"><span class="site-title-fx">FX</span><span class="site-title-text">Trading Today</span></div>
            <p class="footer-about"><?php echo esc_html(get_theme_mod('fxt_footer_about', 'Delivering reliable Forex education, broker reviews, and trading strategies to help investors make informed decisions.')); ?></p>
            <div class="footer-social">
                <?php foreach(['facebook'=>'f','telegram'=>'✈','youtube'=>'▶','tiktok'=>'♪'] as $k=>$icon): $url=get_theme_mod("fxt_social_{$k}"); if($url): ?>
                <a href="<?php echo esc_url($url); ?>" class="social-link" target="_blank" rel="noopener"><?php echo $icon; ?></a>
                <?php endif; endforeach; ?>
            </div>
        </div>
        <div>
            <?php if(has_nav_menu('footer')): ?>
            <h4 class="footer-widget-title"><?php echo esc_html(get_theme_mod('fxt_footer_col2_title', 'Quick Links')); ?></h4>
            <?php wp_nav_menu(['theme_location'=>'footer','container'=>false,'menu_class'=>'footer-links','depth'=>1]);
            elseif(is_active_sidebar('footer-col-2')): dynamic_sidebar('footer-col-2');
            else: ?>
            <h4 class="footer-widget-title"><?php echo esc_html(get_theme_mod('fxt_footer_col2_title', 'Categories')); ?></h4>
            <ul class="footer-links"><?php wp_list_categories(['title_li'=>'','show_count'=>0,'number'=>6]); ?></ul>
            <?php endif; ?>
        </div>
        <div>
            <?php if(is_active_sidebar('footer-col-3')): dynamic_sidebar('footer-col-3'); else: ?>
            <h4 class="footer-widget-title"><?php echo esc_html(get_theme_mod('fxt_footer_col3_title', 'More information')); ?></h4>
            <ul class="footer-links">
                <li><a href="<?php echo esc_url(home_url('/' . get_theme_mod('fxt_footer_about_slug', 'about-us') . '/')); ?>"><?php echo esc_html(get_theme_mod('fxt_footer_link_about', 'About Us')); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/' . get_theme_mod('fxt_footer_contact_slug', 'contact') . '/')); ?>"><?php echo esc_html(get_theme_mod('fxt_footer_link_contact', 'Contact')); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/' . get_theme_mod('fxt_footer_disclaimer_slug', 'disclaimer') . '/')); ?>"><?php echo esc_html(get_theme_mod('fxt_footer_link_disclaimer', 'Disclaimer')); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/' . get_theme_mod('fxt_footer_privacy_slug', 'privacy-policy') . '/')); ?>"><?php echo esc_html(get_theme_mod('fxt_footer_link_privacy', 'Privacy Policy')); ?></a></li>
            </ul>
            <?php endif; ?>
        </div>
    </div></div>
    <div class="footer-disclaimer"><div class="container"><p class="disclaimer-text"><?php echo wp_kses_post(get_theme_mod('fxt_disclaimer', '⚠️ Forex and CFD trading carry a high level of risk and may not be suitable for all investors. You could lose your entire investment')); ?></p></div></div>
    <div class="footer-bottom"><div class="container"><p class="copyright"><?php echo esc_html(get_theme_mod('fxt_copyright', '© ' . date('Y') . ' FX Trading Today. All rights reserved.')); ?></p></div></div>
</footer>
<button class="back-to-top" id="back-to-top">↑</button>
<?php wp_footer(); ?>
</body>
</html>

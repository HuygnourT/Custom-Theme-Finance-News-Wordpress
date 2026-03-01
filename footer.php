</main><!-- /.site-main -->

<!-- ═══ FOOTER ═══ -->
<footer class="site-footer">

    <!-- Footer widgets -->
    <div class="footer-top">
        <div class="container footer-grid">
            <!-- Cột 1: Giới thiệu -->
            <div class="footer-col">
                <div class="footer-logo">
                    <span class="site-title-fx">FX</span>
                    <span class="site-title-text">Trading Today</span>
                </div>
                <p class="footer-about">Cung cấp kiến thức, đánh giá sàn và chiến lược giao dịch Forex uy tín cho nhà đầu tư Việt Nam.</p>

                <!-- Social links -->
                <div class="footer-social">
                    <?php
                    $socials = [
                        'facebook'  => ['label' => 'Facebook',  'icon' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>'],
                        'telegram'  => ['label' => 'Telegram',  'icon' => '<path d="M21.198 2.433a2.242 2.242 0 0 0-1.022.215l-8.609 3.33c-2.068.8-4.133 1.598-5.724 2.21a405.15 405.15 0 0 1-2.849 1.09c-.42.147-.99.332-1.473.901-.728.855.075 1.644.357 1.882l4.052 2.97 1.748 5.349c.283.874 1.047 1.239 1.757.98l3.185-1.458a.491.491 0 0 1 .482.027l4.08 2.96c.262.19.588.327.939.327 1.079 0 1.678-.952 1.816-1.602L22.753 3.74c.123-.582-.027-1.14-.578-1.307z"/>'],
                        'youtube'   => ['label' => 'YouTube',   'icon' => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19.1c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>'],
                        'tiktok'    => ['label' => 'TikTok',    'icon' => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/>'],
                    ];
                    foreach ($socials as $key => $social):
                        $url = get_theme_mod("fxt_social_{$key}");
                        if ($url): ?>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" class="social-link social-<?php echo $key; ?>" aria-label="<?php echo $social['label']; ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><?php echo $social['icon']; ?></svg>
                        </a>
                    <?php endif;
                    endforeach; ?>
                </div>
            </div>

            <!-- Cột 2: Widget -->
            <div class="footer-col">
                <?php if (is_active_sidebar('footer-col-2')): ?>
                    <?php dynamic_sidebar('footer-col-2'); ?>
                <?php else: ?>
                    <h4 class="footer-widget-title">Danh mục</h4>
                    <ul class="footer-links">
                        <?php wp_list_categories(['title_li' => '', 'number' => 6]); ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Cột 3: Widget -->
            <div class="footer-col">
                <?php if (is_active_sidebar('footer-col-3')): ?>
                    <?php dynamic_sidebar('footer-col-3'); ?>
                <?php else: ?>
                    <h4 class="footer-widget-title">Thông tin</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo esc_url(home_url('/ve-chung-toi/')); ?>">Về chúng tôi</a></li>
                        <li><a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ</a></li>
                        <li><a href="<?php echo esc_url(home_url('/chinh-sach-bao-mat/')); ?>">Chính sách bảo mật</a></li>
                        <li><a href="<?php echo esc_url(home_url('/disclaimer/')); ?>">Disclaimer</a></li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Disclaimer / Cảnh báo rủi ro -->
    <div class="footer-disclaimer">
        <div class="container">
            <p class="disclaimer-text">
                ⚠️ <?php echo wp_kses_post(get_theme_mod('fxt_disclaimer', 'Giao dịch Forex/CFD có rủi ro cao. Bạn có thể mất toàn bộ vốn đầu tư. Hãy chỉ giao dịch với số tiền bạn có thể chấp nhận mất.')); ?>
            </p>
        </div>
    </div>

    <!-- Copyright -->
    <div class="footer-bottom">
        <div class="container">
            <p class="copyright">
                <?php echo esc_html(get_theme_mod('fxt_copyright', '© ' . date('Y') . ' FX Trading Today. All rights reserved.')); ?>
            </p>
        </div>
    </div>
</footer>

<!-- Back to top button -->
<button class="back-to-top" id="back-to-top" aria-label="Lên đầu trang">↑</button>

<?php wp_footer(); // WP inject JS ở đây ?>
</body>
</html>

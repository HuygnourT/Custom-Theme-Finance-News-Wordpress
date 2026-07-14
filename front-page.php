<?php
/**
 * front-page.php — Trang chủ (toàn bộ text điều khiển qua Customizer)
 *
 * Section (mỗi section hiện mặc định, ẩn khi tick "Ẩn section này"):
 *   1. Hero                          — fxt_hero_* (đã có)
 *   2. Top Recommended Brokers       — fxt_section_brokers + fxt_home_brokers_*
 *   3. How We Can Help               — fxt_home_help_*
 *   4. Guide By Topic                — fxt_home_guide_*  (broker header + LINK TỰ NHẬP)
 *   5. Everything You Need To Know   — fxt_home_eyntk_*
 *   6. About Us + Risk Disclaimer    — fxt_home_about_*
 *
 * @package FXTradingToday
 */

get_header(); ?>

<!-- ═══════════════ 1. HERO ═══════════════ -->
<section class="hero-section">
    <div class="container"><div class="hero-content">
        <div class="hero-badge"><span class="dot"></span> <?php echo esc_html(get_theme_mod('fxt_hero_badge', 'Latest Forex Broker Reviews ' . date('Y'))); ?></div>
        <h1 class="hero-title"><?php
            $title = get_theme_mod('fxt_hero_title', '{accent}Trusted{/accent} Forex Broker Reviews for Investors');
            echo str_replace(['{accent}', '{/accent}'], ['<span class="text-accent">', '</span>'], esc_html($title));
        ?></h1>
        <p class="hero-desc"><?php echo esc_html(get_theme_mod('fxt_hero_desc', 'Detailed comparison of top Forex brokers. Objective reviews of spreads, leverage, regulation, and real trading experience.')); ?></p>
        <?php
        $btn1 = fxt_parse_label_url(get_theme_mod('fxt_hero_btn1', 'View Broker Reviews'), 'View Broker Reviews', get_post_type_archive_link('broker'));
        $btn2 = fxt_parse_label_url(get_theme_mod('fxt_hero_btn2', 'Forex Education'), 'Forex Education', get_permalink(get_option('page_for_posts')));
        ?>
        <div class="hero-actions">
            <a href="<?php echo esc_url($btn1['url']); ?>" class="btn btn-primary btn-lg"><?php echo esc_html($btn1['label']); ?></a>
            <a href="<?php echo esc_url($btn2['url']); ?>" class="btn btn-outline btn-lg"><?php echo esc_html($btn2['label']); ?></a>
        </div>
        <?php $hero_stats = fxt_parse_stat_list(get_theme_mod('fxt_hero_stats_text', fxt_default_hero_stats_text())); ?>
        <?php if (!empty($hero_stats)): ?>
        <div class="hero-stats">
            <?php foreach ($hero_stats as $stat): ?>
            <div class="hero-stat">
                <div class="hero-stat-number"><?php echo esc_html($stat['value']); ?></div>
                <div class="hero-stat-label"><?php echo esc_html($stat['label']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div></div>
</section>

<?php $viewall = esc_html(get_theme_mod('fxt_section_viewall', 'View All →')); ?>

<!-- ═══════════════ 2. TOP BROKERS ═══════════════ -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo esc_html(get_theme_mod('fxt_section_brokers', '🏆 Top Recommended Brokers')); ?></h2>
            <a href="<?php echo get_post_type_archive_link('broker'); ?>" class="section-link"><?php echo $viewall; ?></a>
        </div>

        <?php $brokers_top = get_theme_mod('fxt_home_brokers_text_top', ''); if ($brokers_top): ?>
        <div class="home-lead"><?php echo wpautop(wp_kses_post($brokers_top)); ?></div>
        <?php endif; ?>

        <div class="broker-cards">
        <?php
        $brokers = new WP_Query(['post_type'=>'broker','posts_per_page'=>5,'meta_key'=>'_fxt_rating','orderby'=>'meta_value_num','order'=>'DESC']);
        $rank = 1;
        $lbl_spread     = esc_html(get_theme_mod('fxt_label_spread', 'Spread'));
        $lbl_leverage   = esc_html(get_theme_mod('fxt_label_leverage', 'Leverage'));
        $lbl_deposit    = esc_html(get_theme_mod('fxt_label_deposit', 'Minimum Deposit'));
        $lbl_regulation = esc_html(get_theme_mod('fxt_label_regulation', 'Regulation'));
        $lbl_review     = esc_html(get_theme_mod('fxt_broker_read_review', 'Read Review'));
        $lbl_open       = esc_html(get_theme_mod('fxt_broker_open_account', 'Open Account'));

        if ($brokers->have_posts()): while ($brokers->have_posts()): $brokers->the_post();
            $meta = fxt_get_broker_meta(get_the_ID());
        ?>
            <div class="broker-card <?php echo $rank === 1 ? 'featured' : ''; ?>">
                <div class="broker-rank">#<?php echo $rank++; ?></div>
                <div class="broker-info">
                    <div class="broker-logo"><?php echo fxt_get_broker_icon_html(get_the_ID()); ?></div>
                    <div><div class="broker-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div><?php echo fxt_star_rating($meta['rating']); ?></div>
                </div>
                <div class="broker-specs">
                    <div class="broker-spec"><span class="spec-label"><?php echo $lbl_spread; ?></span><span class="spec-value"><?php echo esc_html($meta['spread'] ?: 'N/A'); ?></span></div>
                    <div class="broker-spec"><span class="spec-label"><?php echo $lbl_leverage; ?></span><span class="spec-value"><?php echo esc_html($meta['leverage'] ?: 'N/A'); ?></span></div>
                    <div class="broker-spec"><span class="spec-label"><?php echo $lbl_deposit; ?></span><span class="spec-value"><?php echo esc_html($meta['min_deposit'] ?: 'N/A'); ?></span></div>
                    <div class="broker-spec"><span class="spec-label"><?php echo $lbl_regulation; ?></span><span class="spec-value"><?php echo esc_html($meta['regulation'] ?: 'N/A'); ?></span></div>
                </div>
                <div class="broker-actions">
                    <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-sm"><?php echo $lbl_review; ?></a>
                    <?php $aff = $meta['affiliate_link'] ?: get_theme_mod('fxt_default_affiliate_link',''); if($aff): ?>
                    <a href="<?php echo esc_url($aff); ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener nofollow"><?php echo $lbl_open; ?></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>

        <?php $brokers_bottom = get_theme_mod('fxt_home_brokers_text_bottom', ''); if ($brokers_bottom): ?>
        <div class="home-lead" style="margin-top:24px"><?php echo wpautop(wp_kses_post($brokers_bottom)); ?></div>
        <?php endif; ?>

        <?php $brokers_note = get_theme_mod('fxt_home_brokers_note', ''); if ($brokers_note): ?>
        <div class="home-note"><?php echo wp_kses_post($brokers_note); ?></div>
        <?php endif; ?>
    </div>
</section>

<!-- ═══════════════ 3. HOW WE CAN HELP ═══════════════ -->
<?php if (!get_theme_mod('fxt_home_help_hide', '')): ?>
<section class="section">
    <div class="container">
        <div class="section-header" style="justify-content:center">
            <h2 class="section-title"><?php echo esc_html(get_theme_mod('fxt_home_help_title', 'How We Can Help You')); ?></h2>
        </div>

        <?php $help_intro = get_theme_mod('fxt_home_help_intro', ''); if ($help_intro): ?>
        <div class="home-lead"><?php echo wpautop(wp_kses_post($help_intro)); ?></div>
        <?php endif; ?>

        <div class="help-grid">
            <?php for ($i = 1; $i <= 3; $i++):
                $b_title = get_theme_mod("fxt_home_help_box{$i}_title", '');
                $b_text  = get_theme_mod("fxt_home_help_box{$i}_text", '');
                $b_icon  = get_theme_mod("fxt_home_help_box{$i}_icon", '');
                if (!$b_title && !$b_text) continue;
            ?>
            <div class="help-box">
                <?php if ($b_icon): ?><span class="help-box-icon"><?php echo esc_html($b_icon); ?></span><?php endif; ?>
                <?php if ($b_title): ?><h3 class="help-box-title"><?php echo esc_html($b_title); ?></h3><?php endif; ?>
                <?php if ($b_text): ?><div class="help-box-text"><?php echo wp_kses_post($b_text); ?></div><?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>

        <?php
        $help_cta_text = get_theme_mod('fxt_home_help_cta_text', '');
        $help_cta_url  = get_theme_mod('fxt_home_help_cta_url', '');
        if ($help_cta_text && $help_cta_url): ?>
        <div class="help-cta">
            <a href="<?php echo esc_url($help_cta_url); ?>" class="btn btn-primary btn-lg"><?php echo esc_html($help_cta_text); ?></a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════ 4. GUIDE BY TOPIC (custom links) ═══════════════ -->
<?php if (!get_theme_mod('fxt_home_guide_hide', '')): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-header" style="justify-content:center">
            <h2 class="section-title"><?php echo esc_html(get_theme_mod('fxt_home_guide_title', 'Guide By Topic')); ?></h2>
        </div>

        <?php $guide_intro = get_theme_mod('fxt_home_guide_intro', ''); if ($guide_intro): ?>
        <div class="home-lead"><?php echo wpautop(wp_kses_post($guide_intro)); ?></div>
        <?php endif; ?>

        <div class="guide-grid">
        <?php
        for ($i = 1; $i <= 6; $i++):
            $bid = (int) get_theme_mod("fxt_home_guide_item{$i}_broker", 0);
            if ($bid <= 0) continue;

            $bpost = get_post($bid);
            if (!$bpost || $bpost->post_status !== 'publish' || $bpost->post_type !== 'broker') continue;

            // Parse danh sách link tự nhập: mỗi dòng "Tiêu đề | URL"
            $links_raw = (string) get_theme_mod("fxt_home_guide_item{$i}_links", '');
            $links = [];
            foreach (preg_split('/\r\n|\r|\n/', $links_raw) as $line) {
                $line = trim($line);
                if ($line === '') continue;
                $parts = explode('|', $line, 2);
                $ltitle = trim($parts[0]);
                $lurl   = isset($parts[1]) ? trim($parts[1]) : '';
                if ($lurl === '') continue; // bắt buộc có link
                $links[] = ['title' => ($ltitle !== '' ? $ltitle : $lurl), 'url' => $lurl];
            }

            $cta_text = get_theme_mod("fxt_home_guide_item{$i}_link_text", '');
            $cta_url  = get_theme_mod("fxt_home_guide_item{$i}_link_url", '');
        ?>
            <div class="guide-card">
                <div class="guide-card-head">
                    <span class="guide-card-logo"><?php echo fxt_get_broker_icon_html($bid, 'fxt-card-small'); ?></span>
                    <span class="guide-card-name"><a href="<?php echo esc_url(get_permalink($bid)); ?>"><?php echo esc_html(get_the_title($bid)); ?></a></span>
                </div>

                <?php if (!empty($links)): ?>
                <ul class="guide-links">
                    <?php foreach ($links as $l): ?>
                    <li><a href="<?php echo esc_url($l['url']); ?>"><?php echo esc_html($l['title']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <?php if ($cta_text && $cta_url): ?>
                <a href="<?php echo esc_url($cta_url); ?>" class="guide-card-cta" target="_blank" rel="noopener nofollow"><?php echo esc_html($cta_text); ?> →</a>
                <?php endif; ?>

                <?php if (empty($links) && !($cta_text && $cta_url)): ?>
                <p class="guide-empty"><a href="<?php echo esc_url(get_permalink($bid)); ?>"><?php echo esc_html(get_theme_mod('fxt_broker_read_review', 'Read Review')); ?> →</a></p>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════ 5. EVERYTHING YOU NEED TO KNOW ═══════════════ -->
<?php if (!get_theme_mod('fxt_home_eyntk_hide', '')): ?>
<section class="section">
    <div class="container">
        <div class="section-header" style="justify-content:center">
            <h2 class="section-title"><?php echo esc_html(get_theme_mod('fxt_home_eyntk_title', 'Everything You Need to Know')); ?></h2>
        </div>

        <?php $eyntk_intro = get_theme_mod('fxt_home_eyntk_intro', ''); if ($eyntk_intro): ?>
        <div class="eyntk-intro"><?php echo wpautop(wp_kses_post($eyntk_intro)); ?></div>
        <?php endif; ?>

        <div class="eyntk-grid">
            <?php for ($i = 1; $i <= 4; $i++):
                $bt = get_theme_mod("fxt_home_eyntk_b{$i}_title", '');
                $bx = get_theme_mod("fxt_home_eyntk_b{$i}_text", '');
                if (!$bt && !$bx) continue;
            ?>
            <div class="eyntk-block">
                <?php if ($bt): ?><h3><?php echo esc_html($bt); ?></h3><?php endif; ?>
                <?php if ($bx): ?><div class="eyntk-text"><?php echo wp_kses_post($bx); ?></div><?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════ 6. ABOUT US ═══════════════ -->
<?php if (!get_theme_mod('fxt_home_about_hide', '')): ?>
<section class="section section-alt">
    <div class="container">
        <div class="home-about-grid">
            <div class="about-main">
                <h2><?php echo esc_html(get_theme_mod('fxt_home_about_title', 'About Us')); ?></h2>
                <div class="about-body"><?php echo wpautop(wp_kses_post(get_theme_mod('fxt_home_about_text', ''))); ?></div>
            </div>

            <?php
            $about_accordion = fxt_parse_accordion_text(get_theme_mod('fxt_home_about_accordion_text', fxt_default_about_accordion_text()));
            fxt_render_about_accordion($about_accordion);
            ?>
        </div>

        <?php $about_disc = get_theme_mod('fxt_home_about_disclaimer', ''); if ($about_disc): ?>
        <div class="about-disclaimer"><?php echo wp_kses_post($about_disc); ?></div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>

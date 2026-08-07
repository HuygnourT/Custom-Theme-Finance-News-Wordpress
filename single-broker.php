<?php get_header(); if(have_posts()): the_post();
$meta = fxt_get_broker_meta(get_the_ID());
$aff = $meta['affiliate_link'] ?: get_theme_mod('fxt_default_affiliate_link','#');
$prefix = get_theme_mod('fxt_broker_review_prefix', 'Review');
$lbl_open = get_theme_mod('fxt_broker_open_account', 'Open Account');
$post_authors = fxt_get_post_authors(get_the_ID());
?>

<div class="broker-hero"><div class="container">
    <?php fxt_breadcrumbs(); ?>
    <div class="broker-hero-inner">
        <div class="broker-hero-info">
            <div class="broker-hero-logo"><?php echo fxt_get_broker_icon_html(get_the_ID()); ?></div>
            <div>
                <h1 class="broker-hero-title"><?php the_title(); ?></h1>
                <?php if(has_excerpt()): ?><p class="broker-hero-excerpt"><?php echo get_the_excerpt(); ?></p><?php endif; ?>

                <?php // Post meta: hiện tác giả (hỗ trợ nhiều tác giả) ?>
                <?php if (!empty($post_authors)): ?>
                <div class="post-meta" style="margin-top:12px">
                    <span style="color:rgba(255,255,255,.5)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <?php echo get_the_date(); ?>
                    </span>
                    <span style="color:rgba(255,255,255,.5)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <?php fxt_render_author_byline(get_the_ID()); ?>
                    </span>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <div class="broker-rating-box">
            <div class="rating-big"><span class="rating-number-big"><?php echo esc_html($meta['rating'] ?: '0'); ?></span><span class="rating-max">/10</span></div>
            <?php echo fxt_star_rating($meta['rating']); ?>
            <a href="<?php echo esc_url(fxt_get_broker_go_url(get_the_ID())); ?>" class="btn btn-cta btn-block" target="_blank" rel="noopener nofollow sponsored"><?php echo esc_html($lbl_open); ?> <?php echo esc_html(fxt_get_broker_name(get_the_ID())); ?></a>
        </div>
    </div>
</div></div>

<div class="container layout-with-sidebar">
    <div class="content-area">

        <!-- Overview / Specs Table -->
        <div class="broker-specs-table">
            <h2 class="specs-title"><?php echo esc_html(get_theme_mod('fxt_broker_overview', 'Overview')); ?></h2>
            <table class="specs-table">
                <?php
                $specs = [
                    ['regulation', '🏛', 'fxt_label_regulation', 'Regulation'],
                    ['spread', '📊', 'fxt_label_spread', 'Spread'],
                    ['leverage', '📈', 'fxt_label_leverage', 'Leverage'],
                    ['min_deposit', '💰', 'fxt_label_deposit', 'Min Deposit'],
                    ['platforms', '🖥', 'fxt_label_platforms', 'Platforms'],
                    ['founded', '📅', 'fxt_label_founded', 'Year Founded'],
                ];
                foreach ($specs as [$key, $icon, $mod_key, $default]):
                    if ($meta[$key]): ?>
                    <tr><th><?php echo $icon; ?> <?php echo esc_html(get_theme_mod($mod_key, $default)); ?></th><td><?php echo esc_html($meta[$key]); ?></td></tr>
                <?php endif; endforeach;
                if ($meta['website_url']): ?>
                    <tr><th>🌐 <?php echo esc_html(get_theme_mod('fxt_label_website', 'Website')); ?></th><td><a href="<?php echo esc_url(fxt_get_broker_go_url(get_the_ID())); ?>" target="_blank" rel="noopener nofollow sponsored"><?php echo esc_html($meta['website_url']); ?></a></td></tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Main Pros/Cons (global) -->
        <?php
        $pros = $meta['pros'];
        $cons = $meta['cons'];
        if ($pros || $cons): ?>
        <div class="broker-pros-cons">
            <?php if($pros): ?>
            <div class="pros-box">
                <h3 class="pros-title"><?php echo esc_html(get_theme_mod('fxt_broker_pros_title', '✅ Pros')); ?></h3>
                <ul>
                    <?php foreach($pros as $p): $p = trim($p); if($p): ?>
                    <li><?php echo esc_html($p); ?></li>
                    <?php endif; endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <?php if($cons): ?>
            <div class="cons-box">
                <h3 class="cons-title"><?php echo esc_html(get_theme_mod('fxt_broker_cons_title', '❌ Cons')); ?></h3>
                <ul>
                    <?php foreach($cons as $c): $c = trim($c); if($c): ?>
                    <li><?php echo esc_html($c); ?></li>
                    <?php endif; endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="inline-cta"><a href="<?php echo esc_url(fxt_get_broker_go_url(get_the_ID())); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener nofollow sponsored"><?php echo esc_html($lbl_open); ?> <?php echo esc_html(fxt_get_broker_name(get_the_ID())); ?> →</a></div>

        <!-- Main editor content (Gutenberg mặc định) -->
        <div class="entry-content"><?php the_content(); ?></div>

        <?php fxt_share_buttons(); ?>

        <div class="bottom-cta-box">
            <h3><?php echo esc_html(str_replace('{name}', fxt_get_broker_name(get_the_ID()), get_theme_mod('fxt_broker_cta_ready', 'Are you ready to trade with {name}?'))); ?></h3>
            <p><?php echo esc_html(get_theme_mod('fxt_broker_cta_desc', 'Open an account in just a few minutes and start trading today.')); ?></p>
            <a href="<?php echo esc_url(fxt_get_broker_go_url(get_the_ID())); ?>" class="btn btn-cta btn-lg" target="_blank" rel="noopener nofollow sponsored"><?php echo esc_html(get_theme_mod('fxt_broker_cta_btn', 'Get Started →')); ?></a>
        </div>

        <!-- ═══ AUTHOR BOX — hỗ trợ nhiều tác giả ═══ -->
        <?php fxt_render_author_box(get_the_ID()); ?>

    </div>

    <aside class="sidebar" role="complementary">
        <?php get_sidebar(); ?>
    </aside>
</div>

<?php endif; get_footer(); ?>

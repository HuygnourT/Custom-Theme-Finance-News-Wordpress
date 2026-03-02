<?php get_header(); if(have_posts()): the_post();
$meta = fxt_get_broker_meta(get_the_ID());
$aff = $meta['affiliate_link'] ?: get_theme_mod('fxt_default_affiliate_link','#');
$prefix = get_theme_mod('fxt_broker_review_prefix', 'Review');
$lbl_open = get_theme_mod('fxt_broker_open_account', 'Open Account');
?>

<div class="broker-hero"><div class="container">
    <?php fxt_breadcrumbs(); ?>
    <div class="broker-hero-inner">
        <div class="broker-hero-info">
            <div class="broker-hero-logo"><?php if(has_post_thumbnail()): the_post_thumbnail('fxt-broker-logo'); else: echo '<span style="font-size:1.5rem;font-weight:800;color:var(--c-primary)">'.esc_html(mb_substr(get_the_title(),0,2)).'</span>'; endif; ?></div>
            <div>
                <h1 class="broker-hero-title"><?php echo esc_html($prefix); ?> <?php the_title(); ?> <?php echo date('Y'); ?></h1>
                <?php if(has_excerpt()): ?><p class="broker-hero-excerpt"><?php echo get_the_excerpt(); ?></p><?php endif; ?>
            </div>
        </div>
        <div class="broker-rating-box">
            <div class="rating-big"><span class="rating-number-big"><?php echo esc_html($meta['rating'] ?: '0'); ?></span><span class="rating-max">/10</span></div>
            <?php echo fxt_star_rating($meta['rating']); ?>
            <a href="<?php echo esc_url($aff); ?>" class="btn btn-cta btn-block" target="_blank" rel="noopener nofollow"><?php echo esc_html($lbl_open); ?> <?php the_title(); ?></a>
        </div>
    </div>
</div></div>

<div class="container layout-with-sidebar">
    <div class="content-area">
        <div class="broker-specs-table">
            <h2 class="specs-title"><?php echo esc_html(get_theme_mod('fxt_broker_overview', 'Thông tin tổng quan')); ?></h2>
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
                    <tr><th>🌐 <?php echo esc_html(get_theme_mod('fxt_label_website', 'Website')); ?></th><td><a href="<?php echo esc_url($meta['website_url']); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html($meta['website_url']); ?></a></td></tr>
                <?php endif; ?>
            </table>
        </div>

        <?php
        // FIX: $meta['pros'] và $meta['cons'] ĐÃ LÀ ARRAY từ fxt_get_broker_meta()
        // Không cần explode() lại nữa
        $pros = $meta['pros']; // already array
        $cons = $meta['cons']; // already array
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

        <div class="inline-cta"><a href="<?php echo esc_url($aff); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener nofollow"><?php echo esc_html($lbl_open); ?> <?php the_title(); ?> →</a></div>

        <div class="entry-content"><?php the_content(); ?></div>
        <?php fxt_share_buttons(); ?>

        <div class="bottom-cta-box">
            <h3><?php echo esc_html(str_replace('{name}', get_the_title(), get_theme_mod('fxt_broker_cta_ready', 'Are you ready to trade with {name}?'))); ?></h3>
            <p><?php echo esc_html(get_theme_mod('fxt_broker_cta_desc', 'Open an account in just a few minutes and start trading today.')); ?></p>
            <a href="<?php echo esc_url($aff); ?>" class="btn btn-cta btn-lg" target="_blank" rel="noopener nofollow"><?php echo esc_html(get_theme_mod('fxt_broker_cta_btn', 'Bắt đầu ngay →')); ?></a>
        </div>
    </div>

    <aside class="sidebar" role="complementary">
        <?php get_sidebar(); ?>
    </aside>
</div>

<?php endif; get_footer(); ?>

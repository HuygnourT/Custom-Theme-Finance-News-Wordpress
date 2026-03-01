<?php get_header(); ?>

<section class="hero-section">
    <div class="container"><div class="hero-content">
        <div class="hero-badge"><span class="dot"></span> <?php echo esc_html(get_theme_mod('fxt_hero_badge', 'Latest Forex Broker Reviews' . date('Y'))); ?></div>
        <h1 class="hero-title"><?php
            $title = get_theme_mod('fxt_hero_title', '{accent}Trusted{/accent} Forex Broker Reviews for Investors');
            echo str_replace(['{accent}', '{/accent}'], ['<span class="text-accent">', '</span>'], esc_html($title));
        ?></h1>
        <p class="hero-desc"><?php echo esc_html(get_theme_mod('fxt_hero_desc', 'Detailed comparison of top Forex brokers. Objective reviews of spreads, leverage, regulation, and real trading experience.')); ?></p>
        <div class="hero-actions">
            <a href="<?php echo get_post_type_archive_link('broker'); ?>" class="btn btn-primary btn-lg"><?php echo esc_html(get_theme_mod('fxt_hero_btn1', 'View Broker Reviews')); ?></a>
            <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>" class="btn btn-outline btn-lg"><?php echo esc_html(get_theme_mod('fxt_hero_btn2', 'Forex Education')); ?></a>
        </div>
        <div class="hero-stats">
            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="hero-stat">
                <div class="hero-stat-number"><?php echo esc_html(get_theme_mod("fxt_hero_stat{$i}_num")); ?></div>
                <div class="hero-stat-label"><?php echo esc_html(get_theme_mod("fxt_hero_stat{$i}_label")); ?></div>
            </div>
            <?php endfor; ?>
        </div>
    </div></div>
</section>

<?php $viewall = esc_html(get_theme_mod('fxt_section_viewall', 'View All →')); ?>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo esc_html(get_theme_mod('fxt_section_brokers', '🏆 Top Recommended Brokers')); ?></h2>
            <a href="<?php echo get_post_type_archive_link('broker'); ?>" class="section-link"><?php echo $viewall; ?></a>
        </div>
        <div class="broker-cards">
        <?php
        $brokers = new WP_Query(['post_type'=>'broker','posts_per_page'=>5,'meta_key'=>'_fxt_rating','orderby'=>'meta_value_num','order'=>'DESC']);
        $rank = 1;
        $lbl_spread = esc_html(get_theme_mod('fxt_label_spread', 'Spread'));
        $lbl_leverage = esc_html(get_theme_mod('fxt_label_leverage', 'Leverage'));
        $lbl_deposit = esc_html(get_theme_mod('fxt_label_deposit', 'Minimum Deposit'));
        $lbl_regulation = esc_html(get_theme_mod('fxt_label_regulation', 'Regulation'));
        $lbl_review = esc_html(get_theme_mod('fxt_broker_read_review', 'Read Review'));
        $lbl_open = esc_html(get_theme_mod('fxt_broker_open_account', 'Open Account'));

        if ($brokers->have_posts()): while ($brokers->have_posts()): $brokers->the_post();
            $meta = fxt_get_broker_meta(get_the_ID());
        ?>
            <div class="broker-card <?php echo $rank === 1 ? 'featured' : ''; ?>">
                <div class="broker-rank">#<?php echo $rank++; ?></div>
                <div class="broker-info">
                    <div class="broker-logo"><?php if(has_post_thumbnail()): the_post_thumbnail('fxt-broker-logo'); else: echo '<span>'.esc_html(mb_substr(get_the_title(),0,2)).'</span>'; endif; ?></div>
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
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo esc_html(get_theme_mod('fxt_section_latest', '📝 Latest Articles')); ?></h2>
            <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>" class="section-link"><?php echo $viewall; ?></a>
        </div>
        <div class="posts-grid">
        <?php $latest = new WP_Query(['posts_per_page'=>3,'ignore_sticky_posts'=>true]);
        if($latest->have_posts()): while($latest->have_posts()): $latest->the_post(); get_template_part('template-parts/content-card'); endwhile; wp_reset_postdata(); endif; ?>
        </div>
    </div>
</section>

<?php $know_slug = get_theme_mod('fxt_knowledge_category', 'education'); $know_cat = get_category_by_slug($know_slug); ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo esc_html(get_theme_mod('fxt_section_knowledge', '📚 Forex Education')); ?></h2>
            <?php if($know_cat): ?><a href="<?php echo get_category_link($know_cat->term_id); ?>" class="section-link"><?php echo $viewall; ?></a><?php endif; ?>
        </div>
        <div class="posts-grid posts-grid-2">
        <?php $knowledge = new WP_Query(['posts_per_page'=>2,'category_name'=>$know_slug,'ignore_sticky_posts'=>true]);
        if($knowledge->have_posts()): while($knowledge->have_posts()): $knowledge->the_post(); get_template_part('template-parts/content-card-horizontal'); endwhile; wp_reset_postdata(); endif; ?>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container"><div class="cta-box">
        <h2><?php echo esc_html(get_theme_mod('fxt_cta_title', 'Start Trading Forex Today')); ?></h2>
        <p><?php echo esc_html(get_theme_mod('fxt_cta_desc', 'Compare and choose the best broker that suits your needs')); ?></p>
        <a href="<?php echo get_post_type_archive_link('broker'); ?>" class="btn btn-cta btn-lg"><?php echo esc_html(get_theme_mod('fxt_cta_btn', 'Compare Forex Brokers →')); ?></a>
    </div></div>
</section>

<?php get_footer(); ?>

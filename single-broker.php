<?php get_header(); if(have_posts()): the_post();
$meta = fxt_get_broker_meta(get_the_ID());
$sections = fxt_get_broker_sections(get_the_ID());
$aff = $meta['affiliate_link'] ?: get_theme_mod('fxt_default_affiliate_link','#');
$prefix = get_theme_mod('fxt_broker_review_prefix', 'Review');
$lbl_open = get_theme_mod('fxt_broker_open_account', 'Open Account');
$default_show = get_theme_mod('fxt_broker_section_show', '▼ Show details');
$default_hide = get_theme_mod('fxt_broker_section_hide', '▲ Hide details');
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

<?php
// ══════════════════════════════════════════════════
// HORIZONTAL TAB NAVIGATION
// ══════════════════════════════════════════════════
if (!empty($sections)):
?>
<div class="broker-tabs-wrapper">
    <div class="container">
        <nav class="broker-tabs" id="broker-tabs">
            <?php foreach ($sections as $i => $sec):
                if (empty($sec['title'])) continue;
                $tab_id = 'broker-section-' . $i;
            ?>
            <a href="#<?php echo esc_attr($tab_id); ?>" class="broker-tab<?php echo $i === 0 ? ' active' : ''; ?>" data-tab="<?php echo esc_attr($tab_id); ?>">
                <?php echo esc_html($sec['title']); ?>
            </a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>
<?php endif; ?>

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
                    <tr><th>🌐 <?php echo esc_html(get_theme_mod('fxt_label_website', 'Website')); ?></th><td><a href="<?php echo esc_url($meta['website_url']); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html($meta['website_url']); ?></a></td></tr>
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

        <div class="inline-cta"><a href="<?php echo esc_url($aff); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener nofollow"><?php echo esc_html($lbl_open); ?> <?php the_title(); ?> →</a></div>

        <?php
        // ══════════════════════════════════════════════════
        // BROKER SECTIONS (with tabs, pros/cons, collapsible)
        // ══════════════════════════════════════════════════
        if (!empty($sections)):
            foreach ($sections as $i => $sec):
                if (empty($sec['title'])) continue;
                $tab_id = 'broker-section-' . $i;
                $show_text = !empty($sec['show_text']) ? $sec['show_text'] : $default_show;
                $hide_text = !empty($sec['hide_text']) ? $sec['hide_text'] : $default_hide;
        ?>
        <div class="broker-section" id="<?php echo esc_attr($tab_id); ?>">
            <h2 class="broker-section-title"><?php echo esc_html($sec['title']); ?></h2>

            <?php if (!empty($sec['content'])): ?>
            <div class="broker-section-content entry-content">
                <?php echo wp_kses_post($sec['content']); ?>
            </div>
            <?php endif; ?>

            <?php
            // Per-section Pros/Cons
            if (!empty($sec['show_proscons']) && (!empty($sec['pros_arr']) || !empty($sec['cons_arr']))):
            ?>
            <div class="broker-pros-cons broker-section-proscons">
                <?php if (!empty($sec['pros_arr'])): ?>
                <div class="pros-box">
                    <h3 class="pros-title"><?php echo esc_html(get_theme_mod('fxt_broker_pros_title', '✅ Pros')); ?></h3>
                    <ul>
                        <?php foreach ($sec['pros_arr'] as $p): if (trim($p)): ?>
                        <li><?php echo esc_html(trim($p)); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <?php if (!empty($sec['cons_arr'])): ?>
                <div class="cons-box">
                    <h3 class="cons-title"><?php echo esc_html(get_theme_mod('fxt_broker_cons_title', '❌ Cons')); ?></h3>
                    <ul>
                        <?php foreach ($sec['cons_arr'] as $c): if (trim($c)): ?>
                        <li><?php echo esc_html(trim($c)); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php
            // Collapsible detail
            if (!empty($sec['collapsible']) && !empty($sec['collapse_detail'])):
            ?>
            <div class="broker-section-collapsible">
                <div class="broker-section-detail" style="display:none;">
                    <div class="broker-section-detail-content entry-content">
                        <?php echo wp_kses_post($sec['collapse_detail']); ?>
                    </div>
                </div>
                <button type="button" class="broker-toggle-detail"
                        data-show="<?php echo esc_attr($show_text); ?>"
                        data-hide="<?php echo esc_attr($hide_text); ?>">
                    <?php echo esc_html($show_text); ?>
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php
            endforeach;
        endif;
        ?>

        <!-- Main editor content -->
        <div class="entry-content"><?php the_content(); ?></div>

        <?php fxt_share_buttons(); ?>

        <div class="bottom-cta-box">
            <h3><?php echo esc_html(str_replace('{name}', get_the_title(), get_theme_mod('fxt_broker_cta_ready', 'Are you ready to trade with {name}?'))); ?></h3>
            <p><?php echo esc_html(get_theme_mod('fxt_broker_cta_desc', 'Open an account in just a few minutes and start trading today.')); ?></p>
            <a href="<?php echo esc_url($aff); ?>" class="btn btn-cta btn-lg" target="_blank" rel="noopener nofollow"><?php echo esc_html(get_theme_mod('fxt_broker_cta_btn', 'Get Started →')); ?></a>
        </div>
    </div>

    <aside class="sidebar" role="complementary">
        <?php get_sidebar(); ?>
    </aside>
</div>

<?php endif; get_footer(); ?>

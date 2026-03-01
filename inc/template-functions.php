<?php
/**
 * Template Functions v2 - Tất cả text lấy từ Customizer
 * @package FXTradingToday
 */
if (!defined('ABSPATH')) exit;

/**
 * Reading time - text từ Customizer
 */
function fxt_reading_time($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $minutes = max(1, ceil($word_count / 200));
    // Lấy template từ Customizer, thay {min} = số phút
    $template = get_theme_mod('fxt_label_reading_time', '{min} min read');
    return str_replace('{min}', $minutes, $template);
}

/**
 * Star rating
 */
function fxt_star_rating($rating, $max = 10) {
    if (empty($rating)) return '';
    $stars_5 = round(($rating / $max) * 5, 1);
    $full = floor($stars_5);
    $half = ($stars_5 - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $html = '<div class="star-rating" title="' . esc_attr($rating . '/' . $max) . '">';
    $html .= str_repeat('<span class="star star-full">★</span>', $full);
    if ($half) $html .= '<span class="star star-half">★</span>';
    $html .= str_repeat('<span class="star star-empty">☆</span>', $empty);
    $html .= '<span class="rating-number">' . esc_html($rating) . '/10</span>';
    $html .= '</div>';
    return $html;
}

/**
 * Pagination - text từ Customizer
 */
function fxt_pagination() {
    global $wp_query;
    if ($wp_query->max_num_pages <= 1) return;
    echo '<nav class="pagination-nav">';
    echo paginate_links([
        'mid_size'  => 2,
        'prev_text' => esc_html(get_theme_mod('fxt_label_prev', '← Trước')),
        'next_text' => esc_html(get_theme_mod('fxt_label_next', 'Sau →')),
        'type'      => 'list',
    ]);
    echo '</nav>';
}

/**
 * Table of Contents - title từ Customizer
 */
function fxt_table_of_contents($content = '') {
    if (empty($content)) {
        global $post;
        $content = $post->post_content ?? '';
    }
    preg_match_all('/<h([2-3])[^>]*>(.*?)<\/h[2-3]>/i', $content, $matches, PREG_SET_ORDER);
    if (count($matches) < 3) return '';

    $toc_title = esc_html(get_theme_mod('fxt_label_toc', '📑 Table of Contents'));

    $toc = '<div class="toc-wrapper">';
    $toc .= '<div class="toc-header" onclick="this.parentElement.classList.toggle(\'toc-collapsed\')">';
    $toc .= '<h4>' . $toc_title . '</h4>';
    $toc .= '<span class="toc-toggle">▼</span>';
    $toc .= '</div><nav class="toc-body"><ul class="toc-list">';

    foreach ($matches as $i => $match) {
        $level = $match[1];
        $text = strip_tags($match[2]);
        $id = 'heading-' . sanitize_title($text) . '-' . $i;
        $indent = $level == '3' ? ' class="toc-sub"' : '';
        $toc .= "<li{$indent}><a href=\"#{$id}\">{$text}</a></li>";
    }

    $toc .= '</ul></nav></div>';
    return $toc;
}

/**
 * Related posts - title từ Customizer
 */
function fxt_related_posts($count = 4) {
    global $post;
    $categories = get_the_category($post->ID);
    if (empty($categories)) return;

    $related = new WP_Query([
        'category__in' => [$categories[0]->term_id],
        'post__not_in' => [$post->ID],
        'posts_per_page' => $count,
        'orderby' => 'rand',
    ]);
    if (!$related->have_posts()) return;

    $title = esc_html(get_theme_mod('fxt_label_related', 'Related Articles'));
    ?>
    <section class="related-posts">
        <h3 class="section-title"><?php echo $title; ?></h3>
        <div class="related-grid">
            <?php while ($related->have_posts()): $related->the_post(); ?>
            <article class="related-card">
                <?php if (has_post_thumbnail()): ?>
                <a href="<?php the_permalink(); ?>" class="related-card-image"><?php the_post_thumbnail('fxt-card-small'); ?></a>
                <?php endif; ?>
                <div class="related-card-content">
                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                    <span class="related-card-date"><?php echo get_the_date(); ?></span>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
    </section>
    <?php wp_reset_postdata();
}

/**
 * Share buttons - label từ Customizer
 */
function fxt_share_buttons() {
    $url = urlencode(get_permalink());
    $title = urlencode(get_the_title());
    $share_label = esc_html(get_theme_mod('fxt_label_share', 'Share: '));
    ?>
    <div class="share-buttons">
        <span class="share-label"><?php echo $share_label; ?></span>
        <a href="https://www.facebook.com/sharer.php?u=<?php echo $url; ?>" target="_blank" rel="noopener" class="share-btn share-fb" title="Facebook">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
        </a>
        <a href="https://twitter.com/intent/tweet?url=<?php echo $url; ?>&text=<?php echo $title; ?>" target="_blank" rel="noopener" class="share-btn share-tw" title="Twitter">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>
        </a>
        <a href="https://t.me/share/url?url=<?php echo $url; ?>&text=<?php echo $title; ?>" target="_blank" rel="noopener" class="share-btn share-tg" title="Telegram">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M21.198 2.433a2.242 2.242 0 0 0-1.022.215l-8.609 3.33c-2.068.8-4.133 1.598-5.724 2.21a405.15 405.15 0 0 1-2.849 1.09c-.42.147-.99.332-1.473.901-.728.855.075 1.644.357 1.882l4.052 2.97 1.748 5.349c.283.874 1.047 1.239 1.757.98l.006-.002 3.185-1.458a.491.491 0 0 1 .482.027l4.08 2.96c.262.19.588.327.939.327 1.079 0 1.678-.952 1.816-1.602L22.753 3.74c.123-.582-.027-1.14-.578-1.307z"/></svg>
        </a>
    </div>
    <?php
}

/**
 * Get broker meta fields
 */
function fxt_get_broker_meta($post_id) {
    return [
        'rating'         => get_post_meta($post_id, '_fxt_rating', true),
        'spread'         => get_post_meta($post_id, '_fxt_spread', true),
        'leverage'       => get_post_meta($post_id, '_fxt_leverage', true),
        'min_deposit'    => get_post_meta($post_id, '_fxt_min_deposit', true),
        'regulation'     => get_post_meta($post_id, '_fxt_regulation', true),
        'founded'        => get_post_meta($post_id, '_fxt_founded', true),
        'platforms'      => get_post_meta($post_id, '_fxt_platforms', true),
        'affiliate_link' => get_post_meta($post_id, '_fxt_affiliate_link', true),
        'website_url'    => get_post_meta($post_id, '_fxt_website_url', true),
        'pros'           => get_post_meta($post_id, '_fxt_pros', true),
        'cons'           => get_post_meta($post_id, '_fxt_cons', true),
    ];
}

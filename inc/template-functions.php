<?php
/**
 * Template Functions - Helper functions dùng trong template files
 * 
 * Giống utils.js / helpers.js trong Node.js app
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

/**
 * Hiển thị thời gian đọc bài viết
 * Gọi: fxt_reading_time()
 */
function fxt_reading_time($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $minutes = max(1, ceil($word_count / 200)); // 200 từ/phút
    return $minutes . ' phút đọc';
}

/**
 * Hiển thị rating dạng sao
 * Gọi: fxt_star_rating(8.5)
 */
function fxt_star_rating($rating, $max = 10) {
    if (empty($rating)) return '';

    $stars_5 = round(($rating / $max) * 5, 1); // Convert sang thang 5
    $full    = floor($stars_5);
    $half    = ($stars_5 - $full) >= 0.5 ? 1 : 0;
    $empty   = 5 - $full - $half;

    $html = '<div class="star-rating" title="' . esc_attr($rating . '/' . $max) . '">';
    $html .= str_repeat('<span class="star star-full">★</span>', $full);
    if ($half) $html .= '<span class="star star-half">★</span>';
    $html .= str_repeat('<span class="star star-empty">☆</span>', $empty);
    $html .= '<span class="rating-number">' . esc_html($rating) . '/10</span>';
    $html .= '</div>';

    return $html;
}

/**
 * Hiển thị estimated reading time + ngày đăng
 * Gọi: fxt_post_meta()
 */
function fxt_post_meta() {
    $time_diff = human_time_diff(get_the_time('U'), current_time('timestamp'));
    ?>
    <div class="post-meta">
        <span class="post-meta-date">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <?php echo esc_html($time_diff); ?> trước
        </span>
        <span class="post-meta-reading">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            <?php echo esc_html(fxt_reading_time()); ?>
        </span>
        <?php
        $categories = get_the_category();
        if ($categories): ?>
        <span class="post-meta-cat">
            <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>" class="post-cat-link">
                <?php echo esc_html($categories[0]->name); ?>
            </a>
        </span>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Hiển thị pagination (phân trang)
 * Gọi: fxt_pagination()
 */
function fxt_pagination() {
    global $wp_query;

    if ($wp_query->max_num_pages <= 1) return;

    $args = [
        'mid_size'  => 2,
        'prev_text' => '← Trước',
        'next_text' => 'Sau →',
        'type'      => 'list',
    ];

    echo '<nav class="pagination-nav">';
    echo paginate_links($args);
    echo '</nav>';
}

/**
 * Hiển thị Table of Contents tự động từ headings
 * Gọi trong single.php: echo fxt_table_of_contents($post->post_content)
 */
function fxt_table_of_contents($content) {
    // Tìm tất cả h2, h3 trong content
    preg_match_all('/<h([2-3])[^>]*>(.*?)<\/h[2-3]>/i', $content, $matches, PREG_SET_ORDER);

    if (count($matches) < 3) return ''; // Không tạo TOC nếu ít hơn 3 headings

    $toc = '<div class="toc-wrapper">';
    $toc .= '<div class="toc-header" onclick="this.parentElement.classList.toggle(\'toc-collapsed\')">';
    $toc .= '<h4>📑 Mục lục bài viết</h4>';
    $toc .= '<span class="toc-toggle">▼</span>';
    $toc .= '</div>';
    $toc .= '<nav class="toc-body"><ul class="toc-list">';

    foreach ($matches as $i => $match) {
        $level = $match[1];
        $text  = strip_tags($match[2]);
        $id    = 'heading-' . sanitize_title($text) . '-' . $i;

        $indent = $level == '3' ? ' class="toc-sub"' : '';
        $toc .= "<li{$indent}><a href=\"#{$id}\">{$text}</a></li>";

        // Thêm id vào heading trong content
        $old_heading = $match[0];
        $new_heading = preg_replace(
            '/(<h[2-3])([^>]*>)/i',
            '$1 id="' . $id . '"$2',
            $old_heading
        );
        $content = str_replace($old_heading, $new_heading, $content);
    }

    $toc .= '</ul></nav></div>';

    return $toc;
}

/**
 * Hiển thị related posts (bài viết liên quan)
 * Gọi: fxt_related_posts(4)
 */
function fxt_related_posts($count = 4) {
    global $post;

    $categories = get_the_category($post->ID);
    if (empty($categories)) return;

    $args = [
        'category__in'   => [$categories[0]->term_id],
        'post__not_in'   => [$post->ID],
        'posts_per_page' => $count,
        'orderby'        => 'rand',
    ];

    $related = new WP_Query($args);

    if (!$related->have_posts()) return;
    ?>
    <section class="related-posts">
        <h3 class="section-title">Bài viết liên quan</h3>
        <div class="related-grid">
            <?php while ($related->have_posts()): $related->the_post(); ?>
            <article class="related-card">
                <?php if (has_post_thumbnail()): ?>
                <a href="<?php the_permalink(); ?>" class="related-card-image">
                    <?php the_post_thumbnail('fxt-card-small'); ?>
                </a>
                <?php endif; ?>
                <div class="related-card-content">
                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                    <span class="related-card-date"><?php echo get_the_date(); ?></span>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();
}

/**
 * Social share buttons
 * Gọi: fxt_share_buttons()
 */
function fxt_share_buttons() {
    $url   = urlencode(get_permalink());
    $title = urlencode(get_the_title());
    ?>
    <div class="share-buttons">
        <span class="share-label">Chia sẻ:</span>
        <a href="https://www.facebook.com/sharer.php?u=<?php echo $url; ?>" target="_blank" rel="noopener" class="share-btn share-fb" title="Chia sẻ Facebook">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
        </a>
        <a href="https://twitter.com/intent/tweet?url=<?php echo $url; ?>&text=<?php echo $title; ?>" target="_blank" rel="noopener" class="share-btn share-tw" title="Chia sẻ Twitter">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>
        </a>
        <a href="https://t.me/share/url?url=<?php echo $url; ?>&text=<?php echo $title; ?>" target="_blank" rel="noopener" class="share-btn share-tg" title="Chia sẻ Telegram">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M21.198 2.433a2.242 2.242 0 0 0-1.022.215l-8.609 3.33c-2.068.8-4.133 1.598-5.724 2.21a405.15 405.15 0 0 1-2.849 1.09c-.42.147-.99.332-1.473.901-.728.855.075 1.644.357 1.882l4.052 2.97 1.748 5.349c.283.874 1.047 1.239 1.757.98l.006-.002 3.185-1.458a.491.491 0 0 1 .482.027l4.08 2.96c.262.19.588.327.939.327 1.079 0 1.678-.952 1.816-1.602L22.753 3.74c.123-.582-.027-1.14-.578-1.307z"/></svg>
        </a>
    </div>
    <?php
}

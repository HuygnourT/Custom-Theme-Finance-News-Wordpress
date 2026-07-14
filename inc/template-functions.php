<?php
/**
 * Template Functions v2 - Tất cả text lấy từ Customizer
 * @package FXTradingToday
 */
if (!defined('ABSPATH')) exit;

/**
 * Parse chuỗi "Label|URL" thành mảng ['label'=>..,'url'=>..]
 * Nếu không có "|" hoặc URL để trống → dùng $default_url (cho phép fallback dynamic link).
 */
function fxt_parse_label_url($raw, $default_label = '', $default_url = '#') {
    $raw = trim((string) $raw);
    if ($raw === '') {
        return ['label' => $default_label, 'url' => $default_url];
    }

    $parts = array_map('trim', explode('|', $raw, 2));
    $label = $parts[0] !== '' ? $parts[0] : $default_label;
    $url   = (isset($parts[1]) && $parts[1] !== '') ? $parts[1] : $default_url;

    return ['label' => $label, 'url' => $url];
}

/**
 * Parse danh sách Statistics dạng text nhiều dòng thành mảng.
 * Mỗi dòng: "#Nhãn | Giá trị" — số dòng = số cột hiển thị.
 */
function fxt_parse_stat_list($raw_text) {
    $lines = preg_split('/\r\n|\r|\n/', (string) $raw_text);
    $stats = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        $line  = ltrim($line, '# ');
        $parts = array_map('trim', explode('|', $line, 2));
        $label = $parts[0] ?? '';
        $value = $parts[1] ?? '';

        if ($label === '' && $value === '') continue;

        $stats[] = ['label' => $label, 'value' => $value];
    }

    return $stats;
}

/**
 * Nội dung mặc định cho ô Statistics (Customizer textarea)
 */
function fxt_default_hero_stats_text() {
    return "#Brokers Reviewed | 15+\n"
        . "#Educational Articles | 200+\n"
        . "#Monthly Readers | 50K+";
}

/**
 * Parse cấu trúc Accordion dạng text thành mảng item.
 * # = Tiêu đề accordion | > = Nội dung | * Label|URL = nút CTA (tùy chọn)
 */
function fxt_parse_accordion_text($raw_text) {
    $lines   = preg_split('/\r\n|\r|\n/', (string) $raw_text);
    $items   = [];
    $current = null;

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        if ($line[0] === '#') {
            if ($current !== null) $items[] = $current;
            $current = [
                'title'     => trim(ltrim($line, '# ')),
                'content'   => '',
                'cta_label' => '',
                'cta_url'   => '',
            ];
        } elseif ($line[0] === '>') {
            if ($current === null) continue;
            $text = trim(ltrim($line, '> '));
            $current['content'] = trim(trim($current['content'] . ' ' . $text));
        } elseif ($line[0] === '*') {
            if ($current === null) continue;
            $cta_raw = trim(ltrim($line, '* '));
            $parts   = array_map('trim', explode('|', $cta_raw, 2));
            $current['cta_label'] = $parts[0] ?? '';
            $current['cta_url']   = $parts[1] ?? '';
        }
    }

    if ($current !== null) $items[] = $current;

    return $items;
}

/**
 * Render Accordion (dùng <details>/<summary> — không cần JS)
 */
function fxt_render_about_accordion($items) {
    if (empty($items)) return;
    echo '<div class="about-accordion">';
    foreach ($items as $i => $item) {
        if ($item['title'] === '') continue;
        echo '<details class="accordion-item"' . ($i === 0 ? ' open' : '') . '>';
        echo '<summary class="accordion-title">' . esc_html($item['title']) . '</summary>';
        echo '<div class="accordion-body">';
        if ($item['content'] !== '') {
            echo '<p>' . esc_html($item['content']) . '</p>';
        }
        if (!empty($item['cta_label']) && !empty($item['cta_url'])) {
            echo '<a href="' . esc_url($item['cta_url']) . '" class="accordion-cta">' . esc_html($item['cta_label']) . ' <span class="accordion-cta-arrow">→</span></a>';
        }
        echo '</div></details>';
    }
    echo '</div>';
}

/**
 * Nội dung mặc định cho ô Accordion (Customizer textarea)
 */
function fxt_default_about_accordion_text() {
    return "#Why Traders Trust Us\n"
        . ">We review every broker firsthand — real accounts, real spreads, real withdrawal tests — before publishing any rating.\n"
        . "* See Our Methodology|/about-us/\n"
        . "#How We Choose Brokers\n"
        . ">Regulation, trading costs, platform reliability, and customer support are scored using the same checklist for every broker.\n"
        . "* View Broker Reviews|/broker-reviews/";
}

/**
 * Reading time - text từ Customizer
 */
// Fix lại cho đọc được các custom field
function fxt_reading_time($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();

    $post_type = get_post_type($post_id);
    $content   = get_post_field('post_content', $post_id);

    // Gom thêm nội dung từ các custom field, tùy theo loại post
    $extra = [];

    if ($post_type === 'broker') {
        $extra[] = get_post_meta($post_id, '_fxt_pros', true);
        $extra[] = get_post_meta($post_id, '_fxt_cons', true);

        $sections = get_post_meta($post_id, '_fxt_broker_sections', true);
        if (is_array($sections)) {
            foreach ($sections as $sec) {
                $extra[] = $sec['content']         ?? '';
                $extra[] = $sec['pros']             ?? '';
                $extra[] = $sec['cons']              ?? '';
                $extra[] = $sec['collapse_detail']  ?? '';
            }
        }
    }

    if (in_array($post_type, ['broker_post', 'generic_post'], true)) {
        $extra[] = get_post_meta($post_id, '_fxt_sub_pros', true);
        $extra[] = get_post_meta($post_id, '_fxt_sub_cons', true);
        $extra[] = get_post_meta($post_id, '_fxt_sub_intro_text', true);
        $extra[] = get_post_meta($post_id, '_fxt_sub_outro_text', true);

        $sub_sections = get_post_meta($post_id, '_fxt_sub_sections', true);
        if (is_array($sub_sections)) {
            foreach ($sub_sections as $sec) {
                $extra[] = $sec['content']         ?? '';
                $extra[] = $sec['pros']             ?? '';
                $extra[] = $sec['cons']              ?? '';
                $extra[] = $sec['collapse_detail']  ?? '';
            }
        }
    }

    $full_text  = $content . ' ' . implode(' ', $extra);
    $word_count = str_word_count(strip_tags($full_text));
    $minutes    = max(1, ceil($word_count / 200));
    $template   = get_theme_mod('fxt_label_reading_time', '{min} min read');
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
 * Post meta - hiển thị thông tin bài viết
 * *** ĐÂY LÀ FUNCTION BỊ THIẾU - gây "critical error" trên single post ***
 */
function fxt_post_meta() {
    $categories = get_the_category();
    ?>
    <div class="post-meta">
        <?php if ($categories): ?>
        <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>" class="post-cat-link"><?php echo esc_html($categories[0]->name); ?></a>
        <?php endif; ?>
        <span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <?php echo get_the_date(); ?>
        </span>
        <span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?php the_author(); ?>
        </span>
        <span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <?php echo esc_html(fxt_reading_time()); ?>
        </span>
    </div>
    <?php
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

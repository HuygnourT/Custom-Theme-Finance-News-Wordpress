<?php
/**
 * Single Template - Trang riêng của 1 Tác giả (bio đầy đủ + bài đã viết)
 * @package FXTradingToday
 */
get_header();

if (have_posts()): the_post();
    $author_id  = get_the_ID();
    $job_title  = get_post_meta($author_id, '_fxt_author_job_title', true);
    $avatar     = fxt_get_author_avatar_url($author_id, 'medium');

    // Tìm mọi Broker + Broker Post có gắn tác giả này
    $authored = get_posts([
        'post_type'      => ['broker', 'broker_post'],
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => [
            [
                'key'     => '_fxt_post_authors',
                'value'   => '"' . $author_id . '"',
                'compare' => 'LIKE',
            ],
        ],
    ]);
?>

<div class="container">

    <?php fxt_breadcrumbs(); ?>

    <div class="author-profile-header">
        <span class="author-profile-avatar">
            <?php if ($avatar): ?>
                <img src="<?php echo esc_url($avatar); ?>" alt="<?php the_title_attribute(); ?>" width="120" height="120">
            <?php else: ?>
                <span class="author-initial"><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
            <?php endif; ?>
        </span>
        <div>
            <h1 class="author-profile-name"><?php the_title(); ?></h1>
            <?php if ($job_title): ?><p class="author-profile-title"><?php echo esc_html($job_title); ?></p><?php endif; ?>
        </div>
    </div>

    <?php if (get_the_content()): ?>
    <div class="author-profile-bio entry-content"><?php the_content(); ?></div>
    <?php endif; ?>

    <?php if (!empty($authored)): ?>
    <h2 class="author-profile-section-title"><?php echo esc_html(sprintf('Articles by %s', get_the_title())); ?></h2>
    <div class="author-articles-grid">
        <?php foreach ($authored as $p):
            $is_broker = $p->post_type === 'broker';
        ?>
        <a href="<?php echo esc_url(get_permalink($p)); ?>" class="author-article-card">
            <?php if (has_post_thumbnail($p)): ?>
                <span class="author-article-image"><?php echo get_the_post_thumbnail($p, 'fxt-card'); ?></span>
            <?php elseif ($is_broker): ?>
                <span class="author-article-image"><?php echo fxt_get_broker_icon_html($p->ID); ?></span>
            <?php endif; ?>
            <span class="author-article-title"><?php echo esc_html($is_broker ? fxt_get_broker_name($p->ID) : get_the_title($p)); ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<?php endif; get_footer(); ?>

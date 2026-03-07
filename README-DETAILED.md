# FX Trading Today v2 — Hướng Dẫn Chi Tiết Từng File

> **Đối tượng:** Developer đã có kinh nghiệm (Node.js, Python, Java...) nhưng mới chuyển sang PHP / WordPress.
> Mỗi file sẽ được giải thích: **mục đích → cách hoạt động → so sánh với ngôn ngữ quen thuộc → code quan trọng**.

---

## Mục Lục

1. [Kiến thức nền tảng PHP & WordPress](#1-kiến-thức-nền-tảng-php--wordpress)
2. [Entry Point: functions.php](#2-entry-point-functionsphp)
3. [Layout Files: header.php & footer.php](#3-layout-files-headerphp--footerphp)
4. [Template Files (Routing)](#4-template-files-routing)
5. [Thư mục inc/ — Business Logic](#5-thư-mục-inc--business-logic)
6. [Thư mục template-parts/ — Components](#6-thư-mục-template-parts--components)
7. [Thư mục page-templates/](#7-thư-mục-page-templates)
8. [Thư mục assets/js/](#8-thư-mục-assetsjs)
9. [style.css — Stylesheet chính](#9-stylecss--stylesheet-chính)

---

## 1. Kiến thức nền tảng PHP & WordPress

### 1.1. PHP cơ bản cho developer đã biết ngôn ngữ khác

```php
// PHP chạy trong cặp tag <?php ... ?>
// Ngoài cặp tag này là HTML thuần — browser nhận được HTML, không thấy PHP

<?php
// Biến bắt đầu bằng $
$name = 'Timo';
$age = 25;

// Array giống object trong JS
$config = [
    'key1' => 'value1',    // Tương đương { key1: 'value1' } trong JS
    'key2' => 'value2',
];

// Function
function greet($name) {
    return 'Hello ' . $name;  // Nối string dùng dấu chấm (.) thay vì +
}

// Arrow function (PHP 7.4+) — nhưng WordPress hay dùng anonymous function hơn
$double = fn($x) => $x * 2;

// Anonymous function (dùng nhiều trong WP)
add_action('init', function () {
    // Code chạy khi WordPress init
});
?>
```

**Khác biệt quan trọng so với JS/Python:**

| Đặc điểm | JavaScript | PHP |
|---|---|---|
| Biến | `let name` | `$name` (luôn có `$`) |
| Nối string | `+` hoặc template literal | `.` (dấu chấm) |
| Array access | `obj.key` hoặc `obj['key']` | `$arr['key']` (luôn dùng `[]`) |
| Print/echo | `console.log()` | `echo` hoặc `print` |
| Null check | `if (x)` | `if (!empty($x))` |
| Import | `require('./file')` | `require_once __DIR__ . '/file.php'` |
| Class method | `this.method()` | `$this->method()` (dùng `->`) |

### 1.2. WordPress hoạt động thế nào?

Hình dung WordPress như một **framework MVC** nhưng convention-based thay vì configuration-based:

```
User request → WordPress Core (router) → Chọn template file → Render HTML
                    ↑
              functions.php (chạy LUÔN, giống app.js)
```

**Hooks — Hệ thống event của WordPress:**

WordPress dùng 2 loại hook:

```php
// ACTION = "Khi event X xảy ra, chạy function này"
// Giống: eventEmitter.on('event', callback) trong Node.js
add_action('init', function () {
    // Chạy khi WordPress khởi tạo xong
});

add_action('wp_head', function () {
    // Chạy khi WordPress render <head> — dùng để thêm meta tags
    echo '<meta name="author" content="Timo">';
});

// FILTER = "Lấy data, biến đổi, trả về data mới"
// Giống: array.map() hoặc pipe/middleware trong Express
add_filter('the_title', function ($title) {
    return strtoupper($title);  // Biến tất cả tiêu đề thành chữ hoa
});

// Filter có thể nhận thêm priority (thứ tự chạy) và số argument
add_filter('excerpt_length', function () {
    return 25;  // Giới hạn excerpt còn 25 từ
});
```

**Template Hierarchy — Router tự động:**

WordPress KHÔNG cần bạn viết routing. Nó tự chọn file PHP dựa vào URL:

```
URL                         → File được gọi        → Tương đương Express
─────────────────────────────────────────────────────────────────────────
yoursite.com/               → front-page.php       → app.get('/', handler)
yoursite.com/blog/          → index.php            → app.get('/blog', handler)
yoursite.com/bai-viet-abc/  → single.php           → app.get('/post/:slug', handler)
yoursite.com/broker/exness/ → single-broker.php    → app.get('/broker/:slug', handler)
yoursite.com/about-us/      → page.php             → app.get('/page/:slug', handler)
yoursite.com/category/forex/→ archive.php          → app.get('/category/:slug', handler)
yoursite.com/?s=keyword     → search.php           → app.get('/search', handler)
yoursite.com/xyz-not-found  → 404.php              → app.use(notFoundHandler)
```

Quy tắc đặt tên file quyết định routing — đây là "convention over configuration".

### 1.3. Customizer — Hệ thống config của theme

Thay vì `.env` file hay config JSON, WordPress lưu settings trong database thông qua **Customizer** (Appearance → Customize trong Admin).

```php
// ĐĂNG KÝ setting (trong customizer.php)
$wp_customize->add_setting('fxt_hero_title', [
    'default' => 'Welcome to FX Trading Today',
    'sanitize_callback' => 'sanitize_text_field',  // XSS protection
]);

// ĐỌC setting (trong bất kỳ template nào)
$title = get_theme_mod('fxt_hero_title', 'Giá trị mặc định nếu chưa set');

// Tương đương trong Node.js:
// const title = process.env.HERO_TITLE || 'Default value';
```

---

## 2. Entry Point: `functions.php`

### Mục đích
Đây là file **chạy đầu tiên** khi WordPress load theme — tương đương `index.js` hoặc `app.js` trong Node.js. Nó không render HTML nào cả, chỉ **đăng ký và cấu hình**.

### Code giải thích

```php
<?php
// Bảo vệ: Không cho truy cập trực tiếp qua URL
// Nếu ai đó gõ yoursite.com/wp-content/themes/fxtradingtoday/functions.php
// → WP sẽ đặt ABSPATH, nhưng nếu gọi trực tiếp thì ABSPATH không tồn tại → exit
if (!defined('ABSPATH')) exit;

// Định nghĩa constants — giống const trong JS nhưng là global
// FXT_VERSION: Dùng để cache-bust CSS/JS khi deploy version mới
define('FXT_VERSION', '2.0.0');

// FXT_DIR: Đường dẫn tuyệt đối trên server (filesystem path)
// Ví dụ: /var/www/html/wp-content/themes/fxtradingtoday-v2
// Dùng khi require_once file PHP
define('FXT_DIR', get_template_directory());

// FXT_URI: URL công khai (web-accessible URL)
// Ví dụ: https://fxtradingtoday.com/wp-content/themes/fxtradingtoday-v2
// Dùng khi load CSS/JS trong <link> hoặc <script>
define('FXT_URI', get_template_directory_uri());

// Load tất cả modules — thứ tự QUAN TRỌNG vì file sau có thể phụ thuộc file trước
require_once FXT_DIR . '/inc/theme-setup.php';       // 1. Đăng ký features (menu, image sizes)
require_once FXT_DIR . '/inc/enqueue.php';            // 2. Load CSS/JS
require_once FXT_DIR . '/inc/custom-post-types.php';  // 3. Tạo "model" Broker
require_once FXT_DIR . '/inc/meta-boxes.php';         // 4. Form nhập liệu Broker + save logic
require_once FXT_DIR . '/inc/customizer.php';         // 5. Đăng ký config panel
require_once FXT_DIR . '/inc/seo-helpers.php';        // 6. Schema markup, breadcrumbs, OG tags
require_once FXT_DIR . '/inc/template-functions.php'; // 7. Helper functions cho template
require_once FXT_DIR . '/inc/demo-import.php';        // 8. Import nội dung mẫu
```

### So sánh với Node.js

```javascript
// Node.js equivalent
const express = require('express');
const app = express();

const themeSetup = require('./inc/theme-setup');      // register features
const enqueue = require('./inc/enqueue');              // static assets
const models = require('./inc/custom-post-types');     // database models
const metaBoxes = require('./inc/meta-boxes');         // admin forms
const customizer = require('./inc/customizer');        // config panel
const seo = require('./inc/seo-helpers');              // SEO middleware
const helpers = require('./inc/template-functions');   // utility functions
const demo = require('./inc/demo-import');             // seed data
```

### Tại sao dùng `require_once` thay vì `require`?

`require_once` đảm bảo file chỉ load **1 lần** dù được gọi nhiều lần. Nếu 2 file cùng require `customizer.php`, file thứ 2 sẽ bị skip → tránh lỗi "Cannot redeclare function".

---

## 3. Layout Files: `header.php` & `footer.php`

### Khái niệm

Mỗi trang WordPress đều có cấu trúc:

```
header.php  →  [template content]  →  footer.php
```

Giống layout/wrapper trong Express + EJS:

```ejs
<%- include('header') %>
  <h1>Page content here</h1>
<%- include('footer') %>
```

Hoặc trong React:

```jsx
<Layout>
  <PageContent />
</Layout>
```

### 3.1. `header.php` — Chi tiết

```php
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<!-- language_attributes() output: lang="en-US" hoặc lang="vi"
     WP tự detect từ Settings → General → Site Language -->

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <!-- bloginfo('charset') → thường là 'UTF-8' -->

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php wp_head(); ?>
    <!-- ĐÂY LÀ HOOK QUAN TRỌNG NHẤT trong <head>
         wp_head() trigger action 'wp_head' → tất cả code đã đăng ký bằng
         add_action('wp_head', ...) sẽ chạy ở đây.

         Nó output:
         - <link> CSS files (từ enqueue.php)
         - <script> JS files
         - <title> tag (vì ta đăng ký 'title-tag' support)
         - Schema JSON-LD (từ seo-helpers.php)
         - Open Graph meta tags
         - Favicon
         - Và mọi thứ plugin/theme thêm vào

         KHÔNG BAO GIỜ XÓA wp_head() — nhiều plugin sẽ hỏng -->
</head>

<body <?php body_class(); ?>>
<!-- body_class() thêm CSS classes tự động vào <body>
     Ví dụ output: class="home page-template-default logged-in admin-bar"
     Rất hữu ích để CSS target theo từng loại trang -->

<?php wp_body_open(); ?>
<!-- Hook chạy ngay sau <body> mở — dùng cho Google Tag Manager,
     analytics scripts, hoặc bất kỳ code nào cần ở đầu body -->

<header class="site-header" id="site-header">
    <div class="container header-inner">

        <!-- LOGO SECTION -->
        <div class="site-logo">
            <?php if (has_custom_logo()): the_custom_logo();
            // has_custom_logo(): Kiểm tra user đã upload logo trong Customizer chưa
            // the_custom_logo(): Output <a><img src="logo.png"></a>
            else: ?>
            <!-- Fallback: Nếu chưa upload logo, hiện text -->
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-title-link">
                <!-- esc_url(): Sanitize URL — chống XSS injection
                     home_url('/'): Trả về URL trang chủ, ví dụ https://fxtradingtoday.com/ -->
                <span class="site-title-fx">FX</span>
                <span class="site-title-text">Trading Today</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- NAVIGATION MENU -->
        <nav class="main-nav" id="main-nav">
            <?php wp_nav_menu([
                'theme_location' => 'primary',
                // 'primary' = tên đã đăng ký trong theme-setup.php
                // WP sẽ lấy menu được gán vào vị trí 'primary' trong Admin

                'container'    => false,
                // Mặc định WP wrap menu trong <div>, false = bỏ div wrapper

                'menu_class'   => 'nav-menu',
                // CSS class cho <ul> element

                'fallback_cb'  => false,
                // Nếu chưa tạo menu → không hiện gì (thay vì hiện menu mặc định)

                'depth'        => 2,
                // Tối đa 2 cấp: menu chính + 1 cấp dropdown
            ]); ?>
        </nav>
        <!-- wp_nav_menu() output HTML giống:
             <ul class="nav-menu">
               <li class="menu-item current-menu-item"><a href="/">Trang chủ</a></li>
               <li class="menu-item menu-item-has-children">
                 <a href="/brokers/">Broker</a>
                 <ul class="sub-menu">
                   <li><a href="/broker/exness/">Exness</a></li>
                 </ul>
               </li>
             </ul>
        -->

        <!-- HEADER ACTIONS: Search + CTA + Mobile menu -->
        <div class="header-actions">
            <!-- Nút search toggle -->
            <button class="search-toggle" id="search-toggle" aria-label="Search">
                <!-- SVG icon inline — không cần icon library -->
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </button>

            <!-- CTA Button (chỉ hiện nếu có affiliate link) -->
            <?php
            $cta_link = get_theme_mod('fxt_default_affiliate_link', '');
            $cta_text = get_theme_mod('fxt_cta_text', 'Open Account');
            if ($cta_link): ?>
            <a href="<?php echo esc_url($cta_link); ?>"
               class="btn btn-cta btn-sm header-cta"
               target="_blank"
               rel="noopener nofollow">
                <!-- noopener: Bảo mật — tab mới không access được window.opener
                     nofollow: SEO — báo Google không follow link affiliate -->
                <?php echo esc_html($cta_text); ?>
                <!-- esc_html(): Sanitize output HTML — chống XSS
                     Biến <script>alert('hack')</script> thành text thuần -->
            </a>
            <?php endif; ?>

            <!-- Mobile menu toggle (ẩn trên desktop, hiện trên mobile) -->
            <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Menu">
                <span class="hamburger"></span>
                <!-- CSS tạo icon ☰ từ ::before và ::after pseudo-elements -->
            </button>
        </div>
    </div>

    <!-- SEARCH OVERLAY (ẩn mặc định, JS toggle class 'active') -->
    <div class="search-overlay" id="search-overlay">
        <div class="container">
            <form role="search" method="get" class="search-form"
                  action="<?php echo esc_url(home_url('/')); ?>">
                <!-- method="get": Search dùng GET → URL: ?s=keyword
                     action=home_url: Submit về trang chủ, WP tự route đến search.php -->
                <input type="search" class="search-input" name="s"
                       placeholder="<?php echo esc_attr(get_theme_mod(
                           'fxt_label_search_placeholder',
                           'Search articles, brokers...'
                       )); ?>"
                       value="<?php echo get_search_query(); ?>">
                       <!-- get_search_query(): Giữ lại keyword đã tìm trong ô input -->
                <button type="submit" class="search-submit">
                    <?php echo esc_html(get_theme_mod('fxt_label_search_btn', 'Search')); ?>
                </button>
            </form>
        </div>
    </div>
</header>

<!-- MOBILE MENU OVERLAY -->
<div class="mobile-menu-overlay" id="mobile-menu-overlay">
    <div class="mobile-menu-inner">
        <div class="mobile-menu-header">
            <span class="site-title-fx">FX</span>
            <span class="site-title-text">Trading Today</span>
            <button class="mobile-menu-close" id="mobile-menu-close">✕</button>
        </div>
        <?php wp_nav_menu([
            'theme_location' => 'mobile',
            'container'      => false,
            'menu_class'     => 'mobile-nav-menu',
            'fallback_cb'    => function () {
                // Nếu chưa tạo menu mobile riêng → dùng menu primary
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'mobile-nav-menu',
                ]);
            },
            'depth' => 2,
        ]); ?>
    </div>
</div>

<!-- MỞ <main> — sẽ được đóng trong footer.php -->
<main class="site-main" id="main-content">
```

### 3.2. `footer.php` — Chi tiết

```php
</main>
<!-- Đóng <main> đã mở trong header.php -->

<footer class="site-footer">
    <!-- FOOTER TOP: 3 cột grid -->
    <div class="footer-top">
        <div class="container footer-grid">
            <!-- CỘT 1: Logo + About + Social -->
            <div>
                <div class="footer-logo">
                    <span class="site-title-fx">FX</span>
                    <span class="site-title-text">Trading Today</span>
                </div>
                <p class="footer-about">
                    <?php echo esc_html(get_theme_mod('fxt_footer_about', 'Default about text...')); ?>
                </p>
                <div class="footer-social">
                    <?php
                    // Duyệt qua mảng social networks
                    // Key = tên mạng, Value = icon character
                    foreach (['facebook'=>'f', 'telegram'=>'✈', 'youtube'=>'▶', 'tiktok'=>'♪'] as $k => $icon):
                        $url = get_theme_mod("fxt_social_{$k}");
                        // Chỉ hiện icon nếu user đã nhập URL trong Customizer
                        if ($url):
                    ?>
                    <a href="<?php echo esc_url($url); ?>" class="social-link"
                       target="_blank" rel="noopener"><?php echo $icon; ?></a>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>

            <!-- CỘT 2: Menu hoặc Categories -->
            <div>
                <?php if (has_nav_menu('footer')):
                    // Ưu tiên 1: Nếu có menu footer → dùng menu
                ?>
                <h4 class="footer-widget-title">
                    <?php echo esc_html(get_theme_mod('fxt_footer_col2_title', 'Quick Links')); ?>
                </h4>
                <?php wp_nav_menu([
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'footer-links',
                    'depth'          => 1,  // Footer menu không cần dropdown
                ]);

                elseif (is_active_sidebar('footer-col-2')):
                    // Ưu tiên 2: Nếu có widget trong footer col 2
                    dynamic_sidebar('footer-col-2');
                    // dynamic_sidebar() render tất cả widgets đã kéo vào area này
                    // trong Admin → Appearance → Widgets

                else:
                    // Ưu tiên 3: Fallback — hiện danh sách categories
                ?>
                <h4 class="footer-widget-title">
                    <?php echo esc_html(get_theme_mod('fxt_footer_col2_title', 'Categories')); ?>
                </h4>
                <ul class="footer-links">
                    <?php wp_list_categories([
                        'title_li'   => '',      // Không hiện tiêu đề
                        'show_count' => 0,       // Không hiện số bài
                        'number'     => 6,       // Tối đa 6 categories
                    ]); ?>
                </ul>
                <?php endif; ?>
            </div>

            <!-- CỘT 3: Links tĩnh (About, Contact, Privacy...) -->
            <div>
                <?php if (is_active_sidebar('footer-col-3')):
                    dynamic_sidebar('footer-col-3');
                else: ?>
                <h4 class="footer-widget-title">
                    <?php echo esc_html(get_theme_mod('fxt_footer_col3_title', 'More information')); ?>
                </h4>
                <ul class="footer-links">
                    <!-- Mỗi link: text + slug đều lấy từ Customizer
                         Cho phép user đổi cả tên link và URL mà không sửa code -->
                    <li><a href="<?php echo esc_url(home_url('/' . get_theme_mod('fxt_footer_about_slug', 'about-us') . '/')); ?>">
                        <?php echo esc_html(get_theme_mod('fxt_footer_link_about', 'About Us')); ?>
                    </a></li>
                    <!-- ... tương tự cho Contact, Disclaimer, Privacy ... -->
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- DISCLAIMER -->
    <div class="footer-disclaimer">
        <div class="container">
            <p class="disclaimer-text">
                <?php echo wp_kses_post(get_theme_mod('fxt_disclaimer', '⚠️ Forex trading...')); ?>
                <!-- wp_kses_post(): Cho phép HTML an toàn (bold, link, em...)
                     nhưng chặn <script>, <iframe> — an toàn hơn esc_html()
                     mà vẫn cho phép formatting cơ bản -->
            </p>
        </div>
    </div>

    <!-- COPYRIGHT -->
    <div class="footer-bottom">
        <div class="container">
            <p class="copyright">
                <?php echo esc_html(get_theme_mod('fxt_copyright', '© ' . date('Y') . ' FX Trading Today.')); ?>
            </p>
        </div>
    </div>
</footer>

<!-- Back to top button (JS control show/hide) -->
<button class="back-to-top" id="back-to-top">↑</button>

<?php wp_footer(); ?>
<!-- TƯƠNG TỰ wp_head() nhưng cho cuối body
     Output: JS files (defer), analytics, plugin scripts
     KHÔNG BAO GIỜ XÓA -->

</body>
</html>
```

**Tóm tắt flow:** `header.php` mở HTML + `<head>` + `<header>` + `<main>`, rồi template content nằm giữa, rồi `footer.php` đóng `</main>` + `<footer>` + `</body>`.

---

## 4. Template Files (Routing)

### 4.1. `front-page.php` — Trang chủ

**Khi nào chạy:** Khi user truy cập URL gốc (`yoursite.com/`).

**Cấu trúc logic:**

```
┌─ Hero Section (badge, title, description, 2 buttons, 3 stats)
│     ↳ Tất cả text lấy từ Customizer
│
├─ Top Brokers Section
│     ↳ Query: WP_Query lấy 5 broker, sort theo rating cao → thấp
│     ↳ Mỗi broker render: rank, logo, name, star rating, specs, buttons
│
├─ Latest Articles Section
│     ↳ Query: 3 bài mới nhất
│     ↳ Dùng template-part: content-card.php
│
├─ Education Section
│     ↳ Query: 2 bài từ category "education" (slug configurable)
│     ↳ Dùng template-part: content-card-horizontal.php
│
└─ CTA Section (Call to Action cuối trang)
      ↳ Text từ Customizer
```

**Code quan trọng — WP_Query:**

```php
// WP_Query = Cách chính để lấy data từ database trong WordPress
// Tương đương: Model.find({...}).sort({...}).limit(5) trong Mongoose

$brokers = new WP_Query([
    'post_type'      => 'broker',          // Chỉ lấy post type "broker"
    'posts_per_page' => 5,                  // Giới hạn 5 kết quả
    'meta_key'       => '_fxt_rating',      // Sort theo meta field rating
    'orderby'        => 'meta_value_num',   // Sort dạng số (không phải string)
    'order'          => 'DESC',             // Cao → thấp
]);

// The Loop — Pattern chuẩn để iterate qua kết quả
if ($brokers->have_posts()):              // Có kết quả không?
    while ($brokers->have_posts()):       // Còn bài tiếp theo không?
        $brokers->the_post();              // Set current post (giống iterator.next())

        // Bây giờ có thể dùng template functions:
        the_title();                       // Echo tên broker
        the_permalink();                   // Echo URL: /broker-review/exness/
        has_post_thumbnail();              // Có featured image không?
        the_post_thumbnail('fxt-broker-logo'); // Output <img> với size đã định nghĩa

        // Lấy custom meta data (data riêng của broker)
        $meta = fxt_get_broker_meta(get_the_ID());
        // $meta = ['rating' => '9.2', 'spread' => 'From 0.0 pips', ...]

    endwhile;
    wp_reset_postdata();  // QUAN TRỌNG: Reset global $post về trạng thái ban đầu
                          // Nếu quên → query tiếp theo sẽ bị lỗi data
endif;
```

**Hero section — Pattern xử lý accent text:**

```php
// User nhập trong Customizer: "{accent}Trusted{/accent} Forex Broker Reviews"
// Output HTML: <span class="text-accent">Trusted</span> Forex Broker Reviews

$title = get_theme_mod('fxt_hero_title', '{accent}Trusted{/accent} Forex Broker Reviews');
echo str_replace(
    ['{accent}', '{/accent}'],
    ['<span class="text-accent">', '</span>'],
    esc_html($title)
);
// esc_html() chạy trước str_replace() → an toàn vì custom tags không phải HTML thật
```

### 4.2. `single.php` — Chi tiết bài viết

**Khi nào chạy:** URL dạng `yoursite.com/bai-viet-abc/` (single post).

**Cấu trúc:**

```
┌─ Hero: breadcrumbs + title + post meta (category, date, author, reading time)
├─ Featured image
├─ Table of Contents (tự sinh từ headings H2, H3)
├─ Nội dung bài viết (the_content())
├─ Tags
├─ Share buttons (Facebook, Twitter, Telegram)
├─ Author box (avatar + bio)
└─ Related posts (4 bài cùng category)
```

**Code quan trọng:**

```php
<?php while (have_posts()): the_post(); ?>
// Đây là "The Loop" — dù single post chỉ có 1 bài
// nhưng vẫn phải dùng loop vì the_post() setup global variables

    <h1><?php the_title(); ?></h1>
    <?php fxt_post_meta(); ?>
    <!-- fxt_post_meta() = helper function tự viết trong template-functions.php
         Output: category link + date + author + reading time -->

    <?php the_content(); ?>
    <!-- the_content() = LẤY VÀ RENDER nội dung bài viết
         Nó cũng apply tất cả filters trên 'the_content' hook:
         - wpautop: Tự thêm <p> tags
         - do_shortcode: Xử lý [shortcode]
         - Oembeds: Biến YouTube URL thành embed -->

    <?php fxt_share_buttons(); ?>
    <?php fxt_related_posts(4); ?>

<?php endwhile; ?>
```

### 4.3. `single-broker.php` — Chi tiết broker review

**Khi nào chạy:** URL dạng `yoursite.com/broker-review/exness/`.

WordPress tự biết dùng file này vì tên file là `single-{post_type}.php`. Post type "broker" đã đăng ký trong `custom-post-types.php`.

**Cấu trúc phức tạp nhất trong theme:**

```
┌─ Broker Hero (dark background)
│   ├─ Breadcrumbs
│   ├─ Logo + Title + Excerpt
│   └─ Rating box + CTA button
│
├─ Horizontal Tab Navigation (sticky dưới header)
│   └─ Các tab từ Broker Sections meta data
│
├─ Content Area
│   ├─ Specs Table (Regulation, Spread, Leverage, Deposit, Platforms, Founded)
│   ├─ Global Pros/Cons
│   ├─ Inline CTA button
│   │
│   ├─ BROKER SECTIONS (loop) ─── Mỗi section có:
│   │   ├─ Section title (= tab title)
│   │   ├─ Section content (rich HTML from TinyMCE)
│   │   ├─ Per-section Pros/Cons (optional)
│   │   └─ Collapsible detail (optional, ẩn mặc định)
│   │
│   ├─ Main editor content (the_content())
│   ├─ Share buttons
│   └─ Bottom CTA box
│
└─ Sidebar
```

**Code quan trọng — Dynamic text replacement:**

```php
// Pattern {name} → thay bằng tên broker thật
$cta_text = get_theme_mod('fxt_broker_cta_ready', 'Are you ready to trade with {name}?');
echo esc_html(str_replace('{name}', get_the_title(), $cta_text));
// Output: "Are you ready to trade with Exness?"
```

**Broker Sections rendering:**

```php
$sections = fxt_get_broker_sections(get_the_ID());
// Trả về array of sections, mỗi section = [title, content, pros_arr, cons_arr, ...]

foreach ($sections as $i => $sec):
    if (empty($sec['title'])) continue;  // Skip section không có title
    $tab_id = 'broker-section-' . $i;     // ID cho scroll-to từ tab navigation
?>
    <div class="broker-section" id="<?php echo esc_attr($tab_id); ?>">
        <h2><?php echo esc_html($sec['title']); ?></h2>

        <!-- Content từ TinyMCE editor — đã lưu dạng HTML trong database -->
        <div class="entry-content">
            <?php echo apply_filters('the_content', $sec['content']); ?>
            <!-- apply_filters('the_content', ...) XỬ LÝ content giống the_content():
                 - Thêm <p> tags
                 - Xử lý shortcodes
                 - Nhưng KHÔNG phải the_content() vì data không phải từ main post -->
        </div>

        <!-- Collapsible detail: ẩn mặc định, JS toggle -->
        <?php if (!empty($sec['collapsible']) && !empty($sec['collapse_detail'])): ?>
        <div class="broker-section-collapsible">
            <div class="broker-section-detail" style="display:none;">
                <?php echo apply_filters('the_content', $sec['collapse_detail']); ?>
            </div>
            <button type="button" class="broker-toggle-detail"
                    data-show="<?php echo esc_attr($show_text); ?>"
                    data-hide="<?php echo esc_attr($hide_text); ?>">
                <?php echo esc_html($show_text); ?>
            </button>
        </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
```

### 4.4. `index.php` — Danh sách bài viết (Blog page)

**Khi nào chạy:** URL blog page hoặc khi không có template cụ thể hơn.

```php
<?php get_header(); ?>
<!-- Gọi header.php — tương đương include('header') -->

<div class="container layout-with-sidebar">
    <div class="content-area">
        <?php if (is_home() && !is_front_page()): ?>
            <!-- is_home(): Đang ở trang blog
                 !is_front_page(): KHÔNG phải trang chủ
                 Điều kiện này = "đang ở trang blog riêng biệt"
                 (vì WP cho phép trang chủ ≠ trang blog) -->
            <h1 class="page-title">
                <?php echo esc_html(get_theme_mod('fxt_label_latest_posts', 'Latest Articles')); ?>
            </h1>
        <?php endif; ?>

        <?php if (have_posts()): ?>
            <div class="posts-grid posts-grid-2">
                <?php while (have_posts()): the_post();
                    // Ở đây KHÔNG cần new WP_Query vì WordPress đã tự query
                    // dựa vào URL. Main query đã có sẵn data.
                    get_template_part('template-parts/content', 'card');
                    // Load file: template-parts/content-card.php
                    // Tương đương: <?php include('components/PostCard.php'); ?>
                endwhile; ?>
            </div>
            <?php fxt_pagination(); ?>
        <?php else:
            get_template_part('template-parts/content', 'none');
            // Load: template-parts/content-none.php (empty state)
        endif; ?>
    </div>

    <aside class="sidebar">
        <?php get_sidebar(); ?>
        <!-- Load sidebar.php -->
    </aside>
</div>

<?php get_footer(); ?>
<!-- Load footer.php -->
```

### 4.5. `archive.php` — Danh mục / Tag / Archive

**Khi nào chạy:** URL dạng `/category/forex/`, `/tag/scalping/`, hoặc `/2024/01/`.

Gần giống `index.php` nhưng có thêm archive header (tên danh mục, mô tả).

```php
<header class="archive-header">
    <h1 class="archive-title"><?php the_archive_title(); ?></h1>
    <!-- the_archive_title() tự output: "Category: Forex" hoặc "Tag: Scalping"
         WordPress tự biết đang ở archive nào -->

    <?php if (get_the_archive_description()): ?>
        <div class="archive-desc"><?php the_archive_description(); ?></div>
    <?php endif; ?>
</header>
```

### 4.6. `search.php` — Kết quả tìm kiếm

**Khi nào chạy:** URL `?s=keyword`.

```php
<h1><?php
    $search_title_tpl = get_theme_mod('fxt_label_search_results_title', 'Search results: "{query}"');
    echo esc_html(str_replace('{query}', get_search_query(), $search_title_tpl));
    // get_search_query() trả về keyword đã tìm
    // Output: Search results: "forex scalping"
?></h1>

<?php if (have_posts()): ?>
    <p><?php
        $count_tpl = get_theme_mod('fxt_label_search_count', 'Found {count} results');
        echo esc_html(str_replace('{count}', $wp_query->found_posts, $count_tpl));
        // $wp_query->found_posts = tổng số kết quả (global variable)
    ?></p>
    <!-- Render results giống index.php -->
<?php else: ?>
    <?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>
```

### 4.7. `page.php` — Trang tĩnh

**Khi nào chạy:** URL dạng `/about-us/`, `/contact/` (WordPress Page, không phải Post).

Đơn giản nhất — chỉ có breadcrumbs + title + content:

```php
<?php while (have_posts()): the_post(); ?>
<article class="single-page">
    <h1><?php the_title(); ?></h1>
    <div class="entry-content">
        <?php the_content(); ?>
    </div>
</article>
<?php endwhile; ?>
```

### 4.8. `404.php` — Trang lỗi

**Khi nào chạy:** URL không match bất kỳ route nào.

```php
<div class="page-404">
    <div class="error-code">404</div>
    <h2><?php echo esc_html(get_theme_mod('fxt_label_404_title', 'Page Not Found')); ?></h2>
    <p><?php echo esc_html(get_theme_mod('fxt_label_404_desc', 'The page you are looking for...')); ?></p>
    <a href="<?php echo home_url('/'); ?>" class="btn btn-primary">
        <?php echo esc_html(get_theme_mod('fxt_label_back_home', 'Back to Homepage')); ?>
    </a>
</div>
```

### 4.9. `sidebar.php` — Sidebar

**Được gọi bởi:** `get_sidebar()` trong các template files.

```php
<div class="sidebar-sticky">
    <!-- position: sticky → sidebar follow khi scroll -->

    <?php if (is_active_sidebar('main-sidebar')):
        // Nếu user đã kéo widgets vào sidebar trong Admin
        dynamic_sidebar('main-sidebar');
    else:
        // Fallback: Hiện default widgets bằng code
    ?>
        <!-- Widget 1: Search form -->
        <div class="sidebar-widget">
            <h3 class="widget-title">
                <?php echo esc_html(get_theme_mod('fxt_sidebar_search', '🔍 Search')); ?>
            </h3>
            <?php get_search_form(); ?>
            <!-- get_search_form() output HTML form tìm kiếm mặc định WP -->
        </div>

        <!-- Widget 2: Top Brokers (query riêng) -->
        <div class="sidebar-widget">
            <h3 class="widget-title">
                <?php echo esc_html(get_theme_mod('fxt_sidebar_brokers', '🏆 Top Broker')); ?>
            </h3>
            <?php
            $top = new WP_Query([
                'post_type'      => 'broker',
                'posts_per_page' => 5,
                'meta_key'       => '_fxt_rating',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
            ]);
            if ($top->have_posts()):
                while ($top->have_posts()): $top->the_post();
                    $r = get_post_meta(get_the_ID(), '_fxt_rating', true);
            ?>
                <a href="<?php the_permalink(); ?>" class="sidebar-broker-item">
                    <span><?php the_title(); ?></span>
                    <span class="sidebar-broker-rating"><?php echo esc_html($r); ?>/10</span>
                </a>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>

        <!-- Widget 3: Popular Articles -->
        <!-- Query sort theo comment_count — nhiều comment = popular -->
    <?php endif; ?>
</div>
```

---

## 5. Thư mục `inc/` — Business Logic

### 5.1. `inc/theme-setup.php` — Đăng ký features

**Mục đích:** Khai báo theme hỗ trợ những gì. Giống configuration file.

```php
add_action('after_setup_theme', function () {
    // ── Title Tag ──
    add_theme_support('title-tag');
    // WP tự tạo <title> tag thông minh:
    // Trang chủ: "FX Trading Today"
    // Bài viết: "Bài viết ABC - FX Trading Today"
    // Nếu không có dòng này, phải hardcode <title> trong header.php

    // ── Featured Image ──
    add_theme_support('post-thumbnails');
    // Cho phép mỗi post/page có 1 ảnh đại diện (featured image)
    // Tương đương: thêm field "coverImage" vào model

    // ── Custom Image Sizes ──
    add_image_size('fxt-card', 400, 250, true);
    // Khi user upload ảnh, WP TỰ ĐỘNG tạo thêm 1 bản resize 400x250
    // true = crop (cắt chính xác size), false = proportional resize
    // Dùng: the_post_thumbnail('fxt-card') → lấy bản 400x250

    add_image_size('fxt-hero', 1200, 500, true);     // Ảnh lớn cho bài viết
    add_image_size('fxt-broker-logo', 200, 80, false); // Logo broker (không crop)
    add_image_size('fxt-card-small', 120, 80, true);   // Thumbnail nhỏ sidebar

    // ── Register Menu Locations ──
    register_nav_menus([
        'primary' => 'Menu chính (Header)',
        'footer'  => 'Menu Footer',
        'mobile'  => 'Menu Mobile',
    ]);
    // Sau dòng này, Admin → Appearance → Menus sẽ có 3 vị trí để gán menu
    // Giống: app.set('views', ...) — khai báo nơi đặt views

    // ── HTML5 support ──
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption', 'style', 'script']);
    // WP output HTML5 thay vì XHTML cho các elements này

    // ── Custom Logo ──
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,  // Cho phép logo cao hơn/thấp hơn 60px
        'flex-width'  => true,  // Cho phép logo rộng hơn/hẹp hơn 200px
    ]);

    // ── Tắt block editor styles ──
    remove_theme_support('wp-block-styles');
    // WP 6.x thêm ~80KB CSS cho Gutenberg blocks — ta không dùng → tắt
});
```

**Widget Areas:**

```php
add_action('widgets_init', function () {
    register_sidebar([
        'name'          => 'Sidebar Chính',
        'id'            => 'main-sidebar',
        'description'   => 'Hiển thị bên phải bài viết',
        'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
    // Tạo 1 "khu vực" để user kéo-thả widgets trong Admin → Widgets
    // before/after: WP sẽ wrap mỗi widget trong HTML này
    // %1$s = widget ID, %2$s = widget CSS class

    // Tương tự cho: broker-sidebar, footer-col-1, footer-col-2, footer-col-3
});
```

**Performance optimizations:**

```php
// Tắt emoji script (mặc định WP load ~50KB JS để render emoji)
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
});

// Xóa meta tags không cần thiết
remove_action('wp_head', 'wp_generator');         // Ẩn WordPress version (bảo mật)
remove_action('wp_head', 'wlwmanifest_link');     // Windows Live Writer (không ai dùng)
remove_action('wp_head', 'rsd_link');             // Really Simple Discovery
remove_action('wp_head', 'wp_shortlink_wp_head'); // Short URL
```

### 5.2. `inc/enqueue.php` — Load CSS/JS

**Mục đích:** Quản lý tất cả CSS và JS files. Đây là file **quan trọng nhất** cho vấn đề giao diện.

**Tại sao không dùng `<link>` và `<script>` trực tiếp?**

WordPress dùng hệ thống `wp_enqueue_*` để:
1. **Quản lý dependencies** (load đúng thứ tự)
2. **Tránh duplicate** (2 plugins cùng load jQuery → chỉ load 1 lần)
3. **Cache busting** (thêm ?ver=2.0.0 vào URL)
4. **Conditional loading** (chỉ load broker-filter.js trên trang so sánh)

```php
add_action('wp_enqueue_scripts', function () {
    // ── Google Fonts ──
    wp_enqueue_style(
        'fxt-fonts',                          // Handle (ID duy nhất)
        'https://fonts.googleapis.com/....',  // URL
        [],                                    // Dependencies (không phụ thuộc gì)
        null                                   // Version (null = không thêm ?ver=)
    );

    // ── Main CSS ──
    wp_enqueue_style(
        'fxt-style',
        get_stylesheet_uri(),  // = URL đến style.css ở root theme
        ['fxt-fonts'],         // Phụ thuộc vào fonts → fonts load trước
        FXT_VERSION            // ?ver=2.0.0 → cache bust khi deploy mới
    );
    // Output: <link rel="stylesheet" href=".../style.css?ver=2.0.0">

    // ── Main JS ──
    wp_enqueue_script(
        'fxt-main',
        FXT_URI . '/assets/js/main.js',
        [],             // Không phụ thuộc gì (vanilla JS, no jQuery)
        FXT_VERSION,
        true            // true = load ở footer (trước </body>), không phải <head>
    );

    // ── Conditional: Chỉ load broker-filter.js trên trang cần ──
    if (is_page_template('page-templates/template-brokers.php')
        || is_post_type_archive('broker')) {
        wp_enqueue_script('fxt-broker-filter', FXT_URI . '/assets/js/broker-filter.js', [], FXT_VERSION, true);
    }

    // ── Chỉ load broker-sections.js trên single broker page ──
    if (is_singular('broker')) {
        wp_enqueue_script('fxt-broker-sections', FXT_URI . '/assets/js/broker-sections.js', [], FXT_VERSION, true);
    }
}, 10);  // Priority 10 = chạy ở thứ tự mặc định
```

**TẮT WP default styles (critical!):**

```php
add_action('wp_enqueue_scripts', function () {
    // WordPress 6.x thêm rất nhiều CSS mặc định cho Gutenberg
    // Các CSS này thêm margin, padding, max-width vào elements → PHÁ layout theme

    wp_dequeue_style('wp-block-library');       // Block editor CSS (~60KB)
    wp_deregister_style('wp-block-library');    // deregister = không cho load lại
    wp_dequeue_style('global-styles');          // Global styles inline CSS
    wp_deregister_style('global-styles');
    wp_dequeue_style('classic-theme-styles');   // Classic theme compat styles
    wp_deregister_style('classic-theme-styles');

    // ... thêm nhiều styles nữa
}, 200);  // Priority 200 = chạy SAU CÙNG → đảm bảo dequeue sau khi WP enqueue
```

**Tắt jQuery:**

```php
add_action('wp_enqueue_scripts', function () {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        // Theme dùng vanilla JS → không cần jQuery (~90KB)
        // CHỈ tắt ở frontend, giữ trong admin (vì admin cần)
    }
}, 20);
```

**Defer scripts (performance):**

```php
add_filter('script_loader_tag', function ($tag, $handle) {
    if (in_array($handle, ['fxt-main', 'fxt-broker-filter', 'fxt-broker-sections'])) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}, 10, 2);
// Filter 'script_loader_tag' cho phép modify HTML output của <script> tag
// Thêm defer → browser tải JS song song, không block rendering
// Input:  <script src="main.js"></script>
// Output: <script defer src="main.js"></script>
```

### 5.3. `inc/custom-post-types.php` — "Model" Broker

**Mục đích:** Tạo content type mới ngoài Post và Page mặc định.

**So sánh:**

```javascript
// Mongoose (Node.js)
const BrokerSchema = new Schema({
    name: { type: String, required: true },
    rating: Number,
    spread: String,
    // ...
});
const Broker = mongoose.model('Broker', BrokerSchema);

// Sau khi tạo: có thể Broker.find(), Broker.create()...
```

```php
// WordPress equivalent
add_action('init', function () {
    register_post_type('broker', [
        'labels' => [
            'name'          => 'Brokers',
            'singular_name' => 'Broker',
            'add_new'       => 'Thêm Broker',
            'edit_item'     => 'Sửa Broker',
            // Labels cho Admin UI — WP Admin tự tạo CRUD interface!
        ],

        'public'             => true,   // Hiển thị trên frontend + admin
        'publicly_queryable' => true,   // Có thể truy vấn URL: /broker/exness/
        'show_ui'            => true,   // Hiển thị trong WP Admin sidebar
        'show_in_rest'       => true,   // Hỗ trợ REST API (GET /wp-json/wp/v2/broker)
        'menu_icon' => 'dashicons-chart-area', // Icon trong admin sidebar

        'has_archive' => 'brokers',     // URL archive: /brokers/ (list tất cả)

        'rewrite' => [
            'slug'       => 'broker-review',  // URL pattern: /broker-review/exness/
            'with_front' => false,
        ],

        'supports' => [
            'title',          // Trường tên (input text)
            'editor',         // Trường nội dung (rich text editor)
            'thumbnail',      // Featured image
            'excerpt',        // Tóm tắt ngắn
            'custom-fields',  // Cho phép meta fields (rating, spread, v.v.)
            'revisions',      // Lịch sử chỉnh sửa
        ],
    ]);
});
```

**Sau khi đăng ký, WP Admin tự động có:**
- Menu "Brokers" trong sidebar admin
- Trang list tất cả brokers (giống Posts)
- Form tạo/sửa broker (title + editor + featured image)
- REST API: `GET /wp-json/wp/v2/broker`

**Taxonomy (phân loại):**

```php
register_taxonomy('broker_type', 'broker', [
    'hierarchical' => true,   // Giống Category (có parent/child)
    // false = giống Tag (flat list)
    'public'       => true,
    'show_admin_column' => true,  // Hiện cột "Loại Broker" trong admin list
    'rewrite' => ['slug' => 'broker-type'],
]);
// Tương đương: thêm enum field "type" vào Broker model
// Nhưng WP quản lý qua UI, không cần hardcode values
```

**Flush rewrite rules:**

```php
add_action('after_switch_theme', function () {
    flush_rewrite_rules();
});
// Khi activate theme, cần "flush" để WP cập nhật URL patterns
// Nếu không: URL /broker-review/exness/ sẽ 404
// Tương đương: restart server sau khi thay đổi routes
```

### 5.4. `inc/meta-boxes.php` — Form nhập liệu Broker

**Mục đích:** Thêm form fields tùy chỉnh vào trang editor broker trong Admin. Đây là file phức tạp nhất trong theme.

**Gồm 3 phần chính:**

#### Phần A: Meta Box cơ bản (thông tin broker)

```php
add_action('add_meta_boxes', function () {
    add_meta_box(
        'fxt_broker_details',              // ID
        'Information Broker',              // Title hiện trong admin
        'fxt_broker_meta_box_html',        // Function render HTML
        'broker',                          // Chỉ hiện trong post type "broker"
        'normal',                          // Vị trí: dưới editor
        'high'                             // Priority: hiện ở trên cùng
    );
});

// Tắt Gutenberg cho broker (dùng Classic Editor)
add_filter('use_block_editor_for_post_type', function ($use, $post_type) {
    if ($post_type === 'broker') return false;
    return $use;
}, 10, 2);

function fxt_broker_meta_box_html($post) {
    // $post = object chứa data broker đang edit

    // Security: Tạo nonce field (chống CSRF)
    wp_nonce_field('fxt_broker_meta', 'fxt_broker_meta_nonce');
    // Tương đương: CSRF token trong Express
    // Output: <input type="hidden" name="fxt_broker_meta_nonce" value="abc123">

    // Lấy data hiện tại từ database
    $fields = [
        'rating'  => get_post_meta($post->ID, '_fxt_rating', true),
        'spread'  => get_post_meta($post->ID, '_fxt_spread', true),
        // get_post_meta(post_id, meta_key, single)
        // single=true → trả về string
        // single=false → trả về array
        // Prefix _ (underscore) = ẩn khỏi Custom Fields UI mặc định
    ];

    // Render HTML form (giống JSX nhưng dùng PHP echo)
    ?>
    <div class="fxt-meta-grid">
        <div class="fxt-meta-field">
            <label for="fxt_rating">⭐ Rating (0–10)</label>
            <input type="number" id="fxt_rating" name="fxt_rating"
                   value="<?php echo esc_attr($fields['rating']); ?>"
                   min="0" max="10" step="0.1">
            <!-- esc_attr(): Sanitize cho attribute context
                 Khác esc_html() — esc_attr() handle quotes đúng cách -->
        </div>
        <!-- ... tương tự cho spread, leverage, deposit, regulation, v.v. ... -->
    </div>

    <!-- Pros/Cons: textarea, mỗi dòng = 1 item -->
    <textarea name="fxt_pros" rows="5"
              placeholder="Low spread&#10;Fast withdrawal">
        <?php echo esc_textarea($fields['pros']); ?>
        <!-- esc_textarea(): Sanitize cho context textarea
             &#10; = newline character trong HTML attribute -->
    </textarea>
    <?php
}
```

#### Phần B: Broker Sections Meta Box (phức tạp)

Mỗi section có:
- Tab title
- Rich text content (TinyMCE editor)
- Optional pros/cons
- Optional collapsible detail (cũng TinyMCE editor)
- Show/hide text customizable

**TinyMCE Integration:**

```javascript
// KHÔNG dùng wp_editor() (function PHP render editor)
// vì nó có bug khi tạo editor dynamically (thêm section mới bằng JS)

// Thay vào đó: Render <textarea> thuần, rồi init TinyMCE bằng JS
function initTinyMCE(editorId, height) {
    // Remove instance cũ nếu có (khi collapse/expand section)
    var existing = tinymce.get(editorId);
    if (existing) { existing.save(); existing.remove(); }

    tinymce.init({
        selector: '#' + editorId,
        theme: 'modern',
        plugins: 'lists link image media wpautoresize ...',
        toolbar1: 'formatselect bold italic bullist numlist blockquote ...',
        height: height || 250,
        setup: function(editor) {
            // Save content ngược lại textarea khi thay đổi
            editor.on('change keyup', function() { editor.save(); });

            // FIX: Khi editor init xong, ép set content từ textarea
            // (workaround cho bug Visual tab trống)
            editor.on('init', function() {
                setTimeout(function() {
                    editor.setContent(originalContent);
                }, 50);
            });
        }
    });
}
```

#### Phần C: Save Meta Data

```php
add_action('save_post_broker', function ($post_id) {
    // Hook 'save_post_broker' chỉ chạy khi save post type "broker"
    // Tương đương: app.post('/api/broker/:id', handler)

    // ── Security checks ──
    if (!isset($_POST['fxt_broker_meta_nonce']) ||
        !wp_verify_nonce($_POST['fxt_broker_meta_nonce'], 'fxt_broker_meta')) {
        return;  // Nonce không hợp lệ → CSRF attack → abort
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    // WP tự autosave mỗi 60 giây — skip để không ghi đè data

    if (!current_user_can('edit_post', $post_id)) return;
    // Kiểm tra permission — chỉ user có quyền mới save được

    // ── Save text fields ──
    $text_fields = [
        'fxt_rating'   => '_fxt_rating',
        'fxt_spread'   => '_fxt_spread',
        // form field name => meta key in database
    ];

    foreach ($text_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_key]));
            // sanitize_text_field(): Xóa HTML tags, trim whitespace
            // Tương đương: validator.escape() trong Express
        }
    }

    // ── Save textarea fields (giữ line breaks) ──
    foreach ($textarea_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, sanitize_textarea_field($_POST[$form_key]));
            // sanitize_textarea_field(): Giống sanitize_text_field nhưng GIỮA \n
        }
    }

    // ── Save URL fields ──
    foreach ($url_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, esc_url_raw($_POST[$form_key]));
            // esc_url_raw(): Sanitize URL cho database (không encode HTML entities)
        }
    }

    // ── Save Broker Sections (array of sections) ──
    if (isset($_POST['fxt_sections']) && is_array($_POST['fxt_sections'])) {
        $sections = [];
        foreach ($_POST['fxt_sections'] as $sec) {
            if (empty($sec['title']) && empty($sec['content'])) continue;
            $sections[] = [
                'title'           => sanitize_text_field($sec['title'] ?? ''),
                'content'         => wp_kses_post($sec['content'] ?? ''),
                // wp_kses_post(): Cho phép HTML an toàn (p, h2, ul, a, img, strong...)
                // nhưng chặn script, iframe, onclick — QUAN TRỌNG cho security
                'pros'            => sanitize_textarea_field($sec['pros'] ?? ''),
                'cons'            => sanitize_textarea_field($sec['cons'] ?? ''),
                'collapsible'     => !empty($sec['collapsible']) ? '1' : '',
                'collapse_detail' => wp_kses_post($sec['collapse_detail'] ?? ''),
            ];
        }
        update_post_meta($post_id, '_fxt_broker_sections', $sections);
        // Lưu toàn bộ array vào 1 meta key — WP tự serialize thành string
    }
});
```

**Helper functions (cuối file):**

```php
function fxt_get_broker_meta($post_id) {
    return [
        'rating'   => get_post_meta($post_id, '_fxt_rating', true),
        'spread'   => get_post_meta($post_id, '_fxt_spread', true),
        // ...
        'pros' => array_filter(array_map('trim', explode("\n", get_post_meta($post_id, '_fxt_pros', true) ?: ''))),
        // explode("\n", "line1\nline2\nline3") → ["line1", "line2", "line3"]
        // array_map('trim', ...) → trim mỗi phần tử
        // array_filter(...) → xóa phần tử rỗng
        // Tương đương JS: string.split('\n').map(s => s.trim()).filter(Boolean)
    ];
}

function fxt_get_broker_sections($post_id) {
    $sections = get_post_meta($post_id, '_fxt_broker_sections', true);
    if (!is_array($sections)) return [];

    foreach ($sections as &$sec) {
        // & = pass by reference (modify trực tiếp, không tạo copy)
        $sec['pros_arr'] = array_filter(array_map('trim', explode("\n", $sec['pros'] ?? '')));
        $sec['cons_arr'] = array_filter(array_map('trim', explode("\n", $sec['cons'] ?? '')));
    }
    return $sections;
}
```

### 5.5. `inc/customizer.php` — Config Panel

**Mục đích:** Đăng ký TẤT CẢ text customizable trong Appearance → Customize.

**Pattern lặp lại:**

```php
add_action('customize_register', function ($wp_customize) {

    // 1. Tạo SECTION (nhóm settings)
    $wp_customize->add_section('fxt_hero', [
        'title'    => '🏠 Homepage',
        'priority' => 25,  // Thứ tự hiển thị trong sidebar Customizer
    ]);

    // 2. Tạo nhiều SETTINGS + CONTROLS cùng lúc bằng loop
    $hero_fields = [
        'fxt_hero_badge' => ['Badge text', 'Latest Forex Broker Reviews 2026'],
        'fxt_hero_title' => ['Main Title', '{accent}Trusted{/accent} Forex Broker Reviews'],
        // setting_id   => [label hiện cho user, default value]
    ];

    foreach ($hero_fields as $id => [$label, $default]) {
        // SETTING = nơi lưu data (trong wp_options table)
        $wp_customize->add_setting($id, [
            'default'           => $default,
            'sanitize_callback' => 'sanitize_text_field',
            // Mỗi khi user save, data chạy qua sanitize_text_field() trước khi lưu DB
        ]);

        // CONTROL = UI element trong Customizer
        $wp_customize->add_control($id, [
            'label'   => $label,
            'section' => 'fxt_hero',  // Thuộc section nào
            'type'    => 'text',       // Input type: text, textarea, url, checkbox, select...
        ]);
    }

    // Tương tự cho 8 sections khác: Sections, Broker Labels, UI Labels,
    // Breadcrumbs, Sidebar, Affiliate, Social, Footer
});
```

**8 Sections trong Customizer:**

| Section | Settings | Mục đích |
|---|---|---|
| 🏠 Homepage | 12 fields | Hero badge, title, desc, buttons, stats |
| 🏠 Homepage - Sections | 8 fields | Section titles, CTA, category slug |
| 📊 Labels - Broker Pages | 17 fields | Review prefix, button text, spec labels, collapsible text |
| 📊 Broker Comparison | 7 fields | Compare page title, search, sort labels |
| 🔗 Broker Permalinks | 2 fields | URL slugs cho broker |
| 🔤 Labels General | 19 fields | Search, reading time, TOC, share, pagination, 404 |
| 🔗 Breadcrumbs | 3 fields | Home text, broker archive text, search prefix |
| 📌 Labels Sidebar | 3 fields | Widget titles |
| 💰 Affiliate Setup | 2 fields | Default affiliate link, CTA text |
| 🌐 Social Media | 4 fields | Facebook, Telegram, YouTube, TikTok URLs |
| 📋 Footer | 12 fields | About, columns, links, slugs, disclaimer, copyright |

### 5.6. `inc/template-functions.php` — Helper Functions

**Mục đích:** Utility functions dùng trong templates.

```php
// ── Reading Time ──
function fxt_reading_time($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $minutes = max(1, ceil($word_count / 200));  // 200 words/minute
    $template = get_theme_mod('fxt_label_reading_time', '{min} min read');
    return str_replace('{min}', $minutes, $template);
}
// Dùng: echo fxt_reading_time(); → "5 min read"

// ── Star Rating ──
function fxt_star_rating($rating, $max = 10) {
    // Convert 0-10 scale → 0-5 stars
    $stars_5 = round(($rating / $max) * 5, 1);
    $full = floor($stars_5);           // Số sao đầy
    $half = ($stars_5 - $full) >= 0.5; // Có nửa sao không
    $empty = 5 - $full - ($half ? 1 : 0);

    // Build HTML: ★★★★☆ 8.5/10
    $html = str_repeat('<span class="star star-full">★</span>', $full);
    if ($half) $html .= '<span class="star star-half">★</span>';
    $html .= str_repeat('<span class="star star-empty">☆</span>', $empty);
    $html .= '<span class="rating-number">' . esc_html($rating) . '/10</span>';
    return $html;
}

// ── Table of Contents ──
function fxt_table_of_contents($content = '') {
    // Dùng regex tìm tất cả H2, H3 trong content
    preg_match_all('/<h([2-3])[^>]*>(.*?)<\/h[2-3]>/i', $content, $matches, PREG_SET_ORDER);
    if (count($matches) < 3) return '';  // Dưới 3 headings → không cần TOC

    // Build HTML: danh sách links anchor
    foreach ($matches as $i => $match) {
        $level = $match[1];  // 2 hoặc 3
        $text = strip_tags($match[2]);
        $id = 'heading-' . sanitize_title($text) . '-' . $i;
        // sanitize_title(): "Forex là gì?" → "forex-la-gi"
    }
    return $toc;
}

// ── Post Meta (category, date, author, reading time) ──
function fxt_post_meta() {
    // Render HTML block với SVG icons + text
    // Dùng trong single.php
}

// ── Share Buttons ──
function fxt_share_buttons() {
    $url = urlencode(get_permalink());
    $title = urlencode(get_the_title());
    // Render links: Facebook sharer, Twitter intent, Telegram share
}

// ── Related Posts ──
function fxt_related_posts($count = 4) {
    global $post;
    $categories = get_the_category($post->ID);
    // Query bài cùng category, random order, exclude bài hiện tại
}

// ── Pagination ──
function fxt_pagination() {
    global $wp_query;
    if ($wp_query->max_num_pages <= 1) return;  // 1 trang → không cần
    echo paginate_links([
        'mid_size'  => 2,  // Hiện 2 số mỗi bên trang hiện tại
        'prev_text' => esc_html(get_theme_mod('fxt_label_prev', '← Previous')),
        'next_text' => esc_html(get_theme_mod('fxt_label_next', 'Next →')),
    ]);
}
```

### 5.7. `inc/seo-helpers.php` — SEO tự động

**Mục đích:** Tự viết SEO cơ bản thay vì dùng plugin nặng (Yoast ~2MB).

**Schema.org JSON-LD:**

```php
// Trang chủ: WebSite + Organization schema
add_action('wp_head', function () {
    if (!is_front_page()) return;  // Chỉ chạy ở trang chủ

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            ['@type' => 'WebSite', 'name' => get_bloginfo('name'), ...],
            ['@type' => 'Organization', 'name' => get_bloginfo('name'), ...],
        ],
    ];
    echo '<script type="application/ld+json">'
         . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
         . '</script>';
});

// Bài viết: Article schema
// Broker: Review schema (ratingValue, bestRating, worstRating)
// Tất cả trang: BreadcrumbList schema
```

**Breadcrumbs function:**

```php
function fxt_breadcrumbs() {
    if (is_front_page()) return;  // Trang chủ không cần breadcrumbs

    // Luôn bắt đầu bằng Home
    echo '<a href="' . home_url('/') . '">' . $home_text . '</a>';

    // Phân loại theo loại trang
    if (is_singular('post')) {
        // Home > Category > Post Title
    } elseif (is_singular('broker')) {
        // Home > Broker Reviews > Broker Name
    } elseif (is_category()) {
        // Home > Category Name
    } elseif (is_search()) {
        // Home > Search: "keyword"
    }
    // ... v.v.
}
```

**Open Graph (chia sẻ Facebook/Zalo):**

```php
add_action('wp_head', function () {
    // Output: <meta property="og:title" content="Bài viết ABC">
    //         <meta property="og:description" content="Mô tả...">
    //         <meta property="og:image" content="https://...featured-image.jpg">
    //         <meta name="twitter:card" content="summary_large_image">
});
```

### 5.8. `inc/demo-import.php` — Import nội dung mẫu

**Mục đích:** Tạo nội dung demo khi cài theme mới (3 brokers, 6 bài viết, pages, menu).

**KHÔNG tự chạy** — phải click thủ công trong Admin → Appearance → Import Demo.

```php
// Thêm trang admin
add_action('admin_menu', function () {
    add_theme_page(
        'Import Demo Content',      // Page title
        '⚡ Import Demo',           // Menu title
        'manage_options',            // Capability (chỉ admin)
        'fxt-demo-import',          // Menu slug
        'fxt_demo_import_page'      // Render function
    );
});

function fxt_import_demo_content($skip_settings = false) {
    if (get_option('fxt_demo_imported')) return;  // Chỉ chạy 1 lần

    // 1. Tạo Categories
    wp_insert_term('Kiến thức Forex', 'category', ['slug' => 'kien-thuc-forex']);

    // 2. Tạo Brokers
    $post_id = wp_insert_post([
        'post_title'   => 'Exness',
        'post_content' => '<h2>Exness có an toàn không?</h2><p>...</p>',
        'post_type'    => 'broker',
        'post_status'  => 'publish',
    ]);
    update_post_meta($post_id, '_fxt_rating', '9.2');
    update_post_meta($post_id, '_fxt_spread', 'From 0.0 pips');

    // 3. Tạo Pages
    // 4. Tạo Menu + gán vào locations
    // 5. Cấu hình Reading Settings
    // 6. Set permalink structure

    update_option('fxt_demo_imported', true);  // Đánh dấu đã import
}
```

---

## 6. Thư mục `template-parts/` — Components

Tương đương React components — các khối UI tái sử dụng.

### 6.1. `template-parts/content-card.php` — Card bài viết (vertical)

```php
<article class="post-card">
    <!-- Featured image -->
    <?php if (has_post_thumbnail()): ?>
    <a href="<?php the_permalink(); ?>" class="post-card-image">
        <?php the_post_thumbnail('fxt-card'); ?>
        <!-- Output: <img src="image-400x250.jpg" width="400" height="250"> -->
    </a>
    <?php endif; ?>

    <div class="post-card-body">
        <!-- Category badge -->
        <?php $cats = get_the_category(); if ($cats): ?>
        <a href="<?php echo get_category_link($cats[0]->term_id); ?>" class="post-card-cat">
            <?php echo esc_html($cats[0]->name); ?>
        </a>
        <?php endif; ?>

        <!-- Title -->
        <h3 class="post-card-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <!-- Excerpt (tóm tắt) -->
        <p class="post-card-excerpt">
            <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
            <!-- wp_trim_words(text, word_count): Cắt còn 20 từ -->
        </p>

        <!-- Footer: time ago + reading time -->
        <div class="post-card-footer">
            <span>
                <?php echo human_time_diff(get_the_time('U'), current_time('timestamp'))
                    . ' ' . esc_html(get_theme_mod('fxt_label_ago', 'ago')); ?>
                <!-- human_time_diff(): "2 hours", "3 days", "1 month"
                     get_the_time('U'): Unix timestamp bài viết
                     current_time('timestamp'): Thời gian hiện tại -->
            </span>
            <span><?php echo esc_html(fxt_reading_time()); ?></span>
        </div>
    </div>
</article>
```

### 6.2. `template-parts/content-card-horizontal.php`

Giống card vertical nhưng layout ngang (image bên trái, text bên phải). Dùng cho section Education trên homepage.

### 6.3. `template-parts/content-none.php` — Empty state

```php
<div class="content-none">
    <h2><?php echo esc_html(get_theme_mod('fxt_label_notfound', 'No Content Found')); ?></h2>
    <?php if (is_search()):
        // Nếu đang ở trang search → hiện message cụ thể
        $tpl = get_theme_mod('fxt_label_notfound_search', 'No results for "{query}".');
        echo '<p>' . esc_html(str_replace('{query}', get_search_query(), $tpl)) . '</p>';
    endif; ?>
    <?php get_search_form(); ?>
    <!-- Hiện form search để user thử lại -->
</div>
```

---

## 7. Thư mục `page-templates/`

### 7.1. `page-templates/template-brokers.php` — Trang so sánh broker

**Khi nào chạy:** Khi user tạo Page → chọn Template = "So sánh Broker" trong admin.

```php
<?php
/**
 * Template Name: So sánh Broker
 * ↑ DÒNG NÀY QUAN TRỌNG — WP đọc comment này để biết đây là page template
 *   Nó sẽ xuất hiện trong dropdown "Template" khi edit Page trong admin
 */
```

**Cấu trúc:**

```
┌─ Header (title + description)
├─ Filter bar (search input + sort dropdown)
└─ Broker table (loop tất cả brokers)
    └─ Mỗi row: rank, logo, name, rating, specs, action buttons
```

**Data attributes cho JS filtering:**

```php
<div class="broker-row"
     data-name="<?php echo esc_attr(strtolower(get_the_title())); ?>"
     data-rating="<?php echo esc_attr($meta['rating']); ?>"
     data-spread="<?php echo esc_attr($spread_num); ?>"
     data-deposit="<?php echo esc_attr($deposit_num); ?>">
<!-- JS đọc data-* attributes để sort và filter
     Tương đương: React props hoặc Vue :data-binding -->
```

---

## 8. Thư mục `assets/js/`

### 8.1. `assets/js/main.js` — JS chính

**Tất cả viết bằng Vanilla JS (không jQuery)** — giảm ~90KB.

```javascript
document.addEventListener('DOMContentLoaded', function () {
    // ── Mobile Menu ──
    // Toggle class 'active' trên overlay
    // Lock body scroll khi menu mở
    // Close on: X button, click outside, Escape key

    // ── Search Toggle ──
    // Toggle class 'active' trên search overlay
    // Auto focus input khi mở

    // ── Back to Top ──
    // Show button khi scroll > 400px
    // Smooth scroll lên đầu khi click

    // ── Sticky Header Shadow ──
    // Thêm box-shadow khi scroll > 10px

    // ── Smooth Scroll ──
    // Cho tất cả anchor links (#section)
    // Trừ đi header height để không bị che

    // ── External Links ──
    // Tự động thêm target="_blank" rel="noopener nofollow"
    // cho link ngoài site trong .entry-content

    // ── Lazy Load Fallback ──
    // IntersectionObserver cho browser cũ không support native lazy loading
});
```

### 8.2. `assets/js/broker-filter.js` — Filter/sort trang so sánh

Chỉ load trên trang so sánh broker (conditional enqueue trong enqueue.php).

```javascript
// ── Sort ──
sortSelect.addEventListener('change', function () {
    brokerItems.sort(function (a, b) {
        var aVal = parseFloat(a.dataset[sortBy]) || 0;
        var bVal = parseFloat(b.dataset[sortBy]) || 0;
        // Đọc data-rating, data-spread, data-deposit từ HTML
        if (sortBy === 'rating') return bVal - aVal;  // Rating: cao → thấp
        return aVal - bVal;  // Spread/Deposit: thấp → cao
    });
    // Re-append DOM elements (thay đổi thứ tự)
    brokerItems.forEach(function (item) { brokerList.appendChild(item); });
});

// ── Search ──
searchInput.addEventListener('input', function () {
    var query = this.value.toLowerCase().trim();
    brokerItems.forEach(function (item) {
        var name = (item.dataset.name || '').toLowerCase();
        item.style.display = name.includes(query) ? '' : 'none';
    });
});
```

### 8.3. `assets/js/broker-sections.js` — Tab navigation + collapsible

Chỉ load trên single broker page.

```javascript
// ── Tab Navigation ──
// Click tab → smooth scroll đến section
// Scroll spy → update active tab dựa vào scroll position
// Auto scroll active tab into view trong tab bar (horizontal scroll)

// ── Collapsible Sections ──
// Click button → toggle display:none/block
// Update button text (Show/Hide)
```

---

## 9. `style.css` — Stylesheet chính

### Metadata (bắt buộc)

```css
/*
Theme Name: FX Trading Today        ← Tên hiện trong Admin → Themes
Theme URI: https://fxtradingtoday.com
Author: Timo
Description: Custom WordPress theme cho forex affiliate blog.
Version: 2.0.4
Requires at least: 6.0              ← WordPress version tối thiểu
Requires PHP: 8.0
Text Domain: fxtradingtoday         ← Dùng cho translation (i18n)
*/
```

**WordPress BUỘC file này phải ở root theme** và phải có comment block trên.

### 18 phần CSS

| Phần | Mục đích | Điểm quan trọng |
|---|---|---|
| 1. WordPress Override | Xóa CSS mặc định WP | `!important` cần thiết vì WP inject inline styles |
| 2. CSS Variables | Design tokens | Tất cả màu, font, spacing, shadow, border-radius |
| 3. Base Reset | Normalize | `box-sizing: border-box` cho mọi element |
| 4. Layout | Grid system | `.container` max-width 1200px, `.layout-with-sidebar` CSS Grid |
| 5. Header | Sticky header | `backdrop-filter: blur()` cho glass effect |
| 6. Buttons | Button styles | 6 variants: primary, outline, cta, lg, sm, block |
| 7. Hero | Homepage hero | Dark bg + gradient overlay + grid pattern |
| 8. Sections | Section layout | Alternating bg (white/gray) |
| 9. Broker Cards | Homepage broker list | 4-column grid layout |
| 10. Post Cards | Blog card | Vertical + horizontal variants |
| 11. Single Post | Article layout | Entry content typography, TOC, tags, author box |
| 12. Broker Single | Broker review | Hero, specs table, pros/cons boxes |
| 13. Sidebar | Sidebar widgets | Sticky positioning |
| 14. Pagination/404 | Navigation | Paginate links, error page |
| 15. Footer | Footer | 3-column grid, disclaimer, copyright |
| 16. Mobile Menu | Overlay menu | Slide-in from right |
| 17. Responsive | Breakpoints | 1024px, 768px, 480px |
| 18. Broker Tabs | Tab navigation | Sticky tabs, scroll spy support |

### CSS Variables (design system):

```css
:root {
    /* Colors */
    --c-primary: #0f5fe0;           /* Blue — links, buttons, accents */
    --c-accent: #f59e0b;            /* Amber — CTAs, ratings, featured */
    --c-success: #10b981;           /* Green — pros */
    --c-danger: #ef4444;            /* Red — cons */
    --c-text: #0f172a;              /* Near-black — body text */
    --c-text-2: #64748b;            /* Gray — secondary text */
    --c-bg-dark: #0c1222;           /* Dark navy — hero, footer */

    /* Typography */
    --font: 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, sans-serif;

    /* Layout */
    --container: 1200px;
    --sidebar-w: 320px;
    --header-h: 64px;

    /* Border radius */
    --r-sm: 6px;
    --r-md: 10px;
    --r-lg: 16px;

    /* Shadows */
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
    --shadow-lg: 0 12px 40px rgba(0,0,0,0.1);
}
```

---

## Tổng Kết: Data Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  USER nhập data ở đâu?                                         │
├─────────────────────────────────────────────────────────────────┤
│  WP Admin → Posts/Brokers → Bài viết, broker details            │
│  WP Admin → Appearance → Customize → Tất cả text, links, slugs │
│  WP Admin → Appearance → Menus → Cấu trúc navigation           │
│  WP Admin → Appearance → Widgets → Sidebar content              │
│  WP Admin → Media Library → Ảnh, logo                          │
└─────────────┬───────────────────────────────────────────────────┘
              │ Lưu vào MySQL database
              ▼
┌─────────────────────────────────────────────────────────────────┐
│  THEME code đọc data thế nào?                                   │
├─────────────────────────────────────────────────────────────────┤
│  get_theme_mod('key', 'default')  → Customizer settings         │
│  get_post_meta(id, 'key', true)   → Broker meta data            │
│  the_title(), the_content()       → Post/page content            │
│  wp_nav_menu([...])               → Menu HTML                    │
│  dynamic_sidebar('id')            → Widget HTML                  │
│  WP_Query([...])                  → Custom database queries      │
└─────────────┬───────────────────────────────────────────────────┘
              │ Render HTML
              ▼
┌─────────────────────────────────────────────────────────────────┐
│  OUTPUT flow cho mỗi page request:                              │
│                                                                  │
│  functions.php (load modules)                                    │
│       → WordPress routing (chọn template file)                   │
│            → header.php (HTML head + header + main open)         │
│            → [template].php (page content)                       │
│                 → template-parts/*.php (reusable components)     │
│                 → sidebar.php (sidebar widgets)                   │
│            → footer.php (footer + scripts + main/body close)     │
└─────────────────────────────────────────────────────────────────┘
```

### Security Functions Cheat Sheet

| Function | Dùng khi | Ví dụ |
|---|---|---|
| `esc_html()` | Output text vào HTML | `<p><?php echo esc_html($text); ?></p>` |
| `esc_attr()` | Output vào HTML attribute | `<input value="<?php echo esc_attr($val); ?>">` |
| `esc_url()` | Output URL vào HTML | `<a href="<?php echo esc_url($url); ?>">` |
| `esc_textarea()` | Output vào textarea | `<textarea><?php echo esc_textarea($text); ?></textarea>` |
| `wp_kses_post()` | Output HTML an toàn | Cho phép p, h2, a, img... chặn script |
| `sanitize_text_field()` | Lưu text vào DB | Xóa HTML tags, trim |
| `sanitize_textarea_field()` | Lưu textarea vào DB | Như trên nhưng giữ newlines |
| `esc_url_raw()` | Lưu URL vào DB | Không encode HTML entities |
| `wp_verify_nonce()` | Kiểm tra CSRF token | Trong save handlers |

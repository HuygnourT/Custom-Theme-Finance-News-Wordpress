# FX Trading Today v2 — Giải Thích Cấu Trúc Dự Án

---

## 📁 Cấu trúc thư mục

```
fxtradingtoday-v2/
│
├── style.css                  ← CSS chính + metadata theme (WP đọc file này đầu tiên)
├── functions.php              ← Entry point (giống index.js / app.js trong Node)
├── screenshot.png             ← Ảnh preview trong WP Admin → Themes
│
├── header.php                 ← HTML <head> + <header> + menu (mọi trang đều gọi)
├── footer.php                 ← HTML </main> + <footer> + scripts (mọi trang đều gọi)
│
├── front-page.php             ← Template trang chủ (hero + brokers + posts + CTA)
├── index.php                  ← Template danh sách bài viết (blog page)
├── single.php                 ← Template chi tiết 1 bài viết
├── single-broker.php          ← Template chi tiết 1 broker review
├── page.php                   ← Template trang tĩnh (About, Contact...)
├── archive.php                ← Template danh mục / tag / archive
├── search.php                 ← Template kết quả tìm kiếm
├── 404.php                    ← Template trang lỗi 404
├── sidebar.php                ← Sidebar bên phải (widgets)
│
├── template-parts/            ← Components tái sử dụng (giống React components)
│   ├── content-card.php       ← Card bài viết (dùng trong grid)
│   ├── content-card-horizontal.php  ← Card bài viết ngang
│   └── content-none.php       ← Hiển thị khi không có nội dung
│
├── page-templates/            ← Custom page templates (gán cho page cụ thể)
│   └── template-brokers.php   ← Trang so sánh tất cả brokers
│
├── inc/                       ← Modules PHP (giống thư mục /src hoặc /lib trong Node)
│   ├── theme-setup.php        ← Đăng ký features: menu, image sizes, widgets
│   ├── enqueue.php            ← Load CSS/JS + tắt WP default styles
│   ├── custom-post-types.php  ← Đăng ký post type "Broker" (giống tạo model)
│   ├── meta-boxes.php         ← Form nhập liệu cho Broker trong admin
│   ├── customizer.php         ← TOÀN BỘ text customizable từ WP Admin
│   ├── template-functions.php ← Helper functions: star rating, TOC, share, related
│   ├── seo-helpers.php        ← Schema markup, breadcrumbs, Open Graph
│   └── demo-import.php        ← Tạo nội dung mẫu khi cài theme mới
│
└── assets/
    ├── js/
    │   ├── main.js            ← Mobile menu, search, back-to-top, scroll effects
    │   └── broker-filter.js   ← Filter/sort trên trang so sánh brokers
    └── images/                ← (trống - ảnh upload qua WP Media Library)
```

---

## 🔄 WordPress hoạt động như thế nào (so với Node.js)

### So sánh tổng quan

| Khái niệm | Node.js / Express | WordPress |
|---|---|---|
| Entry point | `index.js` / `app.js` | `functions.php` |
| Routing | `app.get('/path', handler)` | WP tự route dựa theo tên file template |
| Components | React components | `template-parts/*.php` |
| Database model | Mongoose Schema / Sequelize Model | Custom Post Type + Meta Boxes |
| Config / .env | `.env` file | Customizer (lưu trong database) |
| CSS loading | `<link>` trong HTML | `wp_enqueue_style()` |
| JS loading | `<script>` trong HTML | `wp_enqueue_script()` |
| Events | `emitter.on('event', fn)` | `add_action('hook', fn)` |
| Middleware | `app.use(middleware)` | `add_filter('data', fn)` |

### Template Routing — WP tự chọn file hiển thị

WordPress không cần viết routing. Nó tự quyết hiển thị file nào dựa vào URL:

```
URL truy cập                →  File được gọi
─────────────────────────────────────────────
yoursite.com/               →  front-page.php
yoursite.com/blog/          →  index.php
yoursite.com/bai-viet-abc/  →  single.php
yoursite.com/broker/exness/ →  single-broker.php
yoursite.com/about-us/      →  page.php
yoursite.com/category/forex/→  archive.php
yoursite.com/?s=keyword     →  search.php
yoursite.com/xyz-404        →  404.php
```

### Mỗi trang đều chạy theo flow:

```
header.php → [template file] → footer.php
```

Giống layout pattern trong Express/EJS:
```
<%- include('header') %>
  [page content]
<%- include('footer') %>
```

---

## 📦 Giải thích từng file trong `inc/`

### 1. `functions.php` — Entry point

```php
require_once FXT_DIR . '/inc/theme-setup.php';      // Bước 1: Setup features
require_once FXT_DIR . '/inc/enqueue.php';           // Bước 2: Load CSS/JS
require_once FXT_DIR . '/inc/custom-post-types.php'; // Bước 3: Tạo "model" Broker
require_once FXT_DIR . '/inc/meta-boxes.php';        // Bước 4: Form nhập liệu
require_once FXT_DIR . '/inc/customizer.php';        // Bước 5: Config panel
require_once FXT_DIR . '/inc/seo-helpers.php';       // Bước 6: SEO tự động
require_once FXT_DIR . '/inc/template-functions.php'; // Bước 7: Helper functions
require_once FXT_DIR . '/inc/demo-import.php';       // Bước 8: Demo content
```

Tương đương trong Node.js:
```javascript
const themeSetup = require('./inc/theme-setup');
const enqueue = require('./inc/enqueue');
// ... etc
```

### 2. `theme-setup.php` — Đăng ký features

Khai báo theme hỗ trợ gì:
- **Menu locations**: 3 vị trí (header, footer, mobile)
- **Image sizes**: 4 kích thước tự động resize khi upload
- **Widget areas**: Sidebar chính, sidebar broker, 3 cột footer
- **Tối ưu**: Tắt emoji script, xóa meta tags thừa

### 3. `enqueue.php` — Load CSS/JS + Tắt WP mặc định

**Đây là file quan trọng nhất** cho vấn đề "layout không khớp preview":

```
Làm 2 việc:
1. Load: Google Fonts → style.css → main.js
2. TẮT: wp-block-library, global-styles, classic-theme-styles
         (những CSS mặc định WP phá layout của theme)
```

Tại sao phải tắt? WordPress 6.x thêm ~80KB CSS mặc định cho Gutenberg blocks.
CSS này thêm `margin`, `padding`, `max-width` vào các elements → phá layout theme.

### 4. `custom-post-types.php` — "Model" cho Broker

Trong Node.js bạn tạo model Mongoose:
```javascript
const BrokerSchema = new Schema({ name: String, rating: Number... });
```

Trong WordPress dùng `register_post_type()`:
```php
register_post_type('broker', [...]);
```

Sau khi đăng ký, WP Admin sẽ có menu "Brokers" riêng, giống Posts nhưng dành cho broker.

### 5. `meta-boxes.php` — Form nhập liệu Broker

Thêm form fields vào trang editor broker trong Admin:
- Rating, Spread, Leverage, Min Deposit
- Regulation, Founded, Platforms
- Affiliate Link, Website URL
- Ưu điểm, Nhược điểm (textarea, mỗi dòng 1 item)

Giống việc tạo HTML form + xử lý POST request trong Express.

### 6. `customizer.php` — Config Panel (Appearance → Customize)

**8 sections**, mỗi section chứa nhiều settings:

| Section | Nội dung |
|---|---|
| 🏠 Hero | Badge, tiêu đề, mô tả, 2 nút, 3 stats |
| 🏠 Sections | Tiêu đề các section, CTA, slug category |
| 📊 Broker Labels | Prefix review, nút mở TK, spec labels |
| 🔤 Labels chung | Search, reading time, TOC, share, pagination, 404 |
| 📌 Sidebar | Tiêu đề 3 widget |
| 💰 Affiliate | Link mặc định, CTA header |
| 🌐 Social | Facebook, Telegram, YouTube, TikTok |
| 📋 Footer | About, disclaimer, copyright |

**Tất cả lưu trong `wp_options` table (database)** → update theme không mất.

Trong template gọi: `get_theme_mod('fxt_hero_title', 'giá trị mặc định')`

### 7. `template-functions.php` — Helper Functions

| Function | Tương đương Node.js |
|---|---|
| `fxt_reading_time()` | Tính thời gian đọc bài |
| `fxt_star_rating(8.5)` | Render HTML sao đánh giá |
| `fxt_pagination()` | Phân trang |
| `fxt_table_of_contents()` | Tự tạo TOC từ headings |
| `fxt_related_posts()` | Query bài liên quan |
| `fxt_share_buttons()` | Nút chia sẻ Facebook/Twitter/Telegram |
| `fxt_get_broker_meta()` | Lấy tất cả meta data broker |

### 8. `seo-helpers.php` — SEO tự động

- **Schema.org JSON-LD**: Organization, Article, Review, BreadcrumbList
- **Breadcrumbs**: Trang chủ > Danh mục > Bài viết
- **Open Graph**: Meta tags cho chia sẻ Facebook/Zalo

### 9. `demo-import.php` — Nội dung mẫu

Chạy thủ công từ **Appearance → ⚡ Import Demo**:
- Tạo 4 categories
- Tạo 3 brokers (Exness, ICMarkets, XM)
- Tạo 6 bài viết mẫu
- Tạo pages (Trang chủ, Blog, So sánh, About, Contact, Disclaimer, Privacy)
- Tạo menu và gán vào vị trí
- Cấu hình permalink

---

## 🎨 `style.css` — Cấu trúc CSS

```
Phần 1:  WordPress Override     ← TẮT CSS mặc định WP (quan trọng nhất!)
Phần 2:  CSS Variables          ← Màu sắc, spacing, font, shadow
Phần 3:  Base Reset             ← Reset margin/padding mọi element
Phần 4:  Layout                 ← Container, grid sidebar
Phần 5:  Header                 ← Sticky header, nav, dropdown, search
Phần 6:  Buttons                ← .btn-primary, .btn-outline, .btn-cta
Phần 7:  Hero                   ← Section hero trang chủ
Phần 8:  Sections               ← Section headers, spacing
Phần 9:  Broker Cards           ← Card broker trên homepage
Phần 10: Post Cards             ← Card bài viết (vertical + horizontal)
Phần 11: Single Post            ← Chi tiết bài viết, entry-content, TOC
Phần 12: Broker Single          ← Chi tiết broker, specs table, pros/cons
Phần 13: Sidebar                ← Sidebar widgets
Phần 14: Pagination / 404       ← Phân trang, archive, 404
Phần 15: Footer                 ← 3 cột footer, disclaimer
Phần 16: Mobile Menu            ← Menu mobile overlay, back-to-top
Phần 17: Responsive             ← Breakpoints: 1024, 768, 480px
```

---

## 🗄️ Dữ liệu lưu ở đâu?

```
┌─────────────────────────────────────────────┐
│              DATABASE (wp_options)           │  ← Update theme KHÔNG mất
│  • Customizer settings (text, links)        │
│  • Menu structure                           │
│  • Widget configuration                     │
│  • Reading settings                         │
├─────────────────────────────────────────────┤
│              DATABASE (wp_posts)             │  ← Update theme KHÔNG mất
│  • Bài viết (posts)                         │
│  • Trang (pages)                            │
│  • Brokers (custom post type)               │
├─────────────────────────────────────────────┤
│              DATABASE (wp_postmeta)          │  ← Update theme KHÔNG mất
│  • Broker meta: rating, spread, pros/cons   │
├─────────────────────────────────────────────┤
│              THEME FILES (code)              │  ← Chỉ phần này thay đổi khi update
│  • style.css (giao diện)                    │
│  • *.php (layout, logic hiển thị)           │
│  • assets/js/ (interactions)                │
└─────────────────────────────────────────────┘
```

**Khi update theme** = chỉ thay đổi code files (giao diện)
**Nội dung** (bài viết, broker, menu, customizer text) = giữ nguyên 100%

---

## 🔀 Flow khi user truy cập trang

```
User truy cập yoursite.com/broker/exness/
          │
          ▼
WordPress core load
          │
          ▼
functions.php chạy → load 8 modules trong inc/
          │
          ▼
WP routing: URL chứa "broker" → chọn single-broker.php
          │
          ▼
single-broker.php gọi get_header()
          │
          ▼
header.php render:
  • <head> + CSS (từ enqueue.php)
  • <header> + logo + menu (từ WP database)
  • Search overlay + mobile menu
          │
          ▼
single-broker.php render:
  • Broker hero (tên, rating, nút CTA)
  • Specs table (data từ wp_postmeta)
  • Pros/Cons (data từ wp_postmeta)
  • Entry content (từ wp_posts)
  • Share buttons (từ template-functions.php)
  • Bottom CTA (text từ Customizer)
  │
  ├── sidebar.php render:
  │     • Top brokers (query từ database)
  │     • Popular posts (query từ database)
  │
          ▼
footer.php render:
  • Footer grid (text từ Customizer)
  • Disclaimer (từ Customizer)
  • Copyright (từ Customizer)
  • Back-to-top button
  • JS files load
```

---

## ✏️ Khi cần thay đổi

| Muốn thay đổi | Làm ở đâu |
|---|---|
| Text trên trang | WP Admin → Appearance → Customize |
| Bài viết / broker | WP Admin → Posts / Brokers |
| Menu | WP Admin → Appearance → Menus |
| Logo | WP Admin → Appearance → Customize → Site Identity |
| Màu sắc / font | Sửa CSS variables trong `style.css` (Phần 2) |
| Layout trang chủ | Sửa `front-page.php` |
| Layout broker | Sửa `single-broker.php` |
| Thêm field cho broker | Sửa `inc/meta-boxes.php` + `inc/template-functions.php` |
| Thêm Customizer setting | Sửa `inc/customizer.php` |
| SEO / Schema | Sửa `inc/seo-helpers.php` |

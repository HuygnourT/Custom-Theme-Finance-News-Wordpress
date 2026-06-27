# FXTradingToday v2.1 — Hướng Dẫn Cài Đặt Update

## 📦 Danh sách file mới/sửa

### File MỚI (thêm vào theme):
```
inc/meta-boxes-sub-posts.php    ← Meta boxes cho CTA, Pros/Cons, Sections (dùng chung broker_post + generic_post)
inc/generic-post-types.php      ← CPT "Sub Posts" — bài phụ đa chủ đề
inc/mega-menu.php               ← Category Bar dưới header
single-generic_post.php         ← Template cho bài phụ đa chủ đề
style-additions.css             ← CSS mới (append vào cuối style.css)
```

### File SỬA (thay thế file cũ):
```
functions.php                   ← Thêm 3 dòng require_once mới
header.php                      ← Thêm fxt_category_bar() sau </header>
single-broker_post.php          ← Xóa TOC, thêm CTA/Pros-Cons/Sections
```

---

## 🔧 Cách cài đặt

### Bước 1: Upload file MỚI
- Copy `inc/meta-boxes-sub-posts.php` vào `wp-content/themes/fxtradingtoday/inc/`
- Copy `inc/generic-post-types.php` vào `wp-content/themes/fxtradingtoday/inc/`
- Copy `inc/mega-menu.php` vào `wp-content/themes/fxtradingtoday/inc/`
- Copy `single-generic_post.php` vào `wp-content/themes/fxtradingtoday/`

### Bước 2: Thay thế file SỬA
- Thay `functions.php` bằng file mới
- Thay `header.php` bằng file mới
- Thay `single-broker_post.php` bằng file mới

### Bước 3: Append CSS
- Mở `style.css`, copy toàn bộ nội dung `style-additions.css` vào **cuối file**

### Bước 4: Flush Permalinks
- Vào **Settings → Permalinks** → nhấn **Save Changes**

---

## ✅ Tính năng mới

### 1. Sub Broker Post (cải tiến)
- ❌ Đã xóa Table of Contents ở đầu bài
- ✅ **CTA Buttons**: Thêm nhiều nút CTA tùy chỉnh (text, URL, style Primary/CTA/Outline, mở tab mới)
- ✅ **Pros & Cons**: Bảng ưu/nhược điểm (mỗi dòng = 1 bullet)
- ✅ **Collapsible Sections**: Mỗi section gồm title, rich content (wp_editor), CTA buttons riêng, Pros/Cons riêng, hidden detail (ẩn/hiện)

### 2. Generic Sub-Posts (MỚI)
- Menu **"Sub Posts"** trong WP Admin sidebar
- Tạo bài phụ thuộc bất kỳ **Post, Page, Category, hoặc Custom URL slug**
- Có đầy đủ tính năng: CTA, Pros/Cons, Collapsible Sections
- URL tự động: `/{prefix}/{parent-slug}/{sub-post-slug}/`
- Taxonomy **"Chủ đề"** để phân loại
- Internal linking tự động (sibling posts)

### 3. Category Bar / Mega Menu (MỚI)
- Thanh category hiển thị ngay dưới header
- **2 cách cấu hình**:
  - Dùng WP Menu: tạo menu → gán vào vị trí "Category Bar"
  - Dùng Customizer: Appearance → Customize → 📂 Category Bar
- Dropdown tự động hiện sub-categories khi trỏ vào
- 3 style: Light / Dark / Primary
- Hỗ trợ icon emoji
- Tối đa 8 items tùy chỉnh qua Customizer
- Responsive (scroll ngang trên mobile)

---

## 📂 Category Bar — Cách sử dụng

### Cách 1: Dùng WP Menu (linh hoạt nhất)
1. Vào **Appearance → Menus**
2. Tạo menu mới, đặt tên "Category Bar"
3. Thêm các Category, Page, Custom Link
4. Kéo sub-items vào thành dropdown
5. Gán vào vị trí **"Category Bar (dưới Header)"**

### Cách 2: Dùng Customizer
1. Vào **Appearance → Customize → 📂 Category Bar**
2. Bật "Bật Category Bar"
3. Chọn Style (Light/Dark/Primary)
4. Điền các Item 1-8:
   - **Label**: Tên hiển thị (để trống = ẩn)
   - **Icon**: Emoji (📊, 📚, 💰...)
   - **Category slug**: Nhập slug → tự dropdown sub-categories
   - **URL**: Hoặc nhập URL tùy chỉnh

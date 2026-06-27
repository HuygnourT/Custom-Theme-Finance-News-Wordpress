# FX Trading Today — Hướng Dẫn Cấu Trúc Dự Án

> Tài liệu này viết cho **người không chuyên code** (chủ website, người quản trị nội dung).
> Mục tiêu: hiểu website gồm những phần nào, **muốn đổi gì thì vào đâu**, và làm sao cập nhật mà **không mất nội dung**.

---

## 1. Website này là gì?

Đây là một **theme (giao diện) WordPress** dành cho trang đánh giá & so sánh **sàn Forex (broker)**. Nó cho phép bạn:

- Đăng các bài **đánh giá sàn** (rating, spread, đòn bẩy, ưu/nhược điểm...).
- Viết **bài hướng dẫn** đi kèm từng sàn.
- Tùy chỉnh gần như **toàn bộ chữ trên trang** mà không cần sửa code, thông qua trang Quản trị.

---

## 2. Quy tắc vàng cần nhớ trước tiên

Hãy hình dung website gồm **2 phần tách biệt**:

| Phần | Nằm ở đâu | Gồm những gì | Khi update theme |
|---|---|---|---|
| **Nội dung** | Trong **database** (chỉnh qua trang Quản trị) | Bài viết, thông tin sàn, menu, chữ trong Customize, logo | **Giữ nguyên** |
| **Giao diện & logic** | Trong **file theme** | Bố cục, màu sắc, cách hiển thị | Phần này mới thay đổi khi update |

➡️ **Cập nhật theme = chỉ thay phần giao diện/logic. Nội dung của bạn vẫn còn nguyên trong database.** (Xem mục 8 để biết cách giữ chắc chắn.)

---

## 3. Bản đồ thư mục (giải thích đời thường)

```
fxtradingtoday/                    ← Thư mục theme
│
├── style.css            Giao diện tổng: màu sắc, font, bố cục + tên & phiên bản theme
├── functions.php        "Công tắc tổng" — nạp tất cả các phần bên dưới
│
├── header.php           Phần ĐẦU của mọi trang: logo, menu, ô tìm kiếm
├── footer.php           Phần CHÂN của mọi trang: cột footer, mạng xã hội, disclaimer
│
├── front-page.php       ⭐ TRANG CHỦ — gồm 6 khối, chỉnh chữ qua Customize
├── index.php            Trang danh sách bài viết (blog)
├── single.php           1 bài viết chi tiết
├── single-broker.php    1 trang đánh giá sàn
├── single-broker_post.php    1 bài phụ thuộc về 1 sàn cụ thể
├── single-generic_post.php   1 bài phụ đa chủ đề
├── page.php             1 trang tĩnh (Giới thiệu, Liên hệ...)
├── archive.php          Trang danh mục / thẻ
├── search.php           Trang kết quả tìm kiếm
├── 404.php              Trang báo "không tìm thấy"
├── sidebar.php          Cột bên phải (widget)
│
├── template-parts/      "Mảnh ghép" dùng lại nhiều nơi (thẻ bài viết...)
├── page-templates/      Mẫu trang đặc biệt (vd: bảng so sánh sàn)
│
├── inc/                 🧠 "Bộ não" — phần xử lý phía sau
│   ├── theme-setup.php          Khai báo tính năng (menu, cỡ ảnh, widget)
│   ├── enqueue.php              Nạp file giao diện (CSS/JS)
│   ├── performance.php          Tăng tốc tải trang (ưu tiên mobile)   ← mới
│   ├── custom-post-types.php    Tạo loại nội dung "Sàn (Broker)"
│   ├── generic-post-types.php   Tạo loại "Bài phụ đa chủ đề"
│   ├── meta-boxes.php           Form nhập thông tin sàn trong admin
│   ├── meta-boxes-sub-posts.php Form nhập cho các bài phụ
│   ├── mega-menu.php            Thanh danh mục dưới header
│   ├── customizer.php           Khai báo phần lớn chữ chỉnh được
│   ├── customizer-homepage.php  Khai báo chữ & khối của TRANG CHỦ      ← mới
│   ├── seo-helpers.php          SEO cơ bản: breadcrumb, chia sẻ mạng xã hội
│   ├── seo-schema.php           SEO nâng cao cho sàn & bài phụ          ← mới
│   ├── template-functions.php   Hàm tiện ích (sao đánh giá, chia trang...)
│   └── demo-import.php          Tạo nội dung mẫu khi cài theme mới
│
└── assets/
    ├── js/              File tương tác (menu mobile, tab, lọc sàn)
    └── css/home.css     Giao diện riêng cho các khối TRANG CHỦ mới     ← mới
```

Ngoài thư mục theme còn có **2 file "sửa lỗi"** đặt ở nơi khác (xem mục 7):

```
wp-content/mu-plugins/
├── fxt-underscore-fix.php     Sửa lỗi Customize không lưu được nội dung
└── fxt-persistent-mods.php    Giữ nội dung Customize khi đổi/cập nhật theme
```

---

## 4. "Tôi muốn đổi X thì vào đâu?" — Bảng tra nhanh

Hầu hết thay đổi đều làm trong **trang Quản trị (wp-admin)**, không cần đụng code:

| Muốn thay đổi | Vào đâu |
|---|---|
| Chữ trên **trang chủ** | Appearance → Customize → các mục **🏠 Home · ...** |
| **Logo** | Customize → Site Identity |
| **Menu** điều hướng | Appearance → Menus |
| Thông tin **1 sàn** (rating, spread, ưu/nhược điểm) | Brokers → chọn sàn → sửa |
| Thêm/sửa **bài viết** | Posts |
| Thêm/sửa **bài phụ về sàn** | Broker Posts |
| Nội dung **Footer** | Customize → 📋 Footer |
| **Mạng xã hội** | Customize → 🌐 Social Media |
| **Link affiliate** mặc định | Customize → 💰 Affiliate Setup |
| **Màu sắc / font chữ** | Cần người biết code, sửa trong `style.css` |
| **Bố cục** một loại trang | Cần người biết code, sửa file `.php` tương ứng |

---

## 5. Trang chủ gồm những khối nào & chỉnh ở đâu

Trang chủ (`front-page.php`) có **6 khối**, tất cả chữ đều chỉnh được trong **Appearance → Customize**:

| Khối trên trang chủ | Chỉnh trong Customize |
|---|---|
| 1. Phần giới thiệu đầu trang (tiêu đề lớn) | 🏠 Homepage |
| 2. Danh sách sàn đề xuất (+ chữ trên/dưới + khung note) | 🏠 Homepage **và** 🏠 Home · Brokers |
| 3. How We Can Help (3 ô + nút) | 🏠 Home · How We Can Help |
| 4. Guide By Topic (link tự nhập theo từng sàn) | 🏠 Home · Guide By Topic |
| 5. Everything You Need (các khối chữ) | 🏠 Home · Everything You Need |
| 6. About Us + Risk Disclaimer | 🏠 Home · About Us |

**Hai điều cần biết khi chỉnh trang chủ:**

- Mỗi khối (từ 3 đến 6) có ô **"Ẩn section này"**. Mặc định **bỏ tick = hiện**. Chỉ tick khi bạn muốn ẩn hẳn khối đó.
- Riêng khối **Guide By Topic**: mỗi ô chọn 1 sàn làm tiêu đề, rồi nhập danh sách link bên dưới — **mỗi dòng một link** theo dạng:
  ```
  Tiêu đề hiển thị | https://duong-dan-bai-viet
  ```
  Ví dụ:
  ```
  Hướng dẫn nạp tiền Exness | https://site.com/exness/nap-tien/
  Spread & phí Exness | https://site.com/exness/spread/
  ```
  Không giới hạn số dòng.

---

## 6. Các "loại nội dung" trong website (dễ nhầm nên đọc kỹ)

Website có nhiều loại nội dung khác nhau, mỗi loại có menu riêng trong trang Quản trị:

| Loại | Là gì | Quản lý ở |
|---|---|---|
| **Sàn (Broker)** | Trang đánh giá chính của 1 sàn — có rating, bảng thông số, ưu/nhược điểm | Menu **Brokers** |
| **Bài phụ về sàn (Broker Post)** | Bài hỗ trợ cho 1 sàn (vd "hướng dẫn nạp tiền Exness"). Đường dẫn nằm "bên dưới" sàn cha | Menu **Broker Posts** |
| **Bài phụ đa chủ đề (Sub Post)** | Giống bài phụ nhưng gắn vào bài/chuyên mục bất kỳ, không chỉ sàn | Menu **Sub Posts** |
| **Bài viết (Post)** | Bài blog thông thường (kiến thức, tin tức) | Menu **Posts** |
| **Trang (Page)** | Trang tĩnh: Giới thiệu, Liên hệ, Chính sách... | Menu **Pages** |

---

## 7. Hai file "sửa lỗi" đặc biệt (rất quan trọng — đừng xóa)

Hai file này nằm ở `wp-content/mu-plugins/` và **tự chạy** (không cần bật). Chúng giải quyết 2 sự cố từng gặp:

**`fxt-underscore-fix.php` — Giúp Customize lưu được nội dung.**
Trước đây có một plugin (thường là plugin SEO) gây xung đột khiến trang Customize bị "đứng" giữa chừng, làm các khối ở cuối (Everything You Need, About Us, Footer) **không lưu được** khi bấm Publish. File này vá lỗi đó để mọi khối đều lưu được bình thường.

**`fxt-persistent-mods.php` — Giữ nội dung khi cập nhật theme.**
Nội dung Customize gắn với **tên thư mục theme**. Nếu mỗi lần lên phiên bản bạn đổi tên thư mục (vd `...-v2` → `...-v3`), nội dung cũ sẽ "biến mất". File này lưu mọi thiết lập vào một chỗ cố định để nội dung **tự chuyển sang phiên bản mới**.

> ⚠️ Nếu xóa 2 file này, các lỗi cũ có thể quay lại (mất nội dung khi Publish hoặc khi update).

---

## 8. Cập nhật theme mà KHÔNG mất nội dung

Cách an toàn nhất:

1. **Giữ nguyên tên thư mục theme** qua các phiên bản (chỉ tăng số `Version:` trong `style.css`). Ghi đè file trong cùng thư mục → nội dung tự giữ.
2. Đảm bảo **2 file ở mục 7** vẫn còn trong `wp-content/mu-plugins/`.
3. Trước khi update lớn, nên **sao lưu**: dùng plugin miễn phí "Customizer Export/Import" để xuất file dự phòng toàn bộ thiết lập Customize.

Sau khi cập nhật, nếu thấy nội dung chưa hiện đúng, thử **xóa cache** (plugin cache / CDN) và nhấn **Ctrl + Shift + R** trên trình duyệt.

---

## 9. Thuật ngữ dịch ra "tiếng người"

| Thuật ngữ | Hiểu đơn giản là |
|---|---|
| **Customize / Customizer** | Trang chỉnh chữ & cài đặt giao diện trong Appearance → Customize |
| **Theme** | Bộ giao diện của website |
| **Database** | Kho lưu trữ nội dung của website (bài viết, cài đặt...) |
| **Publish / Đăng** | Lưu thay đổi ra website thật |
| **Cache** | Bản lưu tạm để web tải nhanh; đôi khi phải xóa để thấy thay đổi mới |
| **Broker** | Sàn giao dịch Forex |
| **Schema / SEO** | Phần "ngầm" giúp Google hiểu và hiển thị trang đẹp hơn trên kết quả tìm kiếm |
| **mu-plugins** | Thư mục chứa các plugin tự chạy, không cần bật trong trang Plugins |
| **Permalink** | Đường dẫn (URL) của một trang/bài viết |

---

## 10. Khi gặp sự cố — kiểm tra nhanh

| Triệu chứng | Thử trước |
|---|---|
| Sửa Customize nhưng web không đổi | Đã bấm **Publish** chưa? Xóa cache? Trang chủ có đang đặt là "trang tĩnh" trong Settings → Reading? |
| Một khối trang chủ không hiện | Kiểm tra ô **"Ẩn section này"** có bị tick nhầm không |
| Trang chủ hiển thị thiếu nửa dưới | Có thể lỗi code trong file — nhờ người biết code bật **WP_DEBUG** để xem |
| Customize báo lỗi không hiện được control | Xung đột plugin (thường là plugin SEO) — tắt thử để kiểm tra |

---

*Tài liệu mô tả cấu trúc tại thời điểm hiện tại. Khi thêm tính năng mới, nên cập nhật lại file này để người sau dễ theo dõi.*

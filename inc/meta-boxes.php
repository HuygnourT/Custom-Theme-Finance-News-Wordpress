<?php
/**
 * Meta Boxes - Custom Fields cho Broker
 * 
 * FIX 1: Thêm 'classic-editor' support để Gutenberg không block meta box
 * FIX 2: Lưu pros/cons dùng sanitize_textarea_field để giữ line breaks
 * NEW: Broker Sections với TinyMCE cho content & hidden detail
 * FIX 3: KHÔNG dùng wp_editor() cho sections — render textarea thuần
 *         rồi init TinyMCE hoàn toàn bằng JS để tránh WP init conflict.
 *         Đây là fix triệt để cho lỗi Visual tab trống.
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

/**
 * Đăng ký Meta Box trong trang editor Broker
 */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'fxt_broker_details',
        'Information Broker',
        'fxt_broker_meta_box_html',
        'broker',
        'normal',
        'high'
    );

    add_meta_box(
        'fxt_broker_sections',
        '📑 Broker Content Sections (Tabs / Collapsible)',
        'fxt_broker_sections_meta_box_html',
        'broker',
        'normal',
        'default'
    );
});

/**
 * FIX: Tắt Gutenberg cho broker post type
 */
add_filter('use_block_editor_for_post_type', function ($use_block_editor, $post_type) {
    if ($post_type === 'broker') {
        return false;
    }
    return $use_block_editor;
}, 10, 2);

/**
 * Render HTML cho meta box chính (không đổi)
 */
function fxt_broker_meta_box_html($post) {
    wp_nonce_field('fxt_broker_meta', 'fxt_broker_meta_nonce');

    $fields = [
        'rating'         => get_post_meta($post->ID, '_fxt_rating', true),
        'spread'         => get_post_meta($post->ID, '_fxt_spread', true),
        'leverage'       => get_post_meta($post->ID, '_fxt_leverage', true),
        'min_deposit'    => get_post_meta($post->ID, '_fxt_min_deposit', true),
        'regulation'     => get_post_meta($post->ID, '_fxt_regulation', true),
        'founded'        => get_post_meta($post->ID, '_fxt_founded', true),
        'platforms'      => get_post_meta($post->ID, '_fxt_platforms', true),
        'affiliate_link' => get_post_meta($post->ID, '_fxt_affiliate_link', true),
        'website_url'    => get_post_meta($post->ID, '_fxt_website_url', true),
        'pros'           => get_post_meta($post->ID, '_fxt_pros', true),
        'cons'           => get_post_meta($post->ID, '_fxt_cons', true),
    ];
    ?>

    <style>
        .fxt-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .fxt-meta-field { margin-bottom: 12px; }
        .fxt-meta-field label { display: block; font-weight: 600; margin-bottom: 4px; color: #1e3a5f; }
        .fxt-meta-field input, .fxt-meta-field textarea { width: 100%; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px; }
        .fxt-meta-field input:focus, .fxt-meta-field textarea:focus { border-color: #2271b1; outline: none; box-shadow: 0 0 0 1px #2271b1; }
        .fxt-meta-section { background: #f6f7f7; padding: 15px; border-radius: 6px; margin-top: 15px; }
        .fxt-meta-section h4 { margin: 0 0 10px; color: #1e3a5f; }
        .fxt-meta-hint { font-size: 12px; color: #666; margin-top: 4px; font-style: italic; }
    </style>

    <div class="fxt-meta-grid">
        <div>
            <div class="fxt-meta-field">
                <label for="fxt_rating">⭐ Rating (0–10)</label>
                <input type="number" id="fxt_rating" name="fxt_rating"
                       value="<?php echo esc_attr($fields['rating']); ?>"
                       min="0" max="10" step="0.1" placeholder="8.5">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_spread">📊 Spread (pips)</label>
                <input type="text" id="fxt_spread" name="fxt_spread"
                       value="<?php echo esc_attr($fields['spread']); ?>"
                       placeholder="Từ 0.0 pips">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_leverage">📈 Maximum Leverage</label>
                <input type="text" id="fxt_leverage" name="fxt_leverage"
                       value="<?php echo esc_attr($fields['leverage']); ?>"
                       placeholder="1:2000">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_min_deposit">💰 Minimum Deposit</label>
                <input type="text" id="fxt_min_deposit" name="fxt_min_deposit"
                       value="<?php echo esc_attr($fields['min_deposit']); ?>"
                       placeholder="$1">
            </div>
        </div>
        <div>
            <div class="fxt-meta-field">
                <label for="fxt_regulation">🏛 Regulation</label>
                <input type="text" id="fxt_regulation" name="fxt_regulation"
                       value="<?php echo esc_attr($fields['regulation']); ?>"
                       placeholder="FCA, CySEC, ASIC...">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_founded">📅 Year Founded</label>
                <input type="text" id="fxt_founded" name="fxt_founded"
                       value="<?php echo esc_attr($fields['founded']); ?>"
                       placeholder="2008">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_platforms">🖥 Trading Platforms</label>
                <input type="text" id="fxt_platforms" name="fxt_platforms"
                       value="<?php echo esc_attr($fields['platforms']); ?>"
                       placeholder="MT4, MT5, cTrader...">
            </div>
            <div class="fxt-meta-field">
                <label for="fxt_affiliate_link">🔗 Affiliate Link</label>
                <input type="url" id="fxt_affiliate_link" name="fxt_affiliate_link"
                       value="<?php echo esc_attr($fields['affiliate_link']); ?>"
                       placeholder="https://exness.com/a/xxxxx">
            </div>
        </div>
    </div>

    <div class="fxt-meta-field">
        <label for="fxt_website_url">🌐 Official Website</label>
        <input type="url" id="fxt_website_url" name="fxt_website_url"
               value="<?php echo esc_attr($fields['website_url']); ?>"
               placeholder="https://exness.com">
    </div>

    <div class="fxt-meta-section">
        <h4>✅ Pros (one per line)</h4>
        <textarea id="fxt_pros" name="fxt_pros" rows="5"
                  placeholder="Spread thấp&#10;Rút tiền nhanh&#10;Hỗ trợ tiếng Việt"><?php echo esc_textarea($fields['pros']); ?></textarea>
        <p class="fxt-meta-hint">Press Enter to add each advantage. Each line = 1 bullet point on the website.</p>
    </div>

    <div class="fxt-meta-section">
        <h4>❌ Cons (one per line)</h4>
        <textarea id="fxt_cons" name="fxt_cons" rows="5"
                  placeholder="Phí qua đêm cao&#10;Không có bonus"><?php echo esc_textarea($fields['cons']); ?></textarea>
        <p class="fxt-meta-hint">Press Enter to add each disadvantage. Each line = 1 bullet point on the website.</p>
    </div>
    <?php
}

// ╔═══════════════════════════════════════════════════════════════╗
// ║  BROKER SECTIONS META BOX                                    ║
// ║  FIX: Dùng textarea thuần + JS init TinyMCE                 ║
// ║  KHÔNG dùng wp_editor() để tránh WP init conflict            ║
// ╚═══════════════════════════════════════════════════════════════╝

/**
 * Render Broker Sections meta box
 */
function fxt_broker_sections_meta_box_html($post) {
    $sections = get_post_meta($post->ID, '_fxt_broker_sections', true);
    if (!is_array($sections)) $sections = [];
    ?>

    <style>
        .fxt-sections-wrap { margin-top: 10px; }
        .fxt-section-item {
            background: #f9fafb; border: 1px solid #ddd; border-radius: 8px;
            padding: 20px; margin-bottom: 16px; position: relative;
        }
        .fxt-section-item.fxt-collapsed .fxt-section-body { display: none; }
        .fxt-section-header {
            display: flex; align-items: center; gap: 12px; cursor: pointer;
            user-select: none;
        }
        .fxt-section-header:hover { opacity: .85; }
        .fxt-section-number {
            background: #2271b1; color: #fff; width: 28px; height: 28px;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0;
        }
        .fxt-section-toggle {
            margin-left: auto; font-size: 18px; color: #666;
            transition: transform .2s;
        }
        .fxt-collapsed .fxt-section-toggle { transform: rotate(-90deg); }
        .fxt-remove-section {
            position: absolute; top: 12px; right: 12px; background: #d63638;
            color: #fff; border: none; border-radius: 4px; padding: 4px 10px;
            cursor: pointer; font-size: 12px; z-index: 2;
        }
        .fxt-remove-section:hover { background: #b32d2e; }
        .fxt-section-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .fxt-section-field { margin-bottom: 10px; }
        .fxt-section-field > label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 13px; color: #1e3a5f; }
        .fxt-section-field input,
        .fxt-section-field textarea,
        .fxt-section-field select { width: 100%; padding: 6px 10px; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; }
        .fxt-section-field textarea { min-height: 80px; }
        .fxt-section-field input:focus,
        .fxt-section-field textarea:focus { border-color: #2271b1; outline: none; }
        .fxt-section-proscons {
            background: #fff; border: 1px solid #e0e0e0; border-radius: 6px;
            padding: 14px; margin-top: 8px;
        }
        .fxt-section-proscons h5 { margin: 0 0 8px; font-size: 13px; color: #1e3a5f; }
        .fxt-add-section { margin-top: 12px; }
        .fxt-section-full { grid-column: 1 / -1; }
        .fxt-checkbox-field { display: flex; align-items: center; gap: 8px; margin-top: 6px; }
        .fxt-checkbox-field input[type="checkbox"] { width: auto; }
        .fxt-collapsible-box {
            margin-top: 12px; background: #fff; border: 1px solid #e0e0e0;
            border-radius: 6px; padding: 14px;
        }
        .fxt-collapsible-box h5 { margin: 0 0 8px; font-size: 13px; color: #1e3a5f; }
        .fxt-section-body { margin-top: 16px; }

        /* Editor styling */
        .fxt-editor-wrap { margin-top: 6px; }
        .fxt-editor-wrap textarea.fxt-rich-editor {
            width: 100%; min-height: 250px; padding: 10px;
            border: none; font-family: monospace; font-size: 13px; line-height: 1.6;
            display: block;
        }
        .fxt-editor-wrap textarea.fxt-rich-editor-small { min-height: 180px; }
        .fxt-editor-tabs {
            display: flex; gap: 0; margin-bottom: 0;
            position: relative; z-index: 1; background: #f0f0f1;
            border: 1px solid #ccd0d4; border-bottom: none;
            border-radius: 4px 4px 0 0; padding: 0;
        }
        .fxt-editor-tab {
            padding: 6px 14px; font-size: 12px; font-weight: 600;
            cursor: pointer; background: #e5e5e5; color: #50575e;
            border-right: 1px solid #ccd0d4; user-select: none;
            transition: background .15s;
        }
        .fxt-editor-tab:first-child { border-radius: 3px 0 0 0; }
        .fxt-editor-tab:hover { background: #f6f6f6; color: #1e3a5f; }
        .fxt-editor-tab.active { background: #fff; color: #1e3a5f; }
        .fxt-editor-container {
            border: 1px solid #ccd0d4; border-radius: 0 0 4px 4px;
            background: #fff; overflow: hidden;
        }
        .fxt-editor-container .mce-tinymce { border: none !important; box-shadow: none !important; }
        .fxt-editor-container .mce-top-part::before { box-shadow: none !important; }
    </style>

    <p style="margin-bottom:16px; color:#555;">
        Mỗi section = 1 <strong>tab ngang</strong> ở đầu trang broker. Click tab → cuộn tới nội dung.
        Section Content và Hidden Detail sử dụng <strong>Visual Editor</strong> đầy đủ (headings, lists, media, v.v.).
        <br><em>💡 Click vào tiêu đề section để thu gọn/mở rộng.</em>
    </p>

    <div class="fxt-sections-wrap" id="fxt-sections-wrap">
        <?php
        if (!empty($sections)):
            foreach ($sections as $i => $sec):
                fxt_render_section_fields($i, $sec);
            endforeach;
        endif;
        ?>
    </div>

    <button type="button" class="button button-primary fxt-add-section" id="fxt-add-section">
        ➕ Add New Section
    </button>

    <script>
    (function(){
        var wrap = document.getElementById('fxt-sections-wrap');
        var addBtn = document.getElementById('fxt-add-section');

        // ══════════════════════════════════════════════════════════════
        // TinyMCE Init — KHÔNG phụ thuộc wp_editor(), init thủ công 100%
        // ══════════════════════════════════════════════════════════════

        function initTinyMCE(editorId, height) {
            if (typeof tinymce === 'undefined') return;

            var ta = document.getElementById(editorId);
            if (!ta) return;

            // Remove instance cũ nếu có
            var existing = tinymce.get(editorId);
            if (existing) {
                existing.save();
                existing.remove();
            }

            // Lưu content từ textarea trước khi init
            var originalContent = ta.value || '';

            tinymce.init({
                selector: '#' + editorId,
                theme: 'modern',
                skin: 'lightgray',
                plugins: 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
                toolbar1: 'formatselect bold italic bullist numlist blockquote alignleft aligncenter alignright link unlink wp_more fullscreen',
                toolbar2: 'strikethrough hr forecolor pastetext removeformat charmap outdent indent undo redo wp_help',
                block_formats: 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;Preformatted=pre',
                menubar: false,
                wpautop: true,
                indent: false,
                relative_urls: false,
                remove_script_host: false,
                convert_urls: false,
                browser_spellcheck: true,
                fix_list_elements: true,
                entities: '38,amp,60,lt,62,gt',
                entity_encoding: 'raw',
                height: height || 250,
                resize: true,
                body_class: 'post-type-broker',
                verify_html: false,
                setup: function(editor) {
                    editor.on('change keyup input NodeChange', function() {
                        editor.save();
                    });

                    // ĐÂY LÀ FIX CHÍNH: Khi editor init xong, ÉP set content từ textarea
                    editor.on('init', function() {
                        if (originalContent && originalContent.trim() !== '') {
                            // Dùng setTimeout nhỏ để đảm bảo editor đã render xong
                            setTimeout(function() {
                                editor.setContent(originalContent);
                                editor.undoManager.clear();
                                editor.undoManager.add();
                            }, 50);
                        }
                    });
                }
            });
        }

        // ── Init editors trong 1 section ──
        function initSectionEditors(sectionEl) {
            var editorWraps = sectionEl.querySelectorAll('.fxt-editor-wrap');
            editorWraps.forEach(function(ew) {
                var editorId = ew.getAttribute('data-editor-id');
                if (!editorId) return;

                bindEditorTabs(ew);

                var ta = document.getElementById(editorId);
                if (ta) ta.style.display = 'none';

                var h = ew.classList.contains('fxt-editor-small') ? 180 : 250;
                initTinyMCE(editorId, h);
            });
        }

        // ── Destroy editors trong 1 section ──
        function destroySectionEditors(sectionEl) {
            if (typeof tinymce === 'undefined') return;
            sectionEl.querySelectorAll('.fxt-editor-wrap').forEach(function(ew) {
                var editorId = ew.getAttribute('data-editor-id');
                if (!editorId) return;
                var ed = tinymce.get(editorId);
                if (ed) { ed.save(); ed.remove(); }
            });
        }

        // ── Visual/Code tab switching ──
        function bindEditorTabs(container) {
            var tabs = container.querySelectorAll('.fxt-editor-tab');
            var editorId = container.getAttribute('data-editor-id');
            if (!tabs.length || !editorId) return;

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var mode = this.getAttribute('data-mode');
                    var ta = document.getElementById(editorId);

                    tabs.forEach(function(t) { t.classList.remove('active'); });
                    this.classList.add('active');

                    if (mode === 'visual') {
                        if (typeof tinymce !== 'undefined') {
                            var ed = tinymce.get(editorId);
                            if (ed) {
                                var mceWrap = container.querySelector('.mce-tinymce');
                                if (mceWrap) mceWrap.style.display = '';
                                if (ta) {
                                    ed.setContent(ta.value || '');
                                    ta.style.display = 'none';
                                }
                            } else {
                                if (ta) ta.style.display = 'none';
                                var h = container.classList.contains('fxt-editor-small') ? 180 : 250;
                                initTinyMCE(editorId, h);
                            }
                        }
                    } else {
                        // Code mode
                        if (typeof tinymce !== 'undefined') {
                            var ed = tinymce.get(editorId);
                            if (ed) {
                                ed.save();
                                var mceWrap = container.querySelector('.mce-tinymce');
                                if (mceWrap) mceWrap.style.display = 'none';
                            }
                        }
                        if (ta) {
                            ta.style.display = '';
                            ta.style.width = '100%';
                            ta.style.minHeight = container.classList.contains('fxt-editor-small') ? '180px' : '250px';
                            ta.style.padding = '10px';
                            ta.style.fontFamily = 'monospace';
                            ta.style.fontSize = '13px';
                            ta.style.border = 'none';
                        }
                    }
                });
            });
        }

        // ══════════════════════════════════════════════════════════════
        // Section management
        // ══════════════════════════════════════════════════════════════

        function getNextIndex() {
            var items = wrap.querySelectorAll('.fxt-section-item');
            var maxIdx = -1;
            items.forEach(function(item) {
                var idx = parseInt(item.getAttribute('data-index'), 10);
                if (idx > maxIdx) maxIdx = idx;
            });
            return maxIdx + 1;
        }

        function reindex() {
            wrap.querySelectorAll('.fxt-section-item').forEach(function(item, i) {
                item.querySelector('.fxt-section-number').textContent = (i + 1);
            });
        }

        // ── Add New Section (client-side, no AJAX needed) ──
        addBtn.addEventListener('click', function() {
            var idx = getNextIndex();
            var temp = document.createElement('div');
            temp.innerHTML = buildSectionHTML(idx);
            var newItem = temp.firstElementChild;
            wrap.appendChild(newItem);

            initSectionEditors(newItem);
            bindRemove(newItem);
            bindCollapse(newItem);
            reindex();
        });

        function buildSectionHTML(index) {
            var cId = 'fxt_sec_content_' + index;
            var dId = 'fxt_sec_detail_' + index;

            return '<div class="fxt-section-item" data-index="' + index + '">'
            + '<div class="fxt-section-header">'
            + '<span class="fxt-section-number">#</span>'
            + '<strong style="flex:1">New Section</strong>'
            + '<span class="fxt-section-toggle">\u25BC</span>'
            + '</div>'
            + '<button type="button" class="fxt-remove-section">\u2715 Remove</button>'
            + '<div class="fxt-section-body">'
            + '<div class="fxt-section-grid">'
            + '<div class="fxt-section-field"><label>\uD83D\uDCCC Tab Title</label>'
            + '<input type="text" name="fxt_sections[' + index + '][title]" value="" placeholder="e.g. Spreads &amp; Fees, Platforms, Safety..."></div>'
            + '<div class="fxt-section-field">'
            + '<div class="fxt-checkbox-field"><input type="checkbox" name="fxt_sections[' + index + '][show_proscons]" value="1"><label>\u2705\u274C Show Pros/Cons</label></div>'
            + '<div class="fxt-checkbox-field" style="margin-top:8px"><input type="checkbox" name="fxt_sections[' + index + '][collapsible]" value="1"><label>\uD83D\uDD3D Collapsible</label></div>'
            + '</div></div>'
            // Content editor
            + '<div class="fxt-section-field fxt-section-full"><label>\uD83D\uDCDD Section Content</label>'
            + '<div class="fxt-editor-wrap" data-editor-id="' + cId + '">'
            + '<div class="fxt-editor-tabs"><span class="fxt-editor-tab active" data-mode="visual">Visual</span><span class="fxt-editor-tab" data-mode="code">Code</span></div>'
            + '<div class="fxt-editor-container"><textarea id="' + cId + '" name="fxt_sections[' + index + '][content]" class="fxt-rich-editor" rows="12"></textarea></div>'
            + '</div></div>'
            // Pros/Cons
            + '<div class="fxt-section-proscons"><h5>Pros/Cons riêng cho section này</h5><div class="fxt-section-grid">'
            + '<div class="fxt-section-field"><label>\u2705 Pros (one per line)</label><textarea name="fxt_sections[' + index + '][pros]" rows="3" placeholder="Low spread"></textarea></div>'
            + '<div class="fxt-section-field"><label>\u274C Cons (one per line)</label><textarea name="fxt_sections[' + index + '][cons]" rows="3" placeholder="High swap fees"></textarea></div>'
            + '</div></div>'
            // Collapsible detail editor
            + '<div class="fxt-collapsible-box"><h5>\uD83D\uDD3D Collapsible Detail</h5>'
            + '<div class="fxt-section-field"><label>Hidden detail content</label>'
            + '<div class="fxt-editor-wrap fxt-editor-small" data-editor-id="' + dId + '">'
            + '<div class="fxt-editor-tabs"><span class="fxt-editor-tab active" data-mode="visual">Visual</span><span class="fxt-editor-tab" data-mode="code">Code</span></div>'
            + '<div class="fxt-editor-container"><textarea id="' + dId + '" name="fxt_sections[' + index + '][collapse_detail]" class="fxt-rich-editor fxt-rich-editor-small" rows="8"></textarea></div>'
            + '</div></div>'
            + '<div class="fxt-section-grid">'
            + '<div class="fxt-section-field"><label>Show more text</label><input type="text" name="fxt_sections[' + index + '][show_text]" value="" placeholder="Default from Customizer"></div>'
            + '<div class="fxt-section-field"><label>Show less text</label><input type="text" name="fxt_sections[' + index + '][hide_text]" value="" placeholder="Default from Customizer"></div>'
            + '</div></div>'
            + '</div></div>';
        }

        // ── Remove section ──
        function bindRemove(item) {
            var btn = item.querySelector('.fxt-remove-section');
            if (btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (confirm('Remove this section?')) {
                        destroySectionEditors(item);
                        item.remove();
                        reindex();
                    }
                });
            }
        }

        // ── Collapse/expand — reinit TinyMCE khi expand ──
        function bindCollapse(item) {
            var header = item.querySelector('.fxt-section-header');
            if (header) {
                header.addEventListener('click', function(e) {
                    if (e.target.closest('.fxt-remove-section')) return;

                    var wasCollapsed = item.classList.contains('fxt-collapsed');

                    if (!wasCollapsed) {
                        // Sắp collapse → save trước
                        destroySectionEditors(item);
                    }

                    item.classList.toggle('fxt-collapsed');

                    if (wasCollapsed) {
                        // Vừa expand → init lại editors
                        setTimeout(function() {
                            initSectionEditors(item);
                        }, 150);
                    }
                });
            }
        }

        // ── Sync trước khi submit ──
        var postForm = document.getElementById('post');
        if (postForm) {
            postForm.addEventListener('submit', function() {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }
                // Cũng save cho collapsed sections (TinyMCE đã bị remove)
                // Textarea vẫn giữ data vì tinymce.save() đã chạy trước khi collapse
            });
        }

        // ══════════════════════════════════════════════════════════════
        // INIT: Bind + init TinyMCE cho tất cả existing sections
        // ══════════════════════════════════════════════════════════════

        function initAll() {
            wrap.querySelectorAll('.fxt-section-item').forEach(function(item) {
                bindRemove(item);
                bindCollapse(item);
                if (!item.classList.contains('fxt-collapsed')) {
                    initSectionEditors(item);
                }
            });
        }

        // Đợi TinyMCE sẵn sàng
        function waitForTinyMCE(callback) {
            if (typeof tinymce !== 'undefined' && typeof tinymce.init === 'function') {
                callback();
                return;
            }
            var attempts = 0;
            var interval = setInterval(function() {
                attempts++;
                if (typeof tinymce !== 'undefined' && typeof tinymce.init === 'function') {
                    clearInterval(interval);
                    callback();
                } else if (attempts > 50) { // 5 giây
                    clearInterval(interval);
                    console.warn('FXT: TinyMCE not available after 5s');
                    // Vẫn bind remove/collapse dù không có TinyMCE
                    wrap.querySelectorAll('.fxt-section-item').forEach(function(item) {
                        bindRemove(item);
                        bindCollapse(item);
                    });
                }
            }, 100);
        }

        // Chạy sau window load + delay nhỏ
        if (document.readyState === 'complete') {
            waitForTinyMCE(function() { setTimeout(initAll, 300); });
        } else {
            window.addEventListener('load', function() {
                waitForTinyMCE(function() { setTimeout(initAll, 300); });
            });
        }

    })();
    </script>

    <?php
}

/**
 * Render fields cho 1 section — TEXTAREA THUẦN (không dùng wp_editor)
 * TinyMCE sẽ được init hoàn toàn bằng JavaScript
 */
function fxt_render_section_fields($index, $data) {
    $title           = $data['title'] ?? '';
    $content         = $data['content'] ?? '';
    $show_proscons   = !empty($data['show_proscons']) ? '1' : '';
    $pros            = $data['pros'] ?? '';
    $cons            = $data['cons'] ?? '';
    $collapsible     = !empty($data['collapsible']) ? '1' : '';
    $collapse_detail = $data['collapse_detail'] ?? '';
    $show_text       = $data['show_text'] ?? '';
    $hide_text       = $data['hide_text'] ?? '';
    $num = is_numeric($index) ? ($index + 1) : '#';

    $content_editor_id = 'fxt_sec_content_' . $index;
    $detail_editor_id  = 'fxt_sec_detail_' . $index;
    ?>
    <div class="fxt-section-item" data-index="<?php echo esc_attr($index); ?>">
        <div class="fxt-section-header">
            <span class="fxt-section-number"><?php echo $num; ?></span>
            <strong style="flex:1"><?php echo $title ? esc_html($title) : 'New Section'; ?></strong>
            <span class="fxt-section-toggle">▼</span>
        </div>
        <button type="button" class="fxt-remove-section">✕ Remove</button>

        <div class="fxt-section-body">
            <div class="fxt-section-grid">
                <div class="fxt-section-field">
                    <label>📌 Tab Title (hiển thị trên tab ngang)</label>
                    <input type="text" name="fxt_sections[<?php echo $index; ?>][title]"
                           value="<?php echo esc_attr($title); ?>"
                           placeholder="e.g. Spreads & Fees, Platforms, Safety...">
                </div>

                <div class="fxt-section-field">
                    <div class="fxt-checkbox-field">
                        <input type="checkbox" name="fxt_sections[<?php echo $index; ?>][show_proscons]" value="1"
                               <?php checked($show_proscons, '1'); ?>>
                        <label>✅❌ Show Pros/Cons for this section</label>
                    </div>
                    <div class="fxt-checkbox-field" style="margin-top:8px">
                        <input type="checkbox" name="fxt_sections[<?php echo $index; ?>][collapsible]" value="1"
                               <?php checked($collapsible, '1'); ?>>
                        <label>🔽 Make this section collapsible (show/hide detail)</label>
                    </div>
                </div>
            </div>

            <!-- Section Content — Visual/Code Editor -->
            <div class="fxt-section-field fxt-section-full">
                <label>📝 Section Content</label>
                <div class="fxt-editor-wrap" data-editor-id="<?php echo esc_attr($content_editor_id); ?>">
                    <div class="fxt-editor-tabs">
                        <span class="fxt-editor-tab active" data-mode="visual">Visual</span>
                        <span class="fxt-editor-tab" data-mode="code">Code</span>
                    </div>
                    <div class="fxt-editor-container">
                        <textarea id="<?php echo esc_attr($content_editor_id); ?>"
                                  name="fxt_sections[<?php echo $index; ?>][content]"
                                  class="fxt-rich-editor"
                                  rows="12"><?php echo esc_textarea($content); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Per-section Pros/Cons -->
            <div class="fxt-section-proscons">
                <h5>Pros/Cons riêng cho section này (chỉ hiện khi bật checkbox ở trên)</h5>
                <div class="fxt-section-grid">
                    <div class="fxt-section-field">
                        <label>✅ Pros (one per line)</label>
                        <textarea name="fxt_sections[<?php echo $index; ?>][pros]" rows="3"
                                  placeholder="Low spread&#10;Fast execution"><?php echo esc_textarea($pros); ?></textarea>
                    </div>
                    <div class="fxt-section-field">
                        <label>❌ Cons (one per line)</label>
                        <textarea name="fxt_sections[<?php echo $index; ?>][cons]" rows="3"
                                  placeholder="High swap fees&#10;Limited tools"><?php echo esc_textarea($cons); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Collapsible Detail — Visual/Code Editor -->
            <div class="fxt-collapsible-box">
                <h5>🔽 Collapsible Detail (chỉ hiện khi bật checkbox)</h5>
                <div class="fxt-section-field">
                    <label>Hidden detail content (hiện khi click "Show more")</label>
                    <div class="fxt-editor-wrap fxt-editor-small" data-editor-id="<?php echo esc_attr($detail_editor_id); ?>">
                        <div class="fxt-editor-tabs">
                            <span class="fxt-editor-tab active" data-mode="visual">Visual</span>
                            <span class="fxt-editor-tab" data-mode="code">Code</span>
                        </div>
                        <div class="fxt-editor-container">
                            <textarea id="<?php echo esc_attr($detail_editor_id); ?>"
                                      name="fxt_sections[<?php echo $index; ?>][collapse_detail]"
                                      class="fxt-rich-editor fxt-rich-editor-small"
                                      rows="8"><?php echo esc_textarea($collapse_detail); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="fxt-section-grid">
                    <div class="fxt-section-field">
                        <label>Text "Show more" button</label>
                        <input type="text" name="fxt_sections[<?php echo $index; ?>][show_text]"
                               value="<?php echo esc_attr($show_text); ?>"
                               placeholder="Leave empty to use default from Customizer">
                    </div>
                    <div class="fxt-section-field">
                        <label>Text "Show less" button</label>
                        <input type="text" name="fxt_sections[<?php echo $index; ?>][hide_text]"
                               value="<?php echo esc_attr($hide_text); ?>"
                               placeholder="Leave empty to use default from Customizer">
                    </div>
                </div>
            </div>

        </div><!-- .fxt-section-body -->
    </div>
    <?php
}

// ╔═══════════════════════════════════════════════════════════════╗
// ║  AJAX Handler — giữ backward compatible                      ║
// ╚═══════════════════════════════════════════════════════════════╝

add_action('wp_ajax_fxt_add_broker_section', function () {
    check_ajax_referer('fxt_add_section_nonce', 'nonce');
    if (!current_user_can('edit_posts')) wp_die('Unauthorized');
    $index = intval($_POST['index'] ?? 0);
    ob_start();
    fxt_render_section_fields($index, []);
    echo ob_get_clean();
    wp_die();
});

// ╔═══════════════════════════════════════════════════════════════╗
// ║  SAVE META DATA                                              ║
// ╚═══════════════════════════════════════════════════════════════╝

add_action('save_post_broker', function ($post_id) {

    if (!isset($_POST['fxt_broker_meta_nonce']) ||
        !wp_verify_nonce($_POST['fxt_broker_meta_nonce'], 'fxt_broker_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $text_fields = [
        'fxt_rating'      => '_fxt_rating',
        'fxt_spread'      => '_fxt_spread',
        'fxt_leverage'    => '_fxt_leverage',
        'fxt_min_deposit' => '_fxt_min_deposit',
        'fxt_regulation'  => '_fxt_regulation',
        'fxt_founded'     => '_fxt_founded',
        'fxt_platforms'   => '_fxt_platforms',
    ];
    $textarea_fields = [
        'fxt_pros' => '_fxt_pros',
        'fxt_cons' => '_fxt_cons',
    ];
    $url_fields = [
        'fxt_affiliate_link' => '_fxt_affiliate_link',
        'fxt_website_url'    => '_fxt_website_url',
    ];

    foreach ($text_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_key]));
        }
    }
    foreach ($textarea_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, sanitize_textarea_field($_POST[$form_key]));
        }
    }
    foreach ($url_fields as $form_key => $meta_key) {
        if (isset($_POST[$form_key])) {
            update_post_meta($post_id, $meta_key, esc_url_raw($_POST[$form_key]));
        }
    }

    // === Save Broker Sections ===
    if (isset($_POST['fxt_sections']) && is_array($_POST['fxt_sections'])) {
        $sections = [];
        foreach ($_POST['fxt_sections'] as $sec) {
            if (empty($sec['title']) && empty($sec['content'])) continue;

            $sections[] = [
                'title'           => sanitize_text_field($sec['title'] ?? ''),
                'content'         => wp_kses_post($sec['content'] ?? ''),
                'show_proscons'   => !empty($sec['show_proscons']) ? '1' : '',
                'pros'            => sanitize_textarea_field($sec['pros'] ?? ''),
                'cons'            => sanitize_textarea_field($sec['cons'] ?? ''),
                'collapsible'     => !empty($sec['collapsible']) ? '1' : '',
                'collapse_detail' => wp_kses_post($sec['collapse_detail'] ?? ''),
                'show_text'       => sanitize_text_field($sec['show_text'] ?? ''),
                'hide_text'       => sanitize_text_field($sec['hide_text'] ?? ''),
            ];
        }
        update_post_meta($post_id, '_fxt_broker_sections', $sections);
    } else {
        delete_post_meta($post_id, '_fxt_broker_sections');
    }
});

/**
 * Helper: Lấy tất cả meta data của broker
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
        'pros'           => array_filter(array_map('trim', explode("\n", get_post_meta($post_id, '_fxt_pros', true) ?: ''))),
        'cons'           => array_filter(array_map('trim', explode("\n", get_post_meta($post_id, '_fxt_cons', true) ?: ''))),
    ];
}

/**
 * Helper: Lấy broker sections
 */
function fxt_get_broker_sections($post_id) {
    $sections = get_post_meta($post_id, '_fxt_broker_sections', true);
    if (!is_array($sections)) return [];

    foreach ($sections as &$sec) {
        $sec['pros_arr'] = array_filter(array_map('trim', explode("\n", $sec['pros'] ?? '')));
        $sec['cons_arr'] = array_filter(array_map('trim', explode("\n", $sec['cons'] ?? '')));
    }
    return $sections;
}

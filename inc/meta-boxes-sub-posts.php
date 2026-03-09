<?php
/**
 * Meta Boxes cho Sub Posts (broker_post + generic_post)
 * 
 * Tính năng:
 * - CTA Buttons (nhiều nút, tùy chỉnh style, URL, new tab)
 * - Pros & Cons
 * - Collapsible Sections (title, content, CTA, pros/cons, hidden detail)
 * 
 * @package FXTradingToday
 */

if (!defined('ABSPATH')) exit;

/**
 * Đăng ký meta boxes cho cả broker_post và generic_post
 */
add_action('add_meta_boxes', function () {
    $post_types = ['broker_post', 'generic_post'];

    foreach ($post_types as $pt) {
        add_meta_box(
            'fxt_sub_cta_buttons',
            '🔘 CTA Buttons',
            'fxt_sub_cta_buttons_html',
            $pt,
            'normal',
            'high'
        );

        add_meta_box(
            'fxt_sub_pros_cons',
            '✅❌ Pros & Cons',
            'fxt_sub_pros_cons_html',
            $pt,
            'normal',
            'high'
        );

        add_meta_box(
            'fxt_sub_sections',
            '📑 Content Sections (Collapsible / CTA / Pros-Cons)',
            'fxt_sub_sections_html',
            $pt,
            'normal',
            'default'
        );
    }
});

// ╔═══════════════════════════════════════════════════════════════╗
// ║  CTA BUTTONS META BOX                                        ║
// ╚═══════════════════════════════════════════════════════════════╝

function fxt_sub_cta_buttons_html($post) {
    wp_nonce_field('fxt_sub_post_meta', 'fxt_sub_post_meta_nonce');
    $buttons = get_post_meta($post->ID, '_fxt_sub_cta_buttons', true);
    if (!is_array($buttons)) $buttons = [];
    ?>
    <style>
        .fxt-cta-list { margin-bottom: 12px; }
        .fxt-cta-item { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; padding: 10px; background: #f9fafb; border: 1px solid #e0e0e0; border-radius: 6px; flex-wrap: wrap; }
        .fxt-cta-item input[type="text"], .fxt-cta-item input[type="url"] { flex: 1; min-width: 150px; padding: 6px 10px; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; }
        .fxt-cta-item select { padding: 6px 10px; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; }
        .fxt-cta-item label { font-size: 12px; display: flex; align-items: center; gap: 4px; }
        .fxt-cta-remove { background: #d63638; color: #fff; border: none; border-radius: 4px; padding: 4px 10px; cursor: pointer; font-size: 12px; }
        .fxt-cta-remove:hover { background: #b32d2e; }
    </style>

    <p style="margin-bottom:12px; color:#555; font-size:13px;">Thêm các nút CTA hiển thị ở đầu bài viết. Có thể thêm nhiều nút với style khác nhau.</p>

    <div class="fxt-cta-list" id="fxt-cta-list">
        <?php foreach ($buttons as $i => $btn): ?>
        <div class="fxt-cta-item">
            <input type="text" name="fxt_sub_cta[<?php echo $i; ?>][text]" value="<?php echo esc_attr($btn['text'] ?? ''); ?>" placeholder="Button text (e.g. Open Account)">
            <input type="url" name="fxt_sub_cta[<?php echo $i; ?>][url]" value="<?php echo esc_attr($btn['url'] ?? ''); ?>" placeholder="URL (https://...)">
            <select name="fxt_sub_cta[<?php echo $i; ?>][style]">
                <option value="primary" <?php selected($btn['style'] ?? '', 'primary'); ?>>Primary (Blue)</option>
                <option value="cta" <?php selected($btn['style'] ?? '', 'cta'); ?>>CTA (Orange)</option>
                <option value="outline" <?php selected($btn['style'] ?? '', 'outline'); ?>>Outline</option>
            </select>
            <label><input type="checkbox" name="fxt_sub_cta[<?php echo $i; ?>][new_tab]" value="1" <?php checked(!empty($btn['new_tab'])); ?>> New tab</label>
            <button type="button" class="fxt-cta-remove" onclick="this.closest('.fxt-cta-item').remove()">✕</button>
        </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="button" id="fxt-add-cta-btn">➕ Add CTA Button</button>

    <script>
    (function(){
        var list = document.getElementById('fxt-cta-list');
        var addBtn = document.getElementById('fxt-add-cta-btn');
        var idx = <?php echo count($buttons); ?>;

        addBtn.addEventListener('click', function() {
            var html = '<div class="fxt-cta-item">'
                + '<input type="text" name="fxt_sub_cta[' + idx + '][text]" placeholder="Button text">'
                + '<input type="url" name="fxt_sub_cta[' + idx + '][url]" placeholder="URL (https://...)">'
                + '<select name="fxt_sub_cta[' + idx + '][style]"><option value="primary">Primary</option><option value="cta">CTA (Orange)</option><option value="outline">Outline</option></select>'
                + '<label><input type="checkbox" name="fxt_sub_cta[' + idx + '][new_tab]" value="1"> New tab</label>'
                + '<button type="button" class="fxt-cta-remove" onclick="this.closest(\'.fxt-cta-item\').remove()">✕</button>'
                + '</div>';
            list.insertAdjacentHTML('beforeend', html);
            idx++;
        });
    })();
    </script>
    <?php
}

// ╔═══════════════════════════════════════════════════════════════╗
// ║  PROS & CONS META BOX                                        ║
// ╚═══════════════════════════════════════════════════════════════╝

function fxt_sub_pros_cons_html($post) {
    $pros = get_post_meta($post->ID, '_fxt_sub_pros', true);
    $cons = get_post_meta($post->ID, '_fxt_sub_cons', true);
    ?>
    <style>
        .fxt-proscons-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .fxt-proscons-grid label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #1e3a5f; }
        .fxt-proscons-grid textarea { width: 100%; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; }
        .fxt-proscons-hint { font-size: 11px; color: #888; margin-top: 4px; font-style: italic; }
    </style>
    <div class="fxt-proscons-grid">
        <div>
            <label>✅ Pros (mỗi dòng = 1 ưu điểm)</label>
            <textarea name="fxt_sub_pros" rows="6" placeholder="Low spread&#10;Fast withdrawal"><?php echo esc_textarea($pros); ?></textarea>
            <p class="fxt-proscons-hint">Enter mỗi dòng. Hiển thị dạng bullet points.</p>
        </div>
        <div>
            <label>❌ Cons (mỗi dòng = 1 nhược điểm)</label>
            <textarea name="fxt_sub_cons" rows="6" placeholder="High swap fees&#10;Limited tools"><?php echo esc_textarea($cons); ?></textarea>
            <p class="fxt-proscons-hint">Enter mỗi dòng. Hiển thị dạng bullet points.</p>
        </div>
    </div>
    <?php
}

// ╔═══════════════════════════════════════════════════════════════╗
// ║  COLLAPSIBLE SECTIONS META BOX                               ║
// ╚═══════════════════════════════════════════════════════════════╝

function fxt_sub_sections_html($post) {
    $sections = get_post_meta($post->ID, '_fxt_sub_sections', true);
    if (!is_array($sections)) $sections = [];
    ?>

    <style>
        .fxt-sub-sections-wrap { margin-top: 10px; }
        .fxt-sub-sec-item {
            background: #f9fafb; border: 1px solid #ddd; border-radius: 8px;
            padding: 20px; margin-bottom: 16px; position: relative;
        }
        .fxt-sub-sec-item.fxt-collapsed .fxt-sub-sec-body { display: none; }
        .fxt-sub-sec-header {
            display: flex; align-items: center; gap: 12px; cursor: pointer; user-select: none;
        }
        .fxt-sub-sec-number {
            background: #2271b1; color: #fff; width: 28px; height: 28px;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0;
        }
        .fxt-sub-sec-toggle { margin-left: auto; font-size: 18px; color: #666; transition: transform .2s; }
        .fxt-collapsed .fxt-sub-sec-toggle { transform: rotate(-90deg); }
        .fxt-sub-sec-remove {
            position: absolute; top: 12px; right: 12px; background: #d63638;
            color: #fff; border: none; border-radius: 4px; padding: 4px 10px;
            cursor: pointer; font-size: 12px; z-index: 2;
        }
        .fxt-sub-sec-field { margin-bottom: 10px; }
        .fxt-sub-sec-field > label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 13px; color: #1e3a5f; }
        .fxt-sub-sec-field input, .fxt-sub-sec-field textarea, .fxt-sub-sec-field select { width: 100%; padding: 6px 10px; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; }
        .fxt-sub-sec-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .fxt-sub-sec-body { margin-top: 16px; }
        .fxt-checkbox-inline { display: flex; align-items: center; gap: 8px; margin-top: 6px; }
        .fxt-checkbox-inline input[type="checkbox"] { width: auto; }
        .fxt-sub-sec-cta-list { margin: 8px 0; }
        .fxt-sub-sec-cta-item { display: flex; gap: 6px; align-items: center; margin-bottom: 6px; }
        .fxt-sub-sec-cta-item input, .fxt-sub-sec-cta-item select { font-size: 12px; padding: 4px 8px; }
        .fxt-sub-sec-cta-remove { background: #d63638; color: #fff; border: none; padding: 2px 8px; border-radius: 3px; cursor: pointer; font-size: 11px; }
    </style>

    <p style="margin-bottom:16px; color:#555; font-size:13px;">
        Mỗi section có thể chứa: title, rich content (wp_editor), CTA buttons, Pros/Cons, và collapsible hidden detail.
        <br><em>💡 Click tiêu đề section để thu gọn/mở rộng.</em>
    </p>

    <div class="fxt-sub-sections-wrap" id="fxt-sub-sections-wrap">
        <?php foreach ($sections as $i => $sec):
            fxt_render_sub_section_fields($i, $sec);
        endforeach; ?>
    </div>

    <button type="button" class="button button-primary" id="fxt-add-sub-section">➕ Add New Section</button>

    <script>
    (function(){
        var wrap = document.getElementById('fxt-sub-sections-wrap');
        var addBtn = document.getElementById('fxt-add-sub-section');

        function getNextIndex() {
            var items = wrap.querySelectorAll('.fxt-sub-sec-item');
            var max = -1;
            items.forEach(function(item) {
                var idx = parseInt(item.getAttribute('data-index'), 10);
                if (idx > max) max = idx;
            });
            return max + 1;
        }

        function reindex() {
            wrap.querySelectorAll('.fxt-sub-sec-item').forEach(function(item, vi) {
                item.querySelector('.fxt-sub-sec-number').textContent = (vi + 1);
            });
        }

        addBtn.addEventListener('click', function() {
            var idx = getNextIndex();
            addBtn.disabled = true;
            addBtn.textContent = '⏳ Loading editor...';

            var formData = new FormData();
            formData.append('action', 'fxt_add_sub_section');
            formData.append('index', idx);
            formData.append('nonce', '<?php echo wp_create_nonce('fxt_add_sub_section_nonce'); ?>');

            fetch(ajaxurl, { method: 'POST', body: formData })
            .then(function(res) { return res.text(); })
            .then(function(html) {
                wrap.insertAdjacentHTML('beforeend', html);
                var newItem = wrap.querySelector('.fxt-sub-sec-item[data-index="' + idx + '"]');
                if (newItem) {
                    initSubEditors(newItem);
                    bindSubRemove(newItem);
                    bindSubCollapse(newItem);
                    bindSubCTA(newItem);
                }
                reindex();
                addBtn.disabled = false;
                addBtn.textContent = '➕ Add New Section';
            })
            .catch(function(err) {
                console.error(err);
                addBtn.disabled = false;
                addBtn.textContent = '➕ Add New Section';
                alert('Error adding section.');
            });
        });

        function initSubEditors(sectionEl) {
            sectionEl.querySelectorAll('.fxt-sub-editor-area').forEach(function(ta) {
                var editorId = ta.id;
                if (!editorId || typeof tinymce === 'undefined') return;

                var settings = {
                    selector: '#' + editorId,
                    theme: 'modern',
                    skin: 'lightgray',
                    plugins: 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
                    toolbar1: 'formatselect bold italic bullist numlist blockquote alignleft aligncenter alignright link unlink fullscreen',
                    toolbar2: 'strikethrough hr forecolor pastetext removeformat charmap outdent indent undo redo',
                    block_formats: 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;Preformatted=pre',
                    menubar: false, wpautop: true, indent: false,
                    relative_urls: false, remove_script_host: false, convert_urls: false,
                    height: 200, resize: true,
                    setup: function(editor) {
                        editor.on('change keyup', function() { editor.save(); });
                    }
                };

                if (typeof tinyMCEPreInit !== 'undefined' && tinyMCEPreInit.mceInit) {
                    var keys = Object.keys(tinyMCEPreInit.mceInit);
                    if (keys.length > 0) {
                        var ref = tinyMCEPreInit.mceInit[keys[0]];
                        for (var k in ref) {
                            if (k !== 'selector' && k !== 'elements' && k !== 'height' && k !== 'setup') settings[k] = ref[k];
                        }
                        settings.selector = '#' + editorId;
                        settings.height = 200;
                        settings.setup = function(editor) { editor.on('change keyup', function() { editor.save(); }); };
                    }
                }
                tinymce.init(settings);

                if (typeof quicktags !== 'undefined') {
                    try { quicktags({ id: editorId }); QTags._buttonsInit(); } catch(e) {}
                }
            });
        }

        function bindSubRemove(item) {
            var btn = item.querySelector('.fxt-sub-sec-remove');
            if (btn) btn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (confirm('Remove this section?')) {
                    item.querySelectorAll('.fxt-sub-editor-area').forEach(function(ta) {
                        if (typeof tinymce !== 'undefined' && tinymce.get(ta.id)) tinymce.get(ta.id).remove();
                    });
                    item.remove();
                    reindex();
                }
            });
        }

        function bindSubCollapse(item) {
            var header = item.querySelector('.fxt-sub-sec-header');
            if (header) header.addEventListener('click', function(e) {
                if (e.target.closest('.fxt-sub-sec-remove')) return;
                item.classList.toggle('fxt-collapsed');
            });
        }

        function bindSubCTA(item) {
            var addCtaBtn = item.querySelector('.fxt-sub-sec-add-cta');
            if (!addCtaBtn) return;
            addCtaBtn.addEventListener('click', function() {
                var list = item.querySelector('.fxt-sub-sec-cta-list');
                var idx = item.getAttribute('data-index');
                var ci = list.querySelectorAll('.fxt-sub-sec-cta-item').length;
                var html = '<div class="fxt-sub-sec-cta-item">'
                    + '<input type="text" name="fxt_sub_sections[' + idx + '][cta_buttons][' + ci + '][text]" placeholder="Button text" style="flex:1">'
                    + '<input type="url" name="fxt_sub_sections[' + idx + '][cta_buttons][' + ci + '][url]" placeholder="URL" style="flex:1">'
                    + '<select name="fxt_sub_sections[' + idx + '][cta_buttons][' + ci + '][style]"><option value="primary">Primary</option><option value="cta">CTA</option><option value="outline">Outline</option></select>'
                    + '<label style="font-size:11px"><input type="checkbox" name="fxt_sub_sections[' + idx + '][cta_buttons][' + ci + '][new_tab]" value="1" style="width:auto"> New tab</label>'
                    + '<button type="button" class="fxt-sub-sec-cta-remove" onclick="this.closest(\'.fxt-sub-sec-cta-item\').remove()">✕</button>'
                    + '</div>';
                list.insertAdjacentHTML('beforeend', html);
            });
        }

        // Submit: save TinyMCE content
        var postForm = document.getElementById('post');
        if (postForm) postForm.addEventListener('submit', function() {
            if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        });

        // Init existing sections
        wrap.querySelectorAll('.fxt-sub-sec-item').forEach(function(item) {
            bindSubRemove(item);
            bindSubCollapse(item);
            bindSubCTA(item);
        });
    })();
    </script>
    <?php
}

/**
 * Render 1 section
 */
function fxt_render_sub_section_fields($index, $data) {
    $title           = $data['title'] ?? '';
    $content         = $data['content'] ?? '';
    $show_proscons   = !empty($data['show_proscons']) ? '1' : '';
    $pros            = $data['pros'] ?? '';
    $cons            = $data['cons'] ?? '';
    $collapsible     = !empty($data['collapsible']) ? '1' : '';
    $collapse_detail = $data['collapse_detail'] ?? '';
    $show_text       = $data['show_text'] ?? '';
    $hide_text       = $data['hide_text'] ?? '';
    $cta_buttons     = $data['cta_buttons'] ?? [];
    if (!is_array($cta_buttons)) $cta_buttons = [];
    $num = is_numeric($index) ? ($index + 1) : '#';

    $content_id = 'fxt_sub_sec_content_' . $index;
    $detail_id  = 'fxt_sub_sec_detail_' . $index;
    ?>
    <div class="fxt-sub-sec-item" data-index="<?php echo esc_attr($index); ?>">
        <div class="fxt-sub-sec-header">
            <span class="fxt-sub-sec-number"><?php echo $num; ?></span>
            <strong style="flex:1"><?php echo $title ? esc_html($title) : 'New Section'; ?></strong>
            <span class="fxt-sub-sec-toggle">▼</span>
        </div>
        <button type="button" class="fxt-sub-sec-remove">✕ Remove</button>

        <div class="fxt-sub-sec-body">
            <div class="fxt-sub-sec-grid">
                <div class="fxt-sub-sec-field">
                    <label>📌 Section Title</label>
                    <input type="text" name="fxt_sub_sections[<?php echo $index; ?>][title]"
                           value="<?php echo esc_attr($title); ?>" placeholder="e.g. Spreads & Fees">
                </div>
                <div class="fxt-sub-sec-field">
                    <div class="fxt-checkbox-inline">
                        <input type="checkbox" name="fxt_sub_sections[<?php echo $index; ?>][show_proscons]" value="1" <?php checked($show_proscons, '1'); ?>>
                        <label>✅❌ Show Pros/Cons</label>
                    </div>
                    <div class="fxt-checkbox-inline" style="margin-top:8px">
                        <input type="checkbox" name="fxt_sub_sections[<?php echo $index; ?>][collapsible]" value="1" <?php checked($collapsible, '1'); ?>>
                        <label>🔽 Collapsible detail</label>
                    </div>
                </div>
            </div>

            <!-- Section Content (wp_editor) -->
            <div class="fxt-sub-sec-field" style="margin-top:12px">
                <label>📝 Section Content</label>
                <div class="fxt-editor-wrap">
                    <?php wp_editor($content, $content_id, [
                        'textarea_name' => 'fxt_sub_sections[' . $index . '][content]',
                        'textarea_rows' => 8,
                        'media_buttons' => true,
                        'teeny'         => false,
                        'quicktags'     => true,
                        'tinymce'       => [
                            'toolbar1' => 'formatselect bold italic bullist numlist blockquote alignleft aligncenter alignright link unlink fullscreen',
                            'toolbar2' => 'strikethrough hr forecolor pastetext removeformat charmap outdent indent undo redo',
                            'height'   => 200,
                        ],
                    ]); ?>
                </div>
            </div>

            <!-- Section CTA Buttons -->
            <div class="fxt-sub-sec-field" style="margin-top:12px; background:#fff; padding:12px; border:1px solid #e0e0e0; border-radius:6px;">
                <label>🔘 Section CTA Buttons</label>
                <div class="fxt-sub-sec-cta-list">
                    <?php foreach ($cta_buttons as $ci => $cta): ?>
                    <div class="fxt-sub-sec-cta-item">
                        <input type="text" name="fxt_sub_sections[<?php echo $index; ?>][cta_buttons][<?php echo $ci; ?>][text]" value="<?php echo esc_attr($cta['text'] ?? ''); ?>" placeholder="Button text" style="flex:1">
                        <input type="url" name="fxt_sub_sections[<?php echo $index; ?>][cta_buttons][<?php echo $ci; ?>][url]" value="<?php echo esc_attr($cta['url'] ?? ''); ?>" placeholder="URL" style="flex:1">
                        <select name="fxt_sub_sections[<?php echo $index; ?>][cta_buttons][<?php echo $ci; ?>][style]">
                            <option value="primary" <?php selected($cta['style'] ?? '', 'primary'); ?>>Primary</option>
                            <option value="cta" <?php selected($cta['style'] ?? '', 'cta'); ?>>CTA</option>
                            <option value="outline" <?php selected($cta['style'] ?? '', 'outline'); ?>>Outline</option>
                        </select>
                        <label style="font-size:11px"><input type="checkbox" name="fxt_sub_sections[<?php echo $index; ?>][cta_buttons][<?php echo $ci; ?>][new_tab]" value="1" <?php checked(!empty($cta['new_tab'])); ?> style="width:auto"> New tab</label>
                        <button type="button" class="fxt-sub-sec-cta-remove" onclick="this.closest('.fxt-sub-sec-cta-item').remove()">✕</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button fxt-sub-sec-add-cta">➕ Add CTA</button>
            </div>

            <!-- Pros/Cons -->
            <div class="fxt-sub-sec-grid" style="margin-top:12px; background:#fff; padding:12px; border:1px solid #e0e0e0; border-radius:6px;">
                <div class="fxt-sub-sec-field">
                    <label>✅ Pros (one per line)</label>
                    <textarea name="fxt_sub_sections[<?php echo $index; ?>][pros]" rows="3" placeholder="Low spread&#10;Fast execution"><?php echo esc_textarea($pros); ?></textarea>
                </div>
                <div class="fxt-sub-sec-field">
                    <label>❌ Cons (one per line)</label>
                    <textarea name="fxt_sub_sections[<?php echo $index; ?>][cons]" rows="3" placeholder="High fees&#10;Limited tools"><?php echo esc_textarea($cons); ?></textarea>
                </div>
            </div>

            <!-- Collapsible Detail -->
            <div style="margin-top:12px; background:#fff; padding:12px; border:1px solid #e0e0e0; border-radius:6px;">
                <label style="font-weight:600; font-size:13px; color:#1e3a5f; display:block; margin-bottom:8px;">🔽 Hidden Detail (collapsible)</label>
                <div class="fxt-editor-wrap">
                    <?php wp_editor($collapse_detail, $detail_id, [
                        'textarea_name' => 'fxt_sub_sections[' . $index . '][collapse_detail]',
                        'textarea_rows' => 6,
                        'media_buttons' => true,
                        'teeny'         => false,
                        'quicktags'     => true,
                        'tinymce'       => [
                            'toolbar1' => 'formatselect bold italic bullist numlist blockquote link unlink fullscreen',
                            'toolbar2' => 'strikethrough hr forecolor pastetext removeformat undo redo',
                            'height'   => 180,
                        ],
                    ]); ?>
                </div>
                <div class="fxt-sub-sec-grid" style="margin-top:8px">
                    <div class="fxt-sub-sec-field">
                        <label>Text "Show more"</label>
                        <input type="text" name="fxt_sub_sections[<?php echo $index; ?>][show_text]" value="<?php echo esc_attr($show_text); ?>" placeholder="Default from Customizer">
                    </div>
                    <div class="fxt-sub-sec-field">
                        <label>Text "Show less"</label>
                        <input type="text" name="fxt_sub_sections[<?php echo $index; ?>][hide_text]" value="<?php echo esc_attr($hide_text); ?>" placeholder="Default from Customizer">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * AJAX: Add new sub section
 */
add_action('wp_ajax_fxt_add_sub_section', function () {
    check_ajax_referer('fxt_add_sub_section_nonce', 'nonce');
    if (!current_user_can('edit_posts')) wp_die('Unauthorized');

    $index = intval($_POST['index'] ?? 0);
    ob_start();
    fxt_render_sub_section_fields($index, []);
    echo ob_get_clean();
    wp_die();
});

/**
 * SAVE: Sub post meta data (CTA, Pros/Cons, Sections)
 */
function fxt_save_sub_post_meta($post_id) {
    if (!isset($_POST['fxt_sub_post_meta_nonce']) ||
        !wp_verify_nonce($_POST['fxt_sub_post_meta_nonce'], 'fxt_sub_post_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // CTA Buttons
    $cta_buttons = [];
    if (isset($_POST['fxt_sub_cta']) && is_array($_POST['fxt_sub_cta'])) {
        foreach ($_POST['fxt_sub_cta'] as $btn) {
            if (empty($btn['text']) && empty($btn['url'])) continue;
            $cta_buttons[] = [
                'text'    => sanitize_text_field($btn['text'] ?? ''),
                'url'     => esc_url_raw($btn['url'] ?? ''),
                'style'   => sanitize_text_field($btn['style'] ?? 'primary'),
                'new_tab' => !empty($btn['new_tab']) ? '1' : '',
            ];
        }
    }
    update_post_meta($post_id, '_fxt_sub_cta_buttons', $cta_buttons);

    // Pros/Cons
    if (isset($_POST['fxt_sub_pros'])) {
        update_post_meta($post_id, '_fxt_sub_pros', sanitize_textarea_field($_POST['fxt_sub_pros']));
    }
    if (isset($_POST['fxt_sub_cons'])) {
        update_post_meta($post_id, '_fxt_sub_cons', sanitize_textarea_field($_POST['fxt_sub_cons']));
    }

    // Sections
    if (isset($_POST['fxt_sub_sections']) && is_array($_POST['fxt_sub_sections'])) {
        $sections = [];
        foreach ($_POST['fxt_sub_sections'] as $sec) {
            if (empty($sec['title']) && empty($sec['content'])) continue;

            $cta_btns = [];
            if (!empty($sec['cta_buttons']) && is_array($sec['cta_buttons'])) {
                foreach ($sec['cta_buttons'] as $cb) {
                    if (empty($cb['text']) && empty($cb['url'])) continue;
                    $cta_btns[] = [
                        'text'    => sanitize_text_field($cb['text'] ?? ''),
                        'url'     => esc_url_raw($cb['url'] ?? ''),
                        'style'   => sanitize_text_field($cb['style'] ?? 'primary'),
                        'new_tab' => !empty($cb['new_tab']) ? '1' : '',
                    ];
                }
            }

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
                'cta_buttons'     => $cta_btns,
            ];
        }
        update_post_meta($post_id, '_fxt_sub_sections', $sections);
    } else {
        delete_post_meta($post_id, '_fxt_sub_sections');
    }
}

// Hook save cho cả 2 post types
add_action('save_post_broker_post', 'fxt_save_sub_post_meta');
add_action('save_post_generic_post', 'fxt_save_sub_post_meta');

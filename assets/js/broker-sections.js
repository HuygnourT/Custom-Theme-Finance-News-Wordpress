/**
 * FX Trading Today - Broker Sections
 * Tab navigation + Collapsible details
 * 
 * @package FXTradingToday
 */

document.addEventListener('DOMContentLoaded', function () {

    // ═══ TAB NAVIGATION (smooth scroll + active state) ═══
    var tabs = document.querySelectorAll('.broker-tab');
    var sections = document.querySelectorAll('.broker-section');

    if (tabs.length > 0 && sections.length > 0) {

        // Click tab → scroll to section
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                var targetId = this.getAttribute('data-tab');
                var target = document.getElementById(targetId);
                if (!target) return;

                // Scroll to section
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });

                // Update active tab
                tabs.forEach(function (t) { t.classList.remove('active'); });
                this.classList.add('active');
            });
        });

        // Scroll spy — update active tab based on scroll position
        var ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    updateActiveTab();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        function updateActiveTab() {
            var scrollPos = window.scrollY;
            var headerH = document.querySelector('.site-header') ? document.querySelector('.site-header').offsetHeight : 64;
            var tabsH = document.querySelector('.broker-tabs-wrapper') ? document.querySelector('.broker-tabs-wrapper').offsetHeight : 50;
            var offset = headerH + tabsH + 40;
            var activeId = null;

            sections.forEach(function (sec) {
                var rect = sec.getBoundingClientRect();
                var top = rect.top + scrollPos - offset;
                if (scrollPos >= top) {
                    activeId = sec.id;
                }
            });

            if (activeId) {
                tabs.forEach(function (tab) {
                    if (tab.getAttribute('data-tab') === activeId) {
                        tab.classList.add('active');
                    } else {
                        tab.classList.remove('active');
                    }
                });

                // Scroll active tab into view in the tab bar
                var activeTab = document.querySelector('.broker-tab.active');
                if (activeTab) {
                    var tabsContainer = document.querySelector('.broker-tabs');
                    if (tabsContainer) {
                        var tabLeft = activeTab.offsetLeft;
                        var tabWidth = activeTab.offsetWidth;
                        var containerWidth = tabsContainer.offsetWidth;
                        var scrollLeft = tabsContainer.scrollLeft;

                        if (tabLeft < scrollLeft || tabLeft + tabWidth > scrollLeft + containerWidth) {
                            tabsContainer.scrollTo({
                                left: tabLeft - containerWidth / 2 + tabWidth / 2,
                                behavior: 'smooth'
                            });
                        }
                    }
                }
            }
        }
    }

    // ═══ COLLAPSIBLE SECTIONS (show/hide detail) ═══
    var toggleBtns = document.querySelectorAll('.broker-toggle-detail');

    toggleBtns.forEach(function (btn) {
        var collapsible = btn.closest('.broker-section-collapsible');
        if (!collapsible) return;

        var detail = collapsible.querySelector('.broker-section-detail');
        if (!detail) return;

        var showText = btn.getAttribute('data-show') || 'Show details';
        var hideText = btn.getAttribute('data-hide') || 'Hide details';

        btn.addEventListener('click', function () {
            var isExpanded = detail.style.display !== 'none';

            if (isExpanded) {
                // Collapse
                detail.style.display = 'none';
                btn.textContent = showText;
                btn.classList.remove('expanded');
            } else {
                // Expand
                detail.style.display = 'block';
                btn.textContent = hideText;
                btn.classList.add('expanded');
            }
        });
    });

});

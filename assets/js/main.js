/**
 * FX Trading Today - Main JavaScript
 * 
 * Vanilla JS (không dùng jQuery để giảm kích thước)
 * Chức năng: Mobile menu, Search toggle, Back to top, Smooth scroll
 * 
 * @package FXTradingToday
 */

document.addEventListener('DOMContentLoaded', function () {

    // ═══ MOBILE MENU ═══════════════════════════════
    const mobileToggle = document.getElementById('mobile-menu-toggle');
    const mobileOverlay = document.getElementById('mobile-menu-overlay');
    const mobileClose = document.getElementById('mobile-menu-close');

    if (mobileToggle && mobileOverlay) {
        mobileToggle.addEventListener('click', function () {
            mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        if (mobileClose) {
            mobileClose.addEventListener('click', closeMenu);
        }

        mobileOverlay.addEventListener('click', function (e) {
            if (e.target === mobileOverlay) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && mobileOverlay.classList.contains('active')) {
                closeMenu();
            }
        });

        function closeMenu() {
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // ═══ SEARCH TOGGLE (fixed overlay) ═════════════
    const searchToggle = document.getElementById('search-toggle');
    const searchOverlay = document.getElementById('search-overlay');

    if (searchToggle && searchOverlay) {
        searchToggle.addEventListener('click', function () {
            searchOverlay.classList.toggle('active');

            if (searchOverlay.classList.contains('active')) {
                var input = searchOverlay.querySelector('.search-input');
                if (input) {
                    setTimeout(function () { input.focus(); }, 150);
                }
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        // Đóng khi click backdrop (vùng tối bên ngoài form)
        searchOverlay.addEventListener('click', function (e) {
            // Chỉ đóng khi click vào backdrop, không phải vào form
            if (e.target === searchOverlay) {
                searchOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Đóng khi nhấn Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                searchOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    // ═══ BACK TO TOP ═══════════════════════════════
    const backToTop = document.getElementById('back-to-top');

    if (backToTop) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 400) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        }, { passive: true });

        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ═══ STICKY HEADER SHADOW ══════════════════════
    const header = document.getElementById('site-header');

    if (header) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 10) {
                header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.08)';
            } else {
                header.style.boxShadow = 'none';
            }
        }, { passive: true });
    }

    // ═══ SMOOTH SCROLL cho anchor links ════════════
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var targetId = this.getAttribute('href');
            if (targetId === '#') return;

            var target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                var headerHeight = document.querySelector('.site-header')?.offsetHeight || 64;
                var targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ═══ EXTERNAL LINKS: mở tab mới + rel ═════════
    document.querySelectorAll('.entry-content a').forEach(function (link) {
        if (link.hostname !== window.location.hostname) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener nofollow');
        }
    });

    // ═══ LAZY LOAD IMAGES (fallback cho browser cũ) ══
    if ('IntersectionObserver' in window) {
        var lazyImages = document.querySelectorAll('img[loading="lazy"]');
        var imageObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(function (img) {
            imageObserver.observe(img);
        });
    }

});

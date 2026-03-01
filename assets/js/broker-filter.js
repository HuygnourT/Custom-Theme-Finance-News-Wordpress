/**
 * Broker Filter - Lọc và sắp xếp bảng so sánh broker
 * Chỉ load ở trang so sánh broker (template-brokers.php)
 * 
 * @package FXTradingToday
 */

document.addEventListener('DOMContentLoaded', function () {

    var filterForm = document.getElementById('broker-filter-form');
    var brokerList = document.getElementById('broker-list');

    if (!filterForm || !brokerList) return;

    var brokerItems = Array.from(brokerList.querySelectorAll('.broker-row'));

    // ── Sắp xếp ────────────────────────────
    var sortSelect = document.getElementById('broker-sort');
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            var sortBy = this.value;

            brokerItems.sort(function (a, b) {
                var aVal = parseFloat(a.dataset[sortBy]) || 0;
                var bVal = parseFloat(b.dataset[sortBy]) || 0;

                // Rating: cao → thấp | Spread & Deposit: thấp → cao
                if (sortBy === 'rating') {
                    return bVal - aVal;
                }
                return aVal - bVal;
            });

            // Re-render
            brokerItems.forEach(function (item) {
                brokerList.appendChild(item);
            });
        });
    }

    // ── Tìm kiếm ───────────────────────────
    var searchInput = document.getElementById('broker-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var query = this.value.toLowerCase().trim();

            brokerItems.forEach(function (item) {
                var name = (item.dataset.name || '').toLowerCase();
                if (name.includes(query) || query === '') {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});

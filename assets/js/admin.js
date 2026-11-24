/**
 * Hidden Places Explorer - Admin Panel Interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Sidebar Toggle
    const mobileToggle = document.querySelector('.admin-mobile-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });

        // Close sidebar if clicking outside on mobile
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target) && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }

    // 2. Image URL Live Preview for Add/Edit forms
    const imageUrlInput = document.getElementById('image_url');
    const imagePreview = document.getElementById('image_preview');
    if (imageUrlInput && imagePreview) {
        const updatePreview = () => {
            const val = imageUrlInput.value.trim();
            if (val) {
                imagePreview.src = val;
                imagePreview.style.display = 'block';
            } else {
                imagePreview.style.display = 'none';
            }
        };
        imageUrlInput.addEventListener('input', updatePreview);
        imageUrlInput.addEventListener('change', updatePreview);
    }

    // 3. Client-side Live Table Filter/Search
    const tableSearchInput = document.getElementById('admin-table-search');
    if (tableSearchInput) {
        tableSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.admin-table tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
});

/**
 * Confirm delete helper
 */
function confirmDelete(message = 'Are you sure you want to delete this item? This action cannot be undone.') {
    return confirm(message);
}

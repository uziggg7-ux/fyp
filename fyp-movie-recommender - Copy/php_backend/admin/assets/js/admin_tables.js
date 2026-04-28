/**
 * admin_tables.js - Client-side table interactions & UI Feedback
 */
document.addEventListener('DOMContentLoaded', function() {

    // 0. Inject Toast Container if not exists
    if (!document.getElementById('admin-toast-container')) {
        const container = document.createElement('div');
        container.id = 'admin-toast-container';
        container.style.cssText = 'position:fixed; bottom:30px; right:30px; z-index:9999; display:flex; flex-direction:column; gap:10px;';
        document.body.appendChild(container);
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        const colors = {
            success: 'rgba(0, 255, 0, 0.1)',
            error: 'rgba(255, 59, 59, 0.1)',
            info: 'rgba(255, 255, 255, 0.05)'
        };
        const borderColors = {
            success: '#00FF00',
            error: '#FF3B3B',
            info: 'rgba(255, 255, 255, 0.2)'
        };

        toast.style.cssText = `
            background: #111;
            backdrop-filter: blur(10px);
            border: 1px solid ${borderColors[type]};
            border-left: 5px solid ${borderColors[type]};
            color: #fff;
            padding: 15px 25px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            animation: slideInToast 0.4s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
            min-width: 250px;
        `;

        toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-octagon'} me-2" style="color: ${borderColors[type]}"></i> ${message}`;

        document.getElementById('admin-toast-container').appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'fadeOutToast 0.4s forwards';
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }

    // Add Keyframes for Toast
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes slideInToast {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOutToast {
            to { transform: translateY(20px); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    // 1. Simple Table Search
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('.table-premium tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // 2. User Deletion with Confirmation
    const deleteBtns = document.querySelectorAll('.delete-user-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');

            // Custom UI for confirmation could go here, but using confirm for now
            if (confirm(`CRITICAL_ACTION: Delete user "${userName}"?`)) {
                deleteUser(userId, this.closest('tr'));
            }
        });
    });

    async function deleteUser(id, rowElement) {
        try {
            const response = await fetch(`api/delete_user.php?id=${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();

            if (result.success) {
                rowElement.style.opacity = '0.3';
                rowElement.style.pointerEvents = 'none';
                showToast("USER_DATA_PURGED: Identity successfully removed.");
                setTimeout(() => rowElement.remove(), 500);
            } else {
                showToast(result.message, 'error');
            }
        } catch (err) {
            console.error(err);
            showToast("CONNECTION_FAILED: System offline.", 'error');
        }
    }
});

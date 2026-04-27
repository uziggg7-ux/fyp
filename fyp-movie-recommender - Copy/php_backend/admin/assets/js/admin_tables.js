/**
 * admin_tables.js - Client-side table interactions
 */
document.addEventListener('DOMContentLoaded', function() {

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

            if (confirm(`CRITICAL_ACTION: Delete user "${userName}" and all associated records? This cannot be undone.`)) {
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
                setTimeout(() => rowElement.remove(), 500);
            } else {
                alert("DELETION_FAILED: " + result.message);
            }
        } catch (err) {
            console.error(err);
            alert("SYSTEM_FAULT: Could not connect to deletion endpoint.");
        }
    }
});

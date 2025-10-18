const filter = document.getElementById('statusFilter');
const rows = document.querySelectorAll('#laporanTable tbody tr');

filter.addEventListener('change', () => {
    const value = filter.value;
    rows.forEach(row => {
        if (value === 'all' || row.getAttribute('data-status') === value) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

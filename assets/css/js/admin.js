$(document).ready(function() {
    // DataTables Initialization
    $('.display').DataTable({
        pageLength: 5,
        lengthChange: false,
        searching: true,
        ordering: true,
        info: true,
        paging: true,
        language: {
            emptyTable: "No data available",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Prev"
            }
        },
        dom: '<"top"f>rt<"bottom"ip><"clear">'
    });

    // Sidebar hover tooltip (for mobile collapse)
    $('.sidebar-nav a').each(function() {
        const text = $(this).text().trim();
        if (text) $(this).attr('title', text);
    });

    // Button hover animation
    $('.btn-action').hover(
        function() { $(this).css('transform', 'scale(1.05)'); },
        function() { $(this).css('transform', 'scale(1)'); }
    );

    // Modal close on Esc key
    $(document).keydown(function(e) {
        if (e.key === "Escape") closeValidateModal();
    });
});

function openValidateModal(studentId) {
    $('#modalStudentId').val(studentId);
    $('#validateModal').fadeIn(200);
}

function closeValidateModal() {
    $('#validateModal').fadeOut(200);
}
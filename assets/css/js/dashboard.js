$(document).ready(function() {
    // Enhance DataTables
    $('#studentsTable').DataTable({
        pageLength: 5,
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: 0 }],
        language: {
            search: "Filter students:",
            lengthMenu: "Show _MENU_ entries"
        }
    });

    $('#coursesTable').DataTable({
        pageLength: 5,
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: 0 }],
        language: {
            search: "Filter courses:",
            lengthMenu: "Show _MENU_ entries"
        }
    });

    // Animation for stats cards
    $('.stat-card').each(function(index) {
        $(this).delay(index * 200).animate({ 
            opacity: 1, 
            top: 0 
        }, 500, 'easeOutCubic');
    });

    // Ensure charts are responsive (already handled in main file, but adding fallback)
    window.addEventListener('resize', function() {
        if (window.studentChart) window.studentChart.resize();
        if (window.subjectChart) window.subjectChart.resize();
    });

    // Add subtle hover effect for table rows
    $('.dataTable tbody tr').hover(
        function() {
            $(this).css('background-color', 'rgba(52, 152, 219, 0.1)');
        },
        function() {
            $(this).css('background-color', '');
        }
    );

    // Add smooth scroll to top if needed
    if ($('.dashboard-container').length) {
        $('html, body').animate({
            scrollTop: 0
        }, 300);
    }
});
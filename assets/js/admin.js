$(document).ready(function() {
    // DataTables Initialization (if still used elsewhere)
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

    // Sidebar hover tooltip
    $('.sidebar-nav a').each(function() {
        const text = $(this).text().trim();
        if (text) $(this).attr('title', text);
    });

    // Button hover animation
    $('.btn-action').hover(
        function() { $(this).css('transform', 'scale(1.05)'); },
        function() { $(this).css('transform', 'scale(1)'); }
    );

    // Course card click redirection
    $('.course-card').on('click', function(e) {
        if (!$(e.target).closest('.course-actions').length) {
            const courseId = $(this).data('id');
            window.location.href = 'view_course.php?id=' + courseId;
        }
    });

    // Debug embedded content loading
    $('.embedded-video').on('load', function() {
        console.log('Video iframe loaded');
    }).on('error', function() {
        alert('Failed to load video');
    });

    // Block print/save/download actions globally
    $(document).on('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            if (e.key === 'p' || e.key === 's' || e.key === 'Shift' || e.key === 'c') {
                e.preventDefault();
                e.stopPropagation();
                console.log('Blocked Ctrl+P/Ctrl+S/Ctrl+Shift/Ctrl+C');
                alert('Copying, saving, and printing are restricted.');
                return false;
            }
        }
        if (e.key === 'PrintScreen') {
            e.preventDefault();
            console.log('Blocked PrintScreen');
            navigator.clipboard.writeText('').then(() => console.log('Clipboard cleared'));
            $('.pdf-container').addClass('blurred');
            setTimeout(() => $('.pdf-container').removeClass('blurred'), 1000); // Brief blur for PrintScreen
            alert('Screenshots are restricted on this page.');
            return false;
        }
    });

    // Block right-click globally
    $(document).on('contextmenu', function(e) {
        e.preventDefault();
        console.log('Blocked right-click');
        alert('Right-click is disabled to protect content.');
        return false;
    });

    // Block print attempts
    window.onbeforeprint = function() {
        console.log('Blocked print attempt');
        alert('Printing is restricted on this page.');
        $('.pdf-container').addClass('blurred');
        return false;
    };
    Object.defineProperty(window, 'print', {
        value: function() {
            console.log('Print function overridden');
            return false;
        },
        writable: false
    });

    // Blur on focus loss to deter screenshots (e.g., Snipping Tool)
    let isBlurred = false;
    $(window).on('blur', function() {
        console.log('Window lost focus - possible screenshot attempt');
        $('.pdf-container').addClass('blurred');
        isBlurred = true;
    });

    // Unblur only on explicit user interaction
    $('.pdf-container').on('click', function() {
        if (isBlurred) {
            console.log('User clicked to unblur');
            $('.pdf-container').removeClass('blurred');
            isBlurred = false;
        }
    });

    // Attempt to detect dev tools
    setInterval(() => {
        if (window.outerWidth - window.innerWidth > 200 || window.outerHeight - window.innerHeight > 200) {
            console.log('Possible dev tools open');
            alert('Developer tools are restricted.');
            $('.pdf-container').addClass('blurred');
            isBlurred = true;
        }
    }, 1000);

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
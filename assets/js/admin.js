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

    // Debug embedded content loading
    $('.embedded-pdf').on('load', function() {
        console.log('PDF iframe loaded');
    }).on('error', function() {
        alert('Failed to load PDF');
    });

    $('.embedded-video').on('load', function() {
        console.log('Video iframe loaded');
    }).on('error', function() {
        alert('Failed to load video');
    });

    // Block print/save actions globally
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && (e.key === 'p' || e.key === 's') || e.key === 'PrintScreen') {
            e.preventDefault();
            e.stopPropagation();
            console.log('Blocked Ctrl+P/Ctrl+S/PrintScreen');
            return false;
        }
    });

    // Block right-click on PDF and overlay
    $('.pdf-wrapper, .embedded-pdf').on('contextmenu', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Blocked right-click on PDF');
        return false;
    });

    // Inject script into iframe to block print/save (best effort)
    $('.embedded-pdf').each(function() {
        const iframe = this;
        const script = `
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && (e.key === 'p' || e.key === 's')) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
            window.onbeforeprint = function() { return false; };
        `;
        try {
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            const scriptTag = doc.createElement('script');
            scriptTag.textContent = script;
            doc.head.appendChild(scriptTag);
            console.log('Injected security script into PDF iframe');
        } catch (e) {
            console.log('Could not inject script into iframe: ', e);
        }
    });

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
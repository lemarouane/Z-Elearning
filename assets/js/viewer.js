async function renderPDF(url, canvasId, errorId) {
    const canvas = document.getElementById(canvasId);
    const errorDiv = document.getElementById(errorId);
    const context = canvas.getContext('2d');

    console.log('PDF.js version:', pdfjsLib.version);
    console.log('Loading PDF from:', url);

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        const pdfData = await response.arrayBuffer();
        console.log('PDF data fetched, size:', pdfData.byteLength);

        const pdf = await pdfjsLib.getDocument({ data: pdfData }).promise;
        console.log('PDF loaded, pages:', pdf.numPages);

        const page = await pdf.getPage(1);
        console.log('Page 1 loaded');

        const viewport = page.getViewport({ scale: 1.5 });
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        console.log('Canvas set to:', canvas.width, 'x', canvas.height);

        await page.render({
            canvasContext: context,
            viewport: viewport
        }).promise;
        console.log('PDF rendered successfully');
    } catch (error) {
        console.error('PDF rendering failed:', error);
        errorDiv.style.display = 'block';
        errorDiv.textContent = 'Failed to load PDF: ' + error.message;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const pdfCanvas = document.getElementById('pdf-canvas');
    if (pdfCanvas) {
        pdfCanvas.style.pointerEvents = 'none';
        pdfCanvas.addEventListener('contextmenu', (e) => e.preventDefault());
    }
});
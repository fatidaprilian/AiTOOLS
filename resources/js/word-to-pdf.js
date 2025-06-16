document.addEventListener('DOMContentLoaded', function () {
    const uploadInput = document.getElementById('upload-word');
    const convertButton = document.getElementById('convert-to-pdf-button');
    const resultSection = document.getElementById('result-section');
    const resultTitle = document.getElementById('result-title');
    const downloadLink = document.getElementById('download-link');
    const resultMessage = document.getElementById('result-message');
    const resetButton = document.getElementById('reset-button');
    const fileNameDisplay = document.getElementById('file-name-display');
    const loadingIndicator = document.getElementById('loading-indicator-simple');
    let uploadedFile = null;

    function clearState() {
        if (downloadLink.href && downloadLink.href.startsWith('blob:')) {
            window.URL.revokeObjectURL(downloadLink.href);
        }
        uploadInput.value = '';
        uploadedFile = null;
        fileNameDisplay.textContent = 'Belum ada file dipilih';
        convertButton.disabled = true;
        resultSection.classList.add('hidden');
    }

    uploadInput.addEventListener('change', () => {
        if (uploadInput.files.length > 0) {
            const file = uploadInput.files[0];
            const allowedExtensions = ['.doc', '.docx'];
            const fileExtension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();

            if (!allowedExtensions.includes(fileExtension)) {
                alert('Format file tidak didukung. Harap unggah file .doc atau .docx.');
                clearState();
                return;
            }
            uploadedFile = file;
            fileNameDisplay.textContent = file.name;
            convertButton.disabled = false;
            resultSection.classList.add('hidden');
        } else {
            clearState();
        }
    });

    convertButton.addEventListener('click', async () => {
        if (!uploadedFile) return alert('Pilih file Word dahulu.');

        convertButton.disabled = true;
        resultSection.classList.remove('hidden');
        resultTitle.textContent = 'Proses Konversi';
        loadingIndicator.classList.remove('hidden');
        resultMessage.classList.add('hidden');
        downloadLink.classList.add('hidden');

        const formData = new FormData();
        formData.append('word_file', uploadedFile);

        try {
            const response = await fetch(convertWordToPdfProcessUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData,
            });

            loadingIndicator.classList.add('hidden');
            const responseData = await response.json(); // <-- Baca respons sebagai JSON

            if (response.ok && responseData.status === 'success') { // <-- Periksa status di dalam JSON
                const pdfUrl = responseData.pdf_url; // <-- Ambil URL PDF dari JSON
                const filename = responseData.filename; // <-- Ambil nama file dari JSON

                downloadLink.href = pdfUrl; // <-- Set href ke URL CloudConvert
                downloadLink.download = filename; // <-- Set nama download
                
                // Opsional: Anda mungkin ingin membuat unduhan "blob" jika PDF perlu diproses oleh JS
                // Tetapi untuk unduhan langsung, cukup set href ke URL CloudConvert.
                // Jika ingin membuat blob lokal (misal untuk fitur preview atau agar tidak redirect user):
                /*
                const pdfResponse = await fetch(pdfUrl);
                if (!pdfResponse.ok) throw new Error('Gagal mengunduh PDF dari CloudConvert.');
                const blob = await pdfResponse.blob();
                const localUrl = window.URL.createObjectURL(blob);
                downloadLink.href = localUrl;
                downloadLink.download = filename;
                */

                resultTitle.textContent = 'Konversi Berhasil!';
                resultMessage.textContent = 'File PDF Anda siap diunduh.';
                resultMessage.classList.remove('hidden');
                downloadLink.classList.remove('hidden');
            } else {
                // Penanganan error jika status bukan 'success' atau respons HTTP bukan OK
                const errorMessage = responseData.message || 'Gagal mengkonversi dokumen.';
                throw new Error(`Gagal (${response.status}): ${errorMessage}`);
            }
        } catch (error) {
            resultTitle.textContent = 'Gagal Konversi';
            resultMessage.textContent = error.message;
            resultMessage.classList.remove('hidden');
            console.error('Conversion error:', error);
        } finally {
            convertButton.disabled = false;
        }
    });

    resetButton.addEventListener('click', clearState);
});
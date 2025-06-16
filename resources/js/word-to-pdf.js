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
            const response = await fetch("{{ route('wordtopdf.process') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData,
            });

            loadingIndicator.classList.add('hidden');
            const contentType = response.headers.get('content-type') || '';

            if (response.ok && contentType.includes('application/pdf')) {
                // Sukses PDF
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                downloadLink.href = url;
                downloadLink.download = uploadedFile.name.replace(/\.(doc|docx)$/i, '.pdf');
                resultTitle.textContent = 'Konversi Berhasil!';
                resultMessage.textContent = 'File PDF Anda siap diunduh.';
                resultMessage.classList.remove('hidden');
                downloadLink.classList.remove('hidden');
            } else {
                // Baca response sebagai text
                const text = await response.text();
                let errorMessage = 'Gagal mengkonversi dokumen.';
                try {
                    const errorData = JSON.parse(text);
                    errorMessage = errorData.message || errorMessage;
                } catch {
                    // HTML error (misal, "<!DOCTYPE html..."), tampilkan sebagian
                    if (text.startsWith('<!DOCTYPE') || text.startsWith('<html')) {
                        errorMessage = 'Server error: ' + text.substring(0, 100) + ' ...';
                    } else {
                        errorMessage = text;
                    }
                }
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
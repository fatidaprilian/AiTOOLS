document.addEventListener('DOMContentLoaded', function() {
    const uploadImageInput = document.getElementById('upload-image-input');
    const originalImageElement = document.getElementById('original-image');
    const resultImageElement = document.getElementById('result-image');
    const originalDimensionSpan = document.getElementById('original-dimension');
    const resultDimensionSpan = document.getElementById('result-dimension');
    const processImageButton = document.getElementById('process-image-button');
    const downloadResultButton = document.getElementById('download-result-button');
    const originalPlaceholderText = document.getElementById('original-placeholder-text');
    const resultPlaceholderText = document.getElementById('result-placeholder-text');

    let uploadedFile = null;
    let originalFileName = '';

    function displayOriginalImage(file) {
        originalFileName = file.name.split('.').slice(0, -1).join('.');
        const reader = new FileReader();
        reader.onload = function(e) {
            originalImageElement.src = e.target.result;
            originalImageElement.classList.remove('hidden');
            originalPlaceholderText.classList.add('hidden');

            resultImageElement.src = '';
            resultImageElement.classList.add('hidden');
            resultPlaceholderText.textContent = 'Hasil akan muncul disini setelah diproses.';
            resultPlaceholderText.classList.remove('hidden');

            const img = new Image();
            img.onload = function() {
                originalDimensionSpan.textContent = `${img.width} × ${img.height}`;
                resultDimensionSpan.textContent = `---`;
                uploadedFile = { file: file, originalWidth: img.width, originalHeight: img.height };
                processImageButton.disabled = false;
                downloadResultButton.classList.add('hidden');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    uploadImageInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (!file) return;
        if (file.size > 10 * 1024 * 1024) return alert('Ukuran gambar maksimal 10MB.');
        displayOriginalImage(file);
    });

    processImageButton.addEventListener('click', async () => {
        if (!uploadedFile) return alert('Silakan unggah gambar dahulu.');

        processImageButton.textContent = 'Memproses...';
        processImageButton.disabled = true;
        resultPlaceholderText.textContent = 'Sedang menghapus background...';

        const formData = new FormData();
        formData.append('image', uploadedFile.file);

        try {
            const response = await fetch('/remove-background-process', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData,
            });
            const data = await response.json();

            if (response.ok && data.status === 'success' && data.processed_image_base64) {
                resultImageElement.src = data.processed_image_base64;
                resultImageElement.classList.remove('hidden');
                resultPlaceholderText.classList.add('hidden');
                resultDimensionSpan.textContent = `${data.processed_dimensions?.width || uploadedFile.originalWidth} × ${data.processed_dimensions?.height || uploadedFile.originalHeight}`;
                downloadResultButton.disabled = false;
                downloadResultButton.classList.remove('hidden');
            } else {
                resultPlaceholderText.textContent = `Gagal: ${data.message || 'Error tidak diketahui'}`;
            }
        } catch (error) {
            resultPlaceholderText.textContent = 'Gagal menghubungi server.';
        } finally {
            processImageButton.textContent = 'Hapus Background';
            processImageButton.disabled = false;
        }
    });

    downloadResultButton.addEventListener('click', () => {
        if (!resultImageElement.src) return alert('Tidak ada hasil untuk diunduh.');
        const link = document.createElement('a');
        link.href = resultImageElement.src;
        link.download = `${originalFileName}_no_bg.png`;
        document.body.appendChild(link);
        link.click();
        link.remove();
    });
});
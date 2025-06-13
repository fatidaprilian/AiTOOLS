document.addEventListener('DOMContentLoaded', function() {
    const uploadImageInput = document.getElementById('upload-image-input');
    const originalImageElement = document.getElementById('original-image');
    const enhancedImageElement = document.getElementById('enhanced-image');
    const originalDimensionSpan = document.getElementById('original-dimension');
    const enhancedDimensionSpan = document.getElementById('enhanced-dimension');
    const processImageButton = document.getElementById('process-image-button');
    const downloadEnhancedButton = document.getElementById('download-enhanced-button');
    const originalPlaceholderText = document.getElementById('original-placeholder-text');
    const enhancedPlaceholderText = document.getElementById('enhanced-placeholder-text');
    const imageFormatOptionsContainer = document.getElementById('image-format-options');

    let selectedOutputFormat = 'png';
    let uploadedFile = null;
    let originalFileName = '';

    function setupOptionSelection(container, defaultValue, callback) {
        if (!container) return;
        const buttons = container.querySelectorAll('button');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                if (this.disabled) return;
                buttons.forEach(btn => {
                    btn.classList.remove('bg-blue-600', 'text-white');
                    btn.classList.add('bg-white', 'dark:bg-gray-700', 'border');
                });
                this.classList.add('bg-blue-600', 'text-white');
                this.classList.remove('bg-white', 'dark:bg-gray-700', 'border');
                if (callback) callback(this.dataset.value);
            });
        });
    }

    setupOptionSelection(imageFormatOptionsContainer, selectedOutputFormat, (value) => selectedOutputFormat = value);

    uploadImageInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) return alert('Ukuran gambar maksimal adalah 5MB.');

        originalFileName = file.name.split('.').slice(0, -1).join('.');
        const reader = new FileReader();
        reader.onload = function(e) {
            originalImageElement.src = e.target.result;
            originalImageElement.classList.remove('hidden');
            originalPlaceholderText.classList.add('hidden');

            enhancedImageElement.src = '';
            enhancedImageElement.classList.add('hidden');
            enhancedPlaceholderText.textContent = 'Hasil akan muncul disini.';
            enhancedPlaceholderText.classList.remove('hidden');

            const img = new Image();
            img.onload = function() {
                originalDimensionSpan.textContent = `${img.width} × ${img.height}`;
                enhancedDimensionSpan.textContent = `---`;
                uploadedFile = { file, originalWidth: img.width, originalHeight: img.height };
                processImageButton.disabled = false;
                downloadEnhancedButton.classList.add('hidden');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    processImageButton.addEventListener('click', async () => {
        if (!uploadedFile) return alert('Unggah gambar dahulu.');

        processImageButton.textContent = 'Memproses...';
        processImageButton.disabled = true;
        enhancedPlaceholderText.textContent = 'Sedang memproses...';

        const formData = new FormData();
        formData.append('image_file', uploadedFile.file);
        formData.append('output_format', selectedOutputFormat);

        try {
            const response = await fetch('/upscale-image', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData,
            });
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                enhancedImageElement.src = data.upscaled_image_base64;
                enhancedImageElement.classList.remove('hidden');
                enhancedPlaceholderText.classList.add('hidden');
                enhancedDimensionSpan.textContent = `${data.enhanced_dimensions.width} × ${data.enhanced_dimensions.height}`;
                downloadEnhancedButton.disabled = false;
                downloadEnhancedButton.classList.remove('hidden');
            } else {
                enhancedPlaceholderText.textContent = `Gagal: ${data.message || 'Error tidak diketahui'}`;
            }
        } catch (error) {
            enhancedPlaceholderText.textContent = 'Gagal menghubungi server.';
        } finally {
            processImageButton.textContent = 'Proses Gambar';
            processImageButton.disabled = false;
        }
    });

    downloadEnhancedButton.addEventListener('click', () => {
        if (!enhancedImageElement.src) return alert('Tidak ada hasil untuk diunduh.');
        const link = document.createElement('a');
        link.href = enhancedImageElement.src;
        link.download = `<span class="math-inline">\{originalFileName\}\_upscaled\.</span>{selectedOutputFormat}`;
        document.body.appendChild(link);
        link.click();
        link.remove();
    });
});
document.addEventListener('DOMContentLoaded', function() {
    const checkGrammarButton = document.getElementById('check-grammar-button');
    const inputTextarea = document.getElementById('input-teks');
    const resultTextarea = document.getElementById('hasil-teks');
    const copyResultButton = document.getElementById('copy-result-button');
    const uploadFileButton = document.getElementById('upload-file-button');
    const uploadFileInput = document.getElementById('upload-file-input');
    const downloadPdfButton = document.getElementById('download-pdf-button');

    uploadFileButton.addEventListener('click', () => uploadFileInput.click());

    uploadFileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file && file.type === 'text/plain') {
            const reader = new FileReader();
            reader.onload = (e) => { inputTextarea.value = e.target.result; };
            reader.onerror = () => alert('Gagal membaca file.');
            reader.readAsText(file);
        } else if (file) {
            alert('Mohon unggah file teks (.txt) saja.');
            uploadFileInput.value = '';
        }
    });

    checkGrammarButton.addEventListener('click', async () => {
        if (inputTextarea.value.trim() === '') return alert('Silakan masukkan teks.');

        checkGrammarButton.textContent = 'Memeriksa...';
        checkGrammarButton.disabled = true;
        resultTextarea.value = 'Memproses...';

        try {
            const response = await fetch('/grammar-check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ text: inputTextarea.value })
            });
            const data = await response.json();
            resultTextarea.value = response.ok && data.status === 'success' ? data.correctedText : `Error: ${data.message || response.statusText}`;
        } catch (error) {
            resultTextarea.value = 'Gagal menghubungi server. Periksa koneksi Anda.';
        } finally {
            checkGrammarButton.textContent = 'Periksa Teks';
            checkGrammarButton.disabled = false;
        }
    });

    downloadPdfButton.addEventListener('click', async () => {
        if (resultTextarea.value.trim() === '') return alert('Tidak ada teks untuk diunduh.');

        downloadPdfButton.textContent = 'Mempersiapkan...';
        downloadPdfButton.disabled = true;

        try {
            const response = await fetch('/download-grammar-pdf', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ text: resultTextarea.value })
            });
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Hasil_Grammar_Checker.pdf`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            } else {
                alert('Gagal mengunduh PDF.');
            }
        } catch (error) {
            alert('Gagal menghubungi server untuk unduh PDF.');
        } finally {
            downloadPdfButton.textContent = 'Unduh PDF';
            downloadPdfButton.disabled = false;
        }
    });

    copyResultButton.addEventListener('click', () => {
        if (resultTextarea.value.trim() === '') return;
        navigator.clipboard.writeText(resultTextarea.value).then(() => alert('Teks hasil telah disalin!'));
    });
});
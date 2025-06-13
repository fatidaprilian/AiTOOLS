document.addEventListener('DOMContentLoaded', function() {
    const summarizeButton = document.getElementById('summarize-text-button');
    const inputTextarea = document.getElementById('input-text-summarizer');
    const resultTextarea = document.getElementById('result-text-summarizer');
    const copySummaryButton = document.getElementById('copy-summary-button');
    const uploadFileButton = document.getElementById('upload-file-button-summarizer');
    const uploadFileInput = document.getElementById('upload-file-summarizer');
    const downloadSummaryButton = document.getElementById('download-summary-button');

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

    summarizeButton.addEventListener('click', async () => {
        if (inputTextarea.value.trim() === '') return alert('Silakan masukkan teks.');

        summarizeButton.textContent = 'Meringkas...';
        summarizeButton.disabled = true;
        resultTextarea.value = 'Sedang memproses ringkasan...';

        try {
            const response = await fetch('/summarize-text', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ text: inputTextarea.value })
            });
            const data = await response.json();
            if (response.ok && data.status === 'success' && data.summarizedText) {
                resultTextarea.value = data.summarizedText;
            } else {
                resultTextarea.value = `Gagal: ${data.message || 'Respon tidak sesuai.'}`;
            }
        } catch (error) {
            resultTextarea.value = 'Gagal menghubungi server.';
        } finally {
            summarizeButton.textContent = 'Ringkas Teks';
            summarizeButton.disabled = false;
        }
    });

    downloadSummaryButton.addEventListener('click', () => {
        if (resultTextarea.value.trim() === '') return alert('Tidak ada hasil untuk diunduh.');

        const blob = new Blob([resultTextarea.value], { type: 'text/plain;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Ringkasan_Teks.txt`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    });

    copySummaryButton.addEventListener('click', () => {
        if (resultTextarea.value.trim() === '') return;
        navigator.clipboard.writeText(resultTextarea.value)
            .then(() => alert('Hasil ringkasan telah disalin!'));
    });
});
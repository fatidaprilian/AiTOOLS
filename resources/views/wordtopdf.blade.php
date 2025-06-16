@extends('layouts.app')

@section('title', 'Convert Word to PDF')

@section('content')
<div class="space-y-6">
    <div class="sm:hidden mb-4">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-blue-600 font-semibold text-sm hover:underline"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>
    <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
        <h1 class="text-lg font-extrabold mb-1 dark:text-white">Convert Word To PDF</h1>
        <p class="text-gray-600 dark:text-gray-300 text-sm">Ubah file Word (.doc, .docx) Anda menjadi file PDF dengan mudah dan cepat.</p>
    </section>
    <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-6">
        <label class="block text-xs text-gray-400 dark:text-gray-500" for="upload-word">Upload File Word</label>
        <div class="flex flex-col items-center justify-center border border-dashed border-gray-400 dark:border-gray-600 rounded-md p-4">
            <label class="cursor-pointer w-full flex flex-col items-center justify-center" for="upload-word">
                <i class="fas fa-file-word fa-3x text-blue-500 mb-2"></i>
                <span class="text-sm text-blue-600 dark:text-blue-400">Klik <span class="underline font-semibold">disini</span> untuk memilih file Word</span>
                <span id="file-name-display" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Belum ada file dipilih</span>
                <input class="hidden" id="upload-word" type="file" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"/>
            </label>
        </div>
        <div class="flex justify-end">
            <button class="bg-blue-600 text-white font-semibold rounded-md px-6 py-2 hover:bg-blue-700 disabled:opacity-50" type="button" id="convert-to-pdf-button" disabled>Convert to PDF</button>
        </div>
    </section>
    <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4 hidden" id="result-section">
        <h2 class="font-semibold text-black dark:text-white" id="result-title">Proses Konversi</h2>
        <div id="loading-indicator-simple" class="hidden items-center space-x-2 text-sm text-gray-600 dark:text-gray-300">
            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span>Sedang memproses konversi...</span>
        </div>
        <p class="text-gray-600 dark:text-gray-300 text-sm hidden" id="result-message"></p>
        <div class="flex justify-end gap-4 mt-2">
            <a class="bg-blue-600 text-white font-semibold rounded-md px-6 py-2 hover:bg-blue-700 hidden" download id="download-link" href="#">Unduh PDF</a>
            <button class="bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-semibold rounded-md px-6 py-2" id="reset-button" type="button">Konversi Lagi</button>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script>
        // Define a global (or scoped) JavaScript variable with the route URL
        const convertWordToPdfProcessUrl = "{{ route('wordtopdf.process') }}"; //
    </script>
    @vite('resources/js/word-to-pdf.js')
@endpush
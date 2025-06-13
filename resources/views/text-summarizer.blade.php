@extends('layouts.app')

@section('title', 'Text Summarizer')

@section('content')
<div class="space-y-6">
    <div class="sm:hidden mb-4">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-blue-600 font-semibold text-sm hover:underline"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>
    <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
        <h1 class="text-lg font-extrabold mb-1 dark:text-white">Text Summarizer</h1>
        <p class="text-gray-600 dark:text-gray-300 text-sm">Ringkas teks panjang menjadi beberapa kalimat inti dengan bantuan AI.</p>
    </section>
    <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4">
        <label class="block font-semibold text-sm mb-1 dark:text-white" for="input-text-summarizer">Input Teks</label>
        <textarea class="w-full resize-none rounded-md border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 p-3" id="input-text-summarizer" placeholder="Masukan teks yang ingin Anda ringkas disini..." rows="6"></textarea>
        <input type="file" id="upload-file-summarizer" accept=".txt" class="hidden">
        <div class="flex justify-end gap-4">
            <button class="font-bold text-sm border-2 border-blue-600 rounded-md px-6 py-2" type="button" id="upload-file-button-summarizer">Upload File (.txt)</button>
            <button class="font-bold text-sm bg-blue-600 text-white rounded-md px-6 py-2" type="button" id="summarize-text-button">Ringkas Teks</button>
        </div>
    </section>
    <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4">
        <label class="block font-semibold text-sm mb-1 dark:text-white" for="result-text-summarizer">Hasil Ringkasan</label>
        <textarea class="w-full resize-none rounded-md border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 p-3" id="result-text-summarizer" readonly rows="6"></textarea>
        <div class="flex justify-end gap-4">
            <button class="font-bold text-sm border-2 border-blue-600 rounded-md px-6 py-2" type="button" id="download-summary-button">Unduh Ringkasan (.txt)</button>
            <button class="font-bold text-sm bg-blue-600 text-white rounded-md px-6 py-2" type="button" id="copy-summary-button">Salin Hasil</button>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    @vite('resources/js/text-summarizer.js')
@endpush
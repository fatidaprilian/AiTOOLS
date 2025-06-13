@extends('layouts.app')

@section('title', 'Grammar Checker')

@section('content')
    <div class="space-y-6">
        <div class="sm:hidden mb-4">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-blue-600 font-semibold text-sm hover:underline"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
        <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <h1 class="text-lg font-extrabold mb-1 dark:text-white">Grammar Checker</h1>
            <p class="text-gray-600 dark:text-gray-300 text-sm">Perbaiki tata bahasa, ejaan, dan tanda baca dalam teks anda</p>
        </section>
        <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4">
            <label class="block font-semibold text-sm mb-1 dark:text-white" for="input-teks">Input Teks</label>
            <textarea class="w-full resize-none rounded-md border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 px-3 py-2 text-gray-600 dark:text-gray-300" id="input-teks" placeholder="Masukan teks..." rows="6"></textarea>
            <input type="file" id="upload-file-input" accept=".txt" class="hidden">
            <div class="flex justify-end gap-4">
                <button class="font-bold text-sm border-2 border-blue-600 rounded-md px-6 py-2" type="button" id="upload-file-button">Upload File</button>
                <button class="font-bold text-sm bg-blue-600 text-white rounded-md px-6 py-2" type="button" id="check-grammar-button">Periksa Teks</button>
            </div>
        </section>
        <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm space-y-4">
            <label class="block font-semibold text-sm mb-1 dark:text-white" for="hasil-teks">Hasil Teks</label>
            <textarea class="w-full resize-none rounded-md border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 px-3 py-2 text-gray-600 dark:text-gray-300" id="hasil-teks" readonly rows="6"></textarea>
            <div class="flex justify-end gap-4">
                <button class="font-bold text-sm border-2 border-blue-600 rounded-md px-6 py-2" type="button" id="download-pdf-button">Unduh PDF</button>
                <button class="font-bold text-sm bg-blue-600 text-white rounded-md px-6 py-2" type="button" id="copy-result-button">Salin Hasil</button>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/grammar-checker.js')
@endpush
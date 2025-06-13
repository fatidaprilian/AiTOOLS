@extends('layouts.app')

@section('title', 'Remove Background')

@section('content')
<div class="space-y-6">
    <div class="sm:hidden mb-4">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-blue-600 font-semibold text-sm hover:underline"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>
    <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
        <h1 class="text-lg font-extrabold mb-1 dark:text-white">Remove Background</h1>
        <p class="text-gray-600 dark:text-gray-300 text-sm">Hilangkan latar belakang pada gambar Anda secara otomatis. Output default adalah PNG.</p>
    </section>
    <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
        <label class="block text-xs text-gray-400 dark:text-gray-500 mb-1" for="upload-image-input">Upload Gambar</label>
        <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-md h-32 cursor-pointer hover:border-blue-500 transition-colors" for="upload-image-input">
            <i class="fas fa-upload text-gray-400 text-2xl mb-2"></i>
            <span class="text-sm text-gray-500 dark:text-gray-400">Klik atau letakkan file disini</span>
        </label>
        <input class="hidden" id="upload-image-input" type="file" accept="image/jpeg,image/png,image/webp"/>
    </section>
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm md:col-span-1 space-y-4">
            <h2 class="font-semibold text-black dark:text-white">Pengaturan</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">API akan memproses gambar dengan kualitas terbaiknya. Output default adalah format PNG.</p>
            <button class="w-full bg-blue-600 text-white font-semibold rounded-md py-2" type="button" id="process-image-button" disabled>Hapus Background</button>
            <button class="w-full bg-green-500 text-white font-semibold rounded-md py-2 hover:bg-green-600 hidden" type="button" id="download-result-button" disabled>Unduh Hasil (.png)</button>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm md:col-span-2">
            <h2 class="font-semibold text-black dark:text-white mb-4">Preview</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" style="min-height: 220px;">
                <div id="original-image-area" class="border rounded-lg p-3 flex flex-col items-center justify-between bg-gray-50 dark:bg-gray-800/50">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Original</span>
                    <div class="flex-grow w-full flex items-center justify-center my-2"><img alt="Original" class="max-w-full max-h-full object-contain hidden" id="original-image"><p id="original-placeholder-text" class="text-xs text-center text-gray-400">Gambar asli akan muncul disini.</p></div>
                    <span id="original-dimension" class="text-xs text-gray-500">---</span>
                </div>
                <div id="result-image-area" class="border rounded-lg p-3 flex flex-col items-center justify-between bg-gray-50 dark:bg-gray-800/50">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Hasil</span>
                    <div class="flex-grow w-full flex items-center justify-center my-2"><img alt="Result" class="max-w-full max-h-full object-contain hidden" id="result-image"><p id="result-placeholder-text" class="text-xs text-center text-gray-400">Hasil akan muncul disini.</p></div>
                    <span id="result-dimension" class="text-xs text-gray-500">---</span>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    @vite('resources/js/remove-background.js')
@endpush
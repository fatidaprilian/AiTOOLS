@extends('layouts.app')

@section('title', 'Upscaling Image')

@section('content')
    <div class="space-y-6">
        
        {{-- BAGIAN 1: Tombol Kembali (Mobile) yang hilang --}}
        <div class="sm:hidden mb-4">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-blue-600 font-semibold text-sm hover:underline">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Dashboard
            </a>
        </div>

        <section class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <h1 class="text-lg font-extrabold mb-1 dark:text-white">Upscaling Image</h1>
            <p class="text-gray-600 dark:text-gray-300 text-sm">Tingkatkan resolusi gambar Anda dengan kualitas yang lebih baik (4x Upscale).</p>
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
            {{-- BAGIAN 2: Konten Pengaturan yang dipulihkan --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm md:col-span-1 space-y-4">
                <h2 class="font-semibold dark:text-white">Pengaturan</h2>
                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Faktor Skala</label>
                    <div class="flex gap-2 mb-4" id="scale-factor-options">
                        <button class="w-full bg-blue-600 text-white font-semibold rounded-md py-2" type="button" data-value="4x">4x</button>
                    </div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Format Gambar Output</label>
                    <div class="flex gap-2 mb-6" id="image-format-options">
                        <button class="flex-1 bg-blue-600 text-white font-semibold rounded-md py-2" type="button" data-value="png">PNG</button>
                        <button class="flex-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md py-2" type="button" data-value="webp">WebP</button>
                        <button class="flex-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md py-2" type="button" data-value="jpeg">JPEG</button>
                    </div>
                    <button class="w-full bg-blue-600 text-white font-semibold rounded-md py-2 mb-2 disabled:opacity-50" type="button" id="process-image-button" disabled>Proses Gambar</button>
                    <button class="w-full bg-green-500 text-white font-semibold rounded-md py-2 hover:bg-green-600 hidden" type="button" id="download-enhanced-button" disabled>Unduh Hasil</button>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm md:col-span-2">
                <h2 class="font-semibold dark:text-white mb-4">Preview</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" style="min-height: 220px;">
                    <div id="original-image-area" class="border rounded-lg p-3 flex flex-col items-center justify-between bg-gray-50 dark:bg-gray-800/50">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Original</span>
                        <div class="flex-grow w-full flex items-center justify-center my-2">
                            <img id="original-image" class="max-w-full max-h-48 object-contain hidden">
                            <p id="original-placeholder-text" class="text-xs text-center text-gray-400">Gambar asli akan muncul disini.</p>
                        </div>
                        <span id="original-dimension" class="text-xs text-gray-500 mt-2">---</span>
                    </div>
                    <div id="enhanced-image-area" class="border rounded-lg p-3 flex flex-col items-center justify-between bg-gray-50 dark:bg-gray-800/50">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Enhanced (4x)</span>
                        <div class="flex-grow w-full flex items-center justify-center my-2">
                            <img id="enhanced-image" class="max-w-full max-h-48 object-contain hidden">
                            <p id="enhanced-placeholder-text" class="text-xs text-center text-gray-400">Hasil upscale akan muncul disini.</p>
                        </div>
                         <span id="enhanced-dimension" class="text-xs text-gray-500 mt-2">---</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/image-upscaler.js')
@endpush
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-xl sm:text-2xl font-semibold mb-1 select-none dark:text-white">Hai, Selamat Pagi <span role="img">👋</span></h1>
    <p class="text-gray-600 dark:text-gray-300 mb-4 sm:mb-6 text-sm sm:text-base select-none">Apa yang akan kamu lakukan hari ini?</p>

    <form class="mb-4 sm:mb-6 max-w-md" onsubmit="return false;">
        <label class="sr-only" for="search">Search AI</label>
        <input class="w-full rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-2 px-3 sm:py-2 sm:px-4 text-sm text-gray-500 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" id="search" placeholder="Search AI" type="search"/>
    </form>

    <div class="flex flex-wrap gap-2 mb-6 sm:mb-8 select-none">
        <button class="filter-category-btn bg-blue-200 dark:bg-blue-900 text-blue-700 dark:text-blue-200 rounded-md px-3 py-1 text-xs sm:text-sm font-semibold" type="button">All</button>
        <button class="filter-category-btn bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-1 text-xs sm:text-sm text-gray-700 dark:text-gray-300" type="button">Image</button>
        <button class="filter-category-btn bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-1 text-xs sm:text-sm text-gray-700 dark:text-gray-300" type="button">Copywriting</button>
        <button class="filter-category-btn bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md px-3 py-1 text-xs sm:text-sm text-gray-700 dark:text-gray-300" type="button">Document</button>
    </div>

    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4" id="ai-tools-grid">
        
        {{-- Card untuk Grammar Checker --}}
        <a href="{{ url('grammar') }}" class="block ai-tool-card" data-category="Copywriting">
            <article class="flex items-center gap-3 sm:gap-4 bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-gray-100 dark:bg-gray-700"><i class="fas fa-pen-nib text-blue-500 text-xl"></i></div>
                <div>
                    <h3 class="text-gray-900 dark:text-white font-semibold text-sm">Grammar Checker</h3>
                    <p class="text-xs text-gray-400">Perbaiki teks anda agar lebih baik</p>
                </div>
            </article>
        </a>

        {{-- Card untuk Upscaling Image --}}
        <a href="{{ url('upscaling') }}" class="block ai-tool-card" data-category="Image">
            <article class="flex items-center gap-3 sm:gap-4 bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-gray-100 dark:bg-gray-700"><i class="fas fa-expand-arrows-alt text-green-500 text-xl"></i></div>
                <div>
                    <h3 class="text-gray-900 dark:text-white font-semibold text-sm">Upscaling Image</h3>
                    <p class="text-xs text-gray-400">Tingkatkan resolusi gambar</p>
                </div>
            </article>
        </a>

        {{-- Card untuk Teks Summarizer --}}
        <a href="{{ url('text-summarizer') }}" class="block ai-tool-card" data-category="Copywriting">
            <article class="flex items-center gap-3 sm:gap-4 bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-gray-100 dark:bg-gray-700"><i class="fas fa-compress-alt text-purple-500 text-xl"></i></div>
                <div>
                    <h3 class="text-gray-900 dark:text-white font-semibold text-sm">Teks Summarizer</h3>
                    <p class="text-xs text-gray-400">Ringkas teks panjang jadi singkat</p>
                </div>
            </article>
        </a>

        {{-- Card untuk Remove Background --}}
        <a href="{{ url('remove-background') }}" class="block ai-tool-card" data-category="Image">
            <article class="flex items-center gap-3 sm:gap-4 bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-gray-100 dark:bg-gray-700"><i class="fas fa-eraser text-pink-500 text-xl"></i></div>
                <div>
                    <h3 class="text-gray-900 dark:text-white font-semibold text-sm">Remove Background</h3>
                    <p class="text-xs text-gray-400">Hapus latar belakang gambar</p>
                </div>
            </article>
        </a>

        {{-- Card untuk Convert Word To PDF --}}
        <a href="{{ url('wordtopdf') }}" class="block ai-tool-card" data-category="Document">
            <article class="flex items-center gap-3 sm:gap-4 bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-gray-100 dark:bg-gray-700"><i class="fas fa-file-pdf text-red-500 text-xl"></i></div>
                <div>
                    <h3 class="text-gray-900 dark:text-white font-semibold text-sm">Convert Word To PDF</h3>
                    <p class="text-xs text-gray-400">Ubah dokumen Word ke PDF</p>
                </div>
            </article>
        </a>

    </section>
@endsection

@push('scripts')
    @vite('resources/js/dashboard-filter.js')
@endpush
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  
  {{-- CSRF Token, penting untuk keamanan form dan AJAX --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Judul halaman dinamis, dengan judul default jika tidak diatur --}}
  <title>@yield('title', 'AI Tools') - AlinAja</title>

  {{-- Memuat CSS dan JS utama yang diproses oleh Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Font & Ikon Eksternal --}}
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
  
  {{-- Style dasar untuk font --}}
  <style> body { font-family: 'Inter', sans-serif; } </style>

  {{-- 
    Skrip inline untuk menerapkan dark mode sesegera mungkin 
    untuk mencegah "flash of unstyled content" (FOUC) 
  --}}
  <script>
    (function() {
      if (localStorage.getItem('darkMode') === 'true') {
        document.documentElement.classList.add('dark');
      }
    })();
  </script>

  {{-- Slot untuk CSS spesifik per halaman jika diperlukan --}}
  @stack('styles')
 </head>
 
 <body class="bg-[#f9f9f9] dark:bg-gray-900 min-h-screen flex flex-col sm:flex-row transition-colors duration-300">

  {{-- Sidebar Terpusat --}}
  <aside class="w-full sm:w-64 bg-white dark:bg-gray-800 border-b sm:border-b-0 sm:border-r border-gray-200 dark:border-gray-700 flex flex-row sm:flex-col justify-between sm:justify-between">
   <div class="flex flex-row sm:flex-col w-full sm:w-auto">
    <div class="flex items-center gap-3 sm:gap-4 px-2 sm:px-6 py-3 sm:py-3">
     <a href="{{ url('/') }}" class="flex items-center gap-3">
<img alt="AlinAja Logo" class="w-8 h-8 sm:w-10 sm:h-10 object-contain rounded-md" src="https://i.ibb.co/FqgHX6c3/Blue-Modern-AI-Technology-Logo.png"/>
        <span class="font-extrabold text-base sm:text-lg select-none dark:text-white">AlinAja</span>
     </a>
    </div>
    <nav class="mt-0 sm:mt-3 px-2 sm:px-6 space-x-2 sm:space-x-0 sm:space-y-2 text-sm text-gray-400 font-semibold select-none flex sm:flex-col overflow-x-auto sm:overflow-visible w-full sm:w-auto">
     <div class="uppercase tracking-wide hidden sm:block dark:text-gray-300 text-xs">Overview</div>
     
     @php
       $navLinks = [
           ['route' => '/', 'icon' => 'fa-th-large', 'label' => 'Dashboard'],
           ['route' => 'grammar', 'icon' => 'fa-pen-nib', 'label' => 'Grammar Checker'],
           ['route' => 'upscaling', 'icon' => 'fa-image', 'label' => 'Upscaling Image'],
           ['route' => 'text-summarizer', 'icon' => 'fa-network-wired', 'label' => 'Teks Summarizer'],
           ['route' => 'remove-background', 'icon' => 'fa-eraser', 'label' => 'Remove Background'],
           ['route' => 'wordtopdf', 'icon' => 'fa-file-pdf', 'label' => 'Convert Word To PDF'],
       ];
     @endphp

     @foreach ($navLinks as $link)
      @php
        // Cek apakah link aktif. Khusus untuk dashboard, cek URL root ('/').
        $isActive = ($link['route'] === '/') ? request()->is('/') : request()->is(ltrim($link['route'], '/').'*');
      @endphp
      <a href="{{ url($link['route']) }}" 
         class="hidden sm:flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors duration-150 ease-in-out
                {{ $isActive ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
        <i class="fas {{ $link['icon'] }} fa-fw w-5 h-5 {{ $isActive ? '' : 'text-gray-400' }}"></i>
        <span>{{ $link['label'] }}</span>
      </a>
     @endforeach
    </nav>
   </div>

   {{-- Tombol Dark Mode --}}
   <div class="p-3">
     <button aria-label="Toggle dark mode" id="darkModeToggle" class="p-3 text-xl text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors sm:p-4 w-full flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
      <i class="fas fa-moon"></i>
     </button>
   </div>
  </aside>
  
  {{-- Konten Utama yang Dinamis dari setiap halaman --}}
  <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto w-full">
    @yield('content')
  </main>

  {{-- Skrip Umum dan Skrip Halaman Spesifik --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const darkModeToggle = document.getElementById('darkModeToggle');
      if (darkModeToggle) {
          const icon = darkModeToggle.querySelector('i');
          // Set ikon awal berdasarkan class di <html>
          if (document.documentElement.classList.contains('dark')) {
              icon.classList.replace('fa-moon', 'fa-sun');
          }

          darkModeToggle.addEventListener('click', function() {
            document.documentElement.classList.toggle('dark');
            if (document.documentElement.classList.contains('dark')) {
              icon.classList.replace('fa-moon', 'fa-sun');
              localStorage.setItem('darkMode', 'true');
            } else {
              icon.classList.replace('fa-sun', 'fa-moon');
              localStorage.setItem('darkMode', 'false');
            }
          });
      }
    });
  </script>
  
  {{-- Slot untuk JS spesifik per halaman --}}
  @stack('scripts')
 </body>
</html>
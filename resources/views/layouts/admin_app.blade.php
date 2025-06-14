<!DOCTYPE html>
<html lang="en" class="light"> {{-- Default ke light, bisa dikontrol JS --}}
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>@yield('title', 'Admin Panel') - AlinAja</title>

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class', // Mengaktifkan dark mode berdasarkan class 'dark' pada elemen html
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    {{-- Google Fonts (Inter) --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Styling tambahan untuk scrollbar agar lebih modern (opsional) */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        /* Dark mode scrollbar */
        html.dark ::-webkit-scrollbar-track {
            background: #2d3748; /* gray-800 */
        }
        html.dark ::-webkit-scrollbar-thumb {
            background: #4a5568; /* gray-600 */
        }
        html.dark ::-webkit-scrollbar-thumb:hover {
            background: #718096; /* gray-500 */
        }
    </style>

    @stack('styles') {{-- Untuk CSS spesifik per halaman --}}
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased">

    <div class="min-h-screen flex flex-col sm:flex-row">
        {{-- Sidebar Admin --}}
        <aside class="w-full sm:w-64 bg-white dark:bg-gray-800 border-b sm:border-b-0 sm:border-r border-gray-200 dark:border-gray-700 transition-colors duration-300 ease-in-out">
            <div class="flex flex-col h-full">
                {{-- Logo & Nama Admin Panel --}}
                <div class="flex items-center justify-center sm:justify-start gap-3 px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <img alt="AlinAja Logo" class="w-8 h-8 sm:w-10 sm:h-10" src="https://i.ibb.co/FqgHX6c3/Blue-Modern-AI-Technology-Logo.png"/>
                    <div>
                        <span class="font-extrabold text-lg select-none text-gray-800 dark:text-white">AlinAja</span>
                        <span class="block text-xs text-blue-600 dark:text-blue-400 font-semibold">Admin Panel</span>
                    </div>
                </div>

                {{-- Navigasi Admin --}}
                <nav class="mt-4 px-3 space-y-1 flex-grow">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-sm 
                        {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}
                        transition-colors duration-150 ease-in-out">
                        <i class="fas fa-tachometer-alt fa-fw w-5 h-5"></i>
                        Dashboard
                    </a>
                    {{-- Contoh link lain (bisa Anda tambahkan nanti) --}}
                    {{--
                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors duration-150 ease-in-out">
                        <i class="fas fa-users fa-fw w-5 h-5"></i>
                        Manajemen User
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors duration-150 ease-in-out">
                        <i class="fas fa-cogs fa-fw w-5 h-5"></i>
                        Pengaturan
                    </a>
                    --}}
                </nav>

                {{-- Footer Sidebar (Dark Mode Toggle & User Info) --}}
                <div class="mt-auto p-3 border-t border-gray-200 dark:border-gray-700">
                    {{-- Jika ada user login admin --}}
                    @auth
                        <div class="mb-3 text-center sm:text-left">
                            <p class="text-sm font-medium text-gray-800 dark:text-white">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="flex items-center justify-center gap-2 w-full px-3 py-2 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-700 dark:hover:text-red-200 transition-colors duration-150 ease-in-out">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    @endauth

                    <button aria-label="Toggle dark mode" id="adminDarkModeToggle" class="mt-3 w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 ease-in-out">
                        <i class="fas fa-moon fa-fw"></i>
                        <span>Toggle Mode</span>
                    </button>
                </div>
            </div>
        </aside>

        {{-- Konten Utama --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 p-4 sm:p-6 transition-colors duration-300 ease-in-out">
                <div class="container mx-auto">
                     <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-white">
                        @yield('page_title', 'Selamat Datang Admin')
                    </h1>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-900 p-4 sm:p-6 lg:p-8 transition-colors duration-300 ease-in-out">
                <div class="container mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        // Dark Mode Toggle Logic
        const darkModeToggleAdmin = document.getElementById('adminDarkModeToggle');
        const htmlElementAdmin = document.documentElement;

        // Inisialisasi tema saat halaman dimuat
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            htmlElementAdmin.classList.add('dark');
            if (darkModeToggleAdmin) {
                darkModeToggleAdmin.querySelector('i').classList.replace('fa-moon', 'fa-sun');
            }
        } else {
            htmlElementAdmin.classList.remove('dark');
             if (darkModeToggleAdmin) {
                darkModeToggleAdmin.querySelector('i').classList.replace('fa-sun', 'fa-moon');
            }
        }

        if (darkModeToggleAdmin) {
            darkModeToggleAdmin.addEventListener('click', function() {
                htmlElementAdmin.classList.toggle('dark');
                const icon = this.querySelector('i');
                if (htmlElementAdmin.classList.contains('dark')) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                    localStorage.setItem('darkMode', 'true');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                    localStorage.setItem('darkMode', 'false');
                }
            });
        }
    </script>
    @stack('scripts') {{-- Untuk JS spesifik per halaman --}}
</body>
</html>
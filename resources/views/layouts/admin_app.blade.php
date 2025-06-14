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
                    <img alt="AlinAja Logo" class="w-8 h-8 sm:w-10 sm:h-10 object-contain rounded-md" src="https://i.ibb.co/FqgHX6c3/Blue-Modern-AI-Technology-Logo.png"/>
                    <div>
                        <span class="font-extrabold text-lg select-none text-gray-800 dark:text-white">AlinAja</span>
                        <span class="block text-xs text-blue-600 dark:text-blue-400 font-semibold">Admin Panel</span>
                    </div>
                </div>

                {{-- Navigasi Admin --}}
                <nav class="mt-4 px-3 space-y-1 flex-grow">
                    {{-- Dashboard --}}
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-sm 
                        {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}
                        transition-colors duration-150 ease-in-out">
                        <i class="fas fa-tachometer-alt fa-fw w-5 h-5"></i>
                        Dashboard
                    </a>

                    {{-- Manajemen User --}}
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-sm 
                        {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}
                        transition-colors duration-150 ease-in-out">
                        <i class="fas fa-users fa-fw w-5 h-5"></i>
                        Manajemen User
                    </a>

                    {{-- Divider --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

                    {{-- Quick Actions --}}
                    <div class="px-3 py-2">
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Quick Actions</p>
                    </div>

                    <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900 hover:text-green-700 dark:hover:text-green-300 transition-colors duration-150 ease-in-out">
                        <i class="fas fa-user-plus fa-fw w-5 h-5"></i>
                        Tambah Admin
                    </a>
                </nav>

                {{-- Footer Sidebar (Dark Mode Toggle & User Info) --}}
                <div class="mt-auto p-3 border-t border-gray-200 dark:border-gray-700">
                    {{-- Jika ada user login admin --}}
                    @auth
                        <div class="mb-3 text-center sm:text-left">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white">{{ Auth::user()->name }}</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                            @if(Auth::user()->is_admin)
                                <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">
                                    Super Admin
                                </span>
                            @endif
                        </div>

                        {{-- User Menu Dropdown --}}
                        <div class="space-y-1">
                            <a href="#" class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 ease-in-out">
                                <i class="fas fa-user-circle fa-fw"></i>
                                Profil Saya
                            </a>

                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                               class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-700 dark:hover:text-red-200 transition-colors duration-150 ease-in-out">
                                <i class="fas fa-sign-out-alt fa-fw"></i>
                                Logout
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>
                    @endauth

                    {{-- Dark Mode Toggle --}}
                    <button aria-label="Toggle dark mode" id="adminDarkModeToggle" class="mt-3 w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 ease-in-out">
                        <i class="fas fa-moon fa-fw"></i>
                        <span class="hidden sm:inline">Toggle Mode</span>
                    </button>

                    {{-- Version Info --}}
                    <div class="mt-3 text-center">
                        <p class="text-xs text-gray-400 dark:text-gray-500">AlinAja v1.0.0</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Konten Utama --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Header --}}
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-300 ease-in-out">
                <div class="flex items-center justify-between px-4 sm:px-6 py-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-white">
                            @yield('page_title', 'Selamat Datang Admin')
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            @yield('page_description', 'Kelola sistem AlinAja dengan mudah')
                        </p>
                    </div>

                    {{-- Header Actions --}}
                    <div class="flex items-center gap-3">
                        {{-- Notification Bell --}}
                        <button class="relative p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-150 ease-in-out">
                            <i class="fas fa-bell"></i>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        {{-- Mobile Menu Toggle --}}
                        <button class="sm:hidden p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-150 ease-in-out">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>

                {{-- Breadcrumb (Optional) --}}
                @hasSection('breadcrumb')
                    <div class="px-4 sm:px-6 pb-4">
                        <nav class="flex text-sm text-gray-600 dark:text-gray-400">
                            @yield('breadcrumb')
                        </nav>
                    </div>
                @endif
            </header>

            {{-- Main Content --}}
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-900 transition-colors duration-300 ease-in-out">
                <div class="p-4 sm:p-6 lg:p-8">
                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg relative" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <span>{{ session('warning') }}</span>
                            </div>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="mb-6 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg relative" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                <span>{{ session('info') }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Page Content --}}
                    @yield('content')
                </div>
            </main>

            {{-- Footer --}}
            <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 transition-colors duration-300 ease-in-out">
                <div class="flex flex-col sm:flex-row items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                    <p>&copy; {{ date('Y') }} AlinAja. All rights reserved.</p>
                    <div class="flex items-center gap-4 mt-2 sm:mt-0">
                        <span>Server Time: {{ now()->format('H:i:s') }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        // Dark Mode Toggle Logic
        const darkModeToggleAdmin = document.getElementById('adminDarkModeToggle');
        const htmlElementAdmin = document.documentElement;

        // Inisialisasi tema saat halaman dimuat
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            htmlElementAdmin.classList.add('dark');
            if (darkModeToggleAdmin) {
                darkModeToggleAdmin.querySelector('i').classList.replace('fa-moon', 'fa-sun');
                darkModeToggleAdmin.querySelector('span').textContent = 'Light Mode';
            }
        } else {
            htmlElementAdmin.classList.remove('dark');
            if (darkModeToggleAdmin) {
                darkModeToggleAdmin.querySelector('i').classList.replace('fa-sun', 'fa-moon');
                darkModeToggleAdmin.querySelector('span').textContent = 'Dark Mode';
            }
        }

        if (darkModeToggleAdmin) {
            darkModeToggleAdmin.addEventListener('click', function() {
                htmlElementAdmin.classList.toggle('dark');
                const icon = this.querySelector('i');
                const text = this.querySelector('span');
                
                if (htmlElementAdmin.classList.contains('dark')) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                    if (text) text.textContent = 'Light Mode';
                    localStorage.setItem('darkMode', 'true');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                    if (text) text.textContent = 'Dark Mode';
                    localStorage.setItem('darkMode', 'false');
                }
            });
        }

        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('[role="alert"]');
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.transition = 'opacity 0.5s ease-in-out';
                    message.style.opacity = '0';
                    setTimeout(function() {
                        message.remove();
                    }, 500);
                }, 5000);
            });
        });

        // Active menu highlighting
        document.addEventListener('DOMContentLoaded', function() {
            const currentUrl = window.location.pathname;
            const menuLinks = document.querySelectorAll('nav a[href]');
            
            menuLinks.forEach(function(link) {
                if (link.getAttribute('href') === currentUrl) {
                    link.classList.add('bg-blue-600', 'text-white', 'shadow-md');
                    link.classList.remove('text-gray-600', 'dark:text-gray-300');
                }
            });
        });

        // Tooltip initialization (if you want to add tooltips)
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipElements = document.querySelectorAll('[data-tooltip]');
            tooltipElements.forEach(function(element) {
                element.addEventListener('mouseenter', function() {
                    // Add tooltip logic here if needed
                });
            });
        });
    </script>

    {{-- Chart.js for dashboard charts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('scripts') {{-- Untuk JS spesifik per halaman --}}
</body>
</html>
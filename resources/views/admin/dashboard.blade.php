@extends('layouts.admin_app') {{-- Menggunakan layout admin baru --}}

@section('title', 'Dashboard Statistik') {{-- Judul spesifik halaman --}}

@section('page_title', 'Dashboard Statistik Penggunaan AI') {{-- Judul yang akan tampil di header konten --}}

@section('content')
<div class="space-y-8">
    {{-- Statistik Dasar --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300">
            <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400 mb-1">Total Penggunaan</h3>
            <p class="text-4xl font-bold text-blue-600 dark:text-blue-400">{{ $totalUsage ?? '0' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300">
            <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400 mb-1">Penggunaan Sukses</h3>
            <p class="text-4xl font-bold text-green-600 dark:text-green-400">{{ $successfulUsage ?? '0' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300">
            <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400 mb-1">Penggunaan Gagal</h3>
            <p class="text-4xl font-bold text-red-600 dark:text-red-400">{{ $failedUsage ?? '0' }}</p>
        </div>
    </section>

    {{-- Grafik --}}
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Penggunaan per Tool</h3>
            <div class="chart-container" style="height: 350px;"> {{-- Beri tinggi eksplisit --}}
                <canvas id="usageByToolChart"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Tren Penggunaan Harian (7 Hari Terakhir)</h3>
            <div class="chart-container" style="height: 350px;"> {{-- Beri tinggi eksplisit --}}
                <canvas id="dailyUsageChart"></canvas>
            </div>
        </div>
    </section>

    {{-- Tabel Detail Log Penggunaan --}}
    <section class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg">
        <h3 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Log Penggunaan Terbaru (10 Terakhir)</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tool</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Waktu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Waktu Proses (ms)</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentLogs ?? [] as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $log->tool_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($log->status == 'success')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                    Success
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                    Error
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $log->processing_time_ms ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-300">Tidak ada data log penggunaan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> {{-- Chart.js sudah ada di layout, tapi boleh saja di sini juga --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const usageByToolData = {!! isset($usageByTool) ? json_encode($usageByTool) : '{}' !!};
        const dailyUsageData = {!! isset($dailyUsage) ? json_encode($dailyUsage) : '{}' !!};
        const isDarkMode = document.documentElement.classList.contains('dark');
        const textColor = isDarkMode ? 'rgb(209, 213, 219)' : 'rgb(55, 65, 81)'; // gray-300 / gray-700
        const gridColor = isDarkMode ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';

        const usageByToolChartEl = document.getElementById('usageByToolChart');
        if (usageByToolChartEl && Object.keys(usageByToolData).length > 0) {
            new Chart(usageByToolChartEl.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: Object.keys(usageByToolData),
                    datasets: [{
                        label: 'Jumlah Penggunaan',
                        data: Object.values(usageByToolData),
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.7)', // blue-500
                            'rgba(16, 185, 129, 0.7)',// green-500
                            'rgba(239, 68, 68, 0.7)',  // red-500
                            'rgba(245, 158, 11, 0.7)',// amber-500
                            'rgba(139, 92, 246, 0.7)', // violet-500
                            'rgba(236, 72, 153, 0.7)' // pink-500
                        ],
                        borderColor: isDarkMode ? 'rgb(31, 41, 55)' : '#fff', // gray-800 or white
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor }
                        }
                    }
                }
            });
        } else if (usageByToolChartEl) {
            usageByToolChartEl.parentElement.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 py-10">Data penggunaan per tool belum tersedia.</p>';
        }

        const dailyUsageChartEl = document.getElementById('dailyUsageChart');
        if (dailyUsageChartEl && Object.keys(dailyUsageData).length > 0) {
            new Chart(dailyUsageChartEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels: Object.keys(dailyUsageData),
                    datasets: [{
                        label: 'Total Penggunaan Harian',
                        data: Object.values(dailyUsageData),
                        fill: true,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1,
                        pointBackgroundColor: 'rgb(75, 192, 192)',
                        pointBorderColor: isDarkMode ? 'rgb(31, 41, 55)' : '#fff',
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: 'rgb(75, 192, 192)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: textColor },
                            grid: { color: gridColor }
                        },
                        x: {
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        }
                    },
                    plugins: {
                        legend: { labels: { color: textColor } }
                    }
                }
            });
        } else if (dailyUsageChartEl) {
            dailyUsageChartEl.parentElement.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 py-10">Data penggunaan harian belum tersedia.</p>';
        }
    });
</script>
@endpush
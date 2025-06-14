<?php
<?php

namespace App\Http\Controllers;

use App\Models\ToolUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Statistik dasar
        $totalUsage = ToolUsageLog::count();
        $successfulUsage = ToolUsageLog::where('status', 'success')->count();
        $failedUsage = ToolUsageLog::where('status', 'error')->count();

        // Penggunaan per tool (untuk pie chart atau bar chart)
        $usageByTool = ToolUsageLog::select('tool_name', DB::raw('count(*) as total'))
            ->groupBy('tool_name')
            ->orderBy('total', 'desc')
            ->pluck('total', 'tool_name'); // Hasil: ['Grammar Checker' => 50, 'Upscaling Image' => 30]

        // Penggunaan harian (untuk line chart) - Contoh 7 hari terakhir
        $dailyUsage = ToolUsageLog::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('total', 'date'); // Hasil: ['2024-05-20' => 10, '2024-05-21' => 15]

        // Log penggunaan terbaru (10 terakhir) - TAMBAHAN BARU
        $recentLogs = ToolUsageLog::latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsage',
            'successfulUsage',
            'failedUsage',
            'usageByTool',
            'dailyUsage',
            'recentLogs' // TAMBAHAN BARU
        ));
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ToolUsageLog; // (Langkah 1) Import model ToolUsageLog
use Illuminate\Support\Facades\Auth; // (Langkah 1) Import Auth
use Illuminate\Support\Str; // (Langkah 1) Import Str

class SummarizerController extends Controller
{
    public function summarize(Request $request)
    {
        $startTime = microtime(true); // (Langkah 2) Catat waktu mulai
        $processingTimeMs = 0; // Inisialisasi

        $request->validate([
            'text' => 'required|string|min:10',
        ]);

        $textToSummarize = $request->input('text');
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            // (Langkah 4) Catat error API Key tidak ada
            ToolUsageLog::create([
                'tool_name' => 'Text Summarizer',
                'user_id' => Auth::id(), // Akan null jika tidak ada user login
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Configuration', 'message' => 'Kunci API Gemini tidak dikonfigurasi.']),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Kunci API Gemini tidak dikonfigurasi di server.'
            ], 500);
        }

        $geminiApiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
        $prompt = "Anda adalah asisten AI yang ahli dalam meringkas teks. Berikan ringkasan yang jelas, padat, dan akurat dari teks berikut, ambil poin-poin utamanya saja. Teks asli:\n\n\"{$textToSummarize}\"\n\nHasil Ringkasan (dalam bahasa Indonesia):";
        $logDetails = ['input_char_length' => Str::length($textToSummarize)]; // Detail awal untuk log

        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->post($geminiApiUrl, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $logDetails['api_response_snippet'] = Str::limit(json_encode($responseData), 200);

                if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                    $summarizedText = $responseData['candidates'][0]['content']['parts'][0]['text'];
                    $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                    // (Langkah 3) Catat penggunaan sukses
                    ToolUsageLog::create([
                        'tool_name' => 'Text Summarizer',
                        'user_id' => Auth::id(),
                        'status' => 'success',
                        'details' => json_encode(array_merge($logDetails, ['output_char_length' => Str::length($summarizedText)])),
                        'processing_time_ms' => $processingTimeMs,
                    ]);
                    return response()->json([
                        'status' => 'success',
                        'summarizedText' => $summarizedText
                    ]);
                } else {
                    Log::error('Gemini API response structure unexpected: ', $responseData);
                    $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                    // (Langkah 4) Catat error struktur API tidak sesuai
                    ToolUsageLog::create([
                        'tool_name' => 'Text Summarizer',
                        'user_id' => Auth::id(),
                        'status' => 'error',
                        'details' => json_encode(array_merge($logDetails, ['error_type' => 'Unexpected API Response', 'message' => 'Struktur respons API tidak sesuai.'])),
                        'processing_time_ms' => $processingTimeMs,
                    ]);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Struktur respons dari API tidak sesuai atau tidak ada hasil.',
                        'details' => $responseData
                    ], 500);
                }
            } else {
                Log::error('Gemini API request failed: ', ['status' => $response->status(), 'body' => $response->body()]);
                $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                // (Langkah 4) Catat error dari API
                ToolUsageLog::create([
                    'tool_name' => 'Text Summarizer',
                    'user_id' => Auth::id(),
                    'status' => 'error',
                    'details' => json_encode(array_merge($logDetails, ['error_type' => 'API Error', 'message' => 'Gagal menghubungi API Gemini.', 'status_code' => $response->status(), 'api_body' => $response->json() ?? $response->body()])),
                    'processing_time_ms' => $processingTimeMs,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menghubungi API Gemini: Error ' . $response->status(),
                    'details' => $response->json() ?? ['body' => $response->body()]
                ], $response->status());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('ConnectionException during Gemini API call: ' . $e->getMessage());
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            // (Langkah 4) Catat error ConnectionException
            ToolUsageLog::create([
                'tool_name' => 'Text Summarizer',
                'user_id' => Auth::id(),
                'status' => 'error',
                'details' => json_encode(array_merge($logDetails, ['error_type' => 'Connection Exception', 'message' => Str::limit($e->getMessage(), 255)])),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke API Gemini: ' . Str::limit($e->getMessage(), 100)
            ], 500);
        } catch (\Exception $e) {
            Log::error('Generic Exception during Gemini API call: ' . $e->getMessage(), ['trace' => Str::limit($e->getTraceAsString(), 500)]);
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            // (Langkah 4) Catat error exception umum
            ToolUsageLog::create([
                'tool_name' => 'Text Summarizer',
                'user_id' => Auth::id(),
                'status' => 'error',
                'details' => json_encode(array_merge($logDetails, ['error_type' => 'Exception', 'message' => Str::limit($e->getMessage(), 255), 'file' => $e->getFile(), 'line' => $e->getLine()])),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan internal saat memproses permintaan: ' . Str::limit($e->getMessage(), 100)
            ], 500);
        }
    }
}

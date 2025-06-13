<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Exception;
use PDF; // Import Dompdf Facade
use App\Models\ToolUsageLog; // (Langkah 1) Import model ToolUsageLog
use Illuminate\Support\Facades\Auth; // (Langkah 1) Import Auth
use Illuminate\Support\Facades\Log; // (Langkah 1) Import Log untuk error internal
use Illuminate\Support\Str; // (Langkah 1) Import Str untuk Str::limit

class GrammarController extends Controller
{
    /**
     * Memeriksa tata bahasa teks menggunakan Google Gemini API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        $startTime = microtime(true); // (Langkah 2) Catat waktu mulai

        $request->validate([
            'text' => 'required|string',
        ]);

        $inputText = $request->input('text');
        $processingTimeMs = 0; // Inisialisasi

        // Inisialisasi Guzzle Client.
        $client = new Client([
            'verify' => false // Ini adalah solusi sementara untuk masalah sertifikat di lokal.
            // Hapus baris ini jika masalah sertifikat sudah teratasi!
        ]);

        $geminiApiUrl = env('GEMINI_API_URL');
        $geminiApiKey = env('GEMINI_API_KEY');

        if (empty($geminiApiKey)) {
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            // (Langkah 4) Catat error API Key tidak ada
            ToolUsageLog::create([
                'tool_name' => 'Grammar Checker',
                'user_id' => Auth::id(), // Akan null jika tidak ada user login
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Configuration', 'message' => 'Gemini API Key tidak ditemukan di .env.']),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gemini API Key tidak ditemukan di .env. Harap tambahkan API Key Anda dari Google AI Studio.',
            ], 500);
        }

        $fullGeminiApiUrl = $geminiApiUrl . $geminiApiKey;

        try {
            $prompt = "Perbaiki tata bahasa, ejaan, dan tanda baca dari teks berikut dalam Bahasa Indonesia, dan berikan hanya teks yang sudah diperbaiki tanpa tambahan komentar atau pengantar:\n\n" . $inputText;

            $requestBody = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
            ];

            $response = $client->post($fullGeminiApiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestBody,
                'timeout' => 60,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $correctedText = $inputText;
            $isSuccess = false;

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $correctedText = $data['candidates'][0]['content']['parts'][0]['text'];
                $isSuccess = true;

                $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                // (Langkah 3) Catat penggunaan sukses
                ToolUsageLog::create([
                    'tool_name' => 'Grammar Checker',
                    'user_id' => Auth::id(),
                    'status' => 'success',
                    'details' => json_encode([
                        'input_char_length' => Str::length($inputText),
                        'output_char_length' => Str::length($correctedText),
                        // 'gemini_response_snippet' => Str::limit(json_encode($data), 200) // Opsional: cuplikan respons API
                    ]),
                    'processing_time_ms' => $processingTimeMs,
                ]);
                return response()->json([
                    'status' => 'success',
                    'correctedText' => $correctedText,
                    // 'details' => $data, // Hapus atau sesuaikan untuk produksi
                ]);
            } elseif (isset($data['error'])) {
                $errorMessage = 'Error dari Gemini API: ' . $data['error']['message'];
                $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                // (Langkah 4) Catat error dari Gemini API
                ToolUsageLog::create([
                    'tool_name' => 'Grammar Checker',
                    'user_id' => Auth::id(),
                    'status' => 'error',
                    'details' => json_encode(['error_type' => 'Gemini API Error', 'message' => $errorMessage, 'api_response' => $data]),
                    'processing_time_ms' => $processingTimeMs,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMessage,
                    // 'details' => $data,
                ], 500);
            } else {
                $errorMessage = 'Format respons dari Gemini tidak terduga.';
                $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                // (Langkah 4) Catat error format respons tidak terduga
                ToolUsageLog::create([
                    'tool_name' => 'Grammar Checker',
                    'user_id' => Auth::id(),
                    'status' => 'error',
                    'details' => json_encode(['error_type' => 'Unexpected API Response', 'message' => $errorMessage, 'api_response' => $data]),
                    'processing_time_ms' => $processingTimeMs,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMessage,
                    // 'details' => $data,
                ], 500);
            }
        } catch (Exception $e) {
            Log::error('Grammar Check Exception: ' . $e->getMessage(), ['trace' => Str::limit($e->getTraceAsString(), 500)]);
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            // (Langkah 4) Catat error exception umum
            ToolUsageLog::create([
                'tool_name' => 'Grammar Checker',
                'user_id' => Auth::id(),
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Exception', 'message' => Str::limit($e->getMessage(), 255), 'file' => $e->getFile(), 'line' => $e->getLine()]),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memeriksa tata bahasa: ' . Str::limit($e->getMessage(), 100),
            ], 500);
        }
    }

    /**
     * Mengunduh teks yang sudah diperbaiki sebagai file PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadPdf(Request $request)
    {
        // Fungsi ini tidak secara langsung menggunakan tool AI, 
        // jadi logging penggunaan tool mungkin tidak relevan di sini,
        // kecuali Anda ingin melacak seberapa sering fitur unduh PDF ini digunakan.
        // Jika iya, Anda bisa menambahkan log serupa dengan 'tool_name' => 'Grammar Checker PDF Download'

        $request->validate([
            'text' => 'required|string',
        ]);

        $textToPdf = $request->input('text');
        $fileName = 'Hasil_Grammar_Checker_' . date('Ymd_His') . '.pdf';

        $html = "
            <html>
            <head>
                <title>Hasil Grammar Checker</title>
                <style>
                    body { font-family: sans-serif; margin: 40px; line-height: 1.6; }
                    h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 20px; }
                    p { color: #555; }
                    pre {
                        background-color: #f8f8f8;
                        border: 1px solid #ddd;
                        padding: 15px;
                        border-radius: 5px;
                        white-space: pre-wrap; 
                        word-wrap: break-word;  
                        font-family: 'Courier New', Courier, monospace;
                        color: #333;
                    }
                    .footer { text-align: center; margin-top: 50px; font-size: 0.8em; color: #777; }
                </style>
            </head>
            <body>
                <h1>Hasil Pemeriksaan Tata Bahasa</h1>
                <p>Berikut adalah teks Anda yang sudah diperbaiki:</p>
                <pre>" . htmlspecialchars($textToPdf) . "</pre>
                <div class='footer'>Dokumen ini dibuat secara otomatis oleh AlinAja Grammar Checker pada " . date('d M Y H:i:s') . "</div>
            </body>
            </html>
        ";

        $pdf = PDF::loadHTML($html);
        return $pdf->download($fileName);
    }
}

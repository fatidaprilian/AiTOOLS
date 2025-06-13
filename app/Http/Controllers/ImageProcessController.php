<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\ToolUsageLog; // (Langkah 1) Import model ToolUsageLog
use Illuminate\Support\Facades\Auth; // (Langkah 1) Import Auth
use Illuminate\Support\Str; // (Langkah 1) Import Str untuk Str::limit

class ImageProcessController extends Controller
{
    public function removeBackground(Request $request)
    {
        $startTime = microtime(true); // (Langkah 2) Catat waktu mulai
        $processingTimeMs = 0; // Inisialisasi

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,webp|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            // (Langkah 4) Catat error validasi
            ToolUsageLog::create([
                'tool_name' => 'Remove Background',
                'user_id' => Auth::id(),
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Validation', 'message' => $validator->errors()->first(), 'errors_all' => $validator->errors()]),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 400);
        }

        $apiKey = env('REMOVEBG_API_KEY');
        $apiUrl = env('REMOVEBG_API_URL');

        if (!$apiKey) {
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            // (Langkah 4) Catat error API Key tidak ada
            ToolUsageLog::create([
                'tool_name' => 'Remove Background',
                'user_id' => Auth::id(),
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Configuration', 'message' => 'Kunci API Remove Background tidak dikonfigurasi.']),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json(['status' => 'error', 'message' => 'Kunci API Remove Background tidak dikonfigurasi di server.'], 500);
        }
        if (!$apiUrl) {
            $apiUrl = "https://api.stability.ai/v2beta/stable-image/edit/remove-background";
            Log::warning('REMOVEBG_API_URL tidak ditemukan di .env, menggunakan URL default.');
            // Tidak perlu log error ke ToolUsageLog di sini karena ini fallback, bukan error fatal dari user.
        }

        $imageFile = $request->file('image');
        $originalFileNameForLog = $imageFile->getClientOriginalName(); // Untuk logging

        try {
            $response = Http::withoutVerifying() // HANYA UNTUK DEVELOPMENT LOKAL JIKA SSL ERROR
                ->asMultipart()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                ])
                ->timeout(60)
                ->post($apiUrl, [
                    [
                        'name'     => 'image',
                        'contents' => fopen($imageFile->getPathname(), 'r'),
                        'filename' => $imageFile->getClientOriginalName()
                    ],
                    // Jika API mendukung parameter output_format, bisa ditambahkan di sini
                    // [
                    //     'name'     => 'output_format',
                    //     'contents' => 'png' // atau 'webp'
                    // ],
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $logDetails = ['api_response_snippet' => Str::limit(json_encode($responseData), 200)]; // Detail awal untuk log

                if (isset($responseData['artifacts']) && count($responseData['artifacts']) > 0 && isset($responseData['artifacts'][0]['base64'])) {
                    $base64Image = $responseData['artifacts'][0]['base64'];
                    $finishReason = $responseData['artifacts'][0]['finishReason'] ?? 'UNKNOWN';
                    $logDetails['finish_reason'] = $finishReason;

                    if ($finishReason === 'CONTENT_FILTERED') {
                        Log::warning('Stability AI Remove Background - Content filtered: ', $responseData);
                        $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                        ToolUsageLog::create([
                            'tool_name' => 'Remove Background',
                            'user_id' => Auth::id(),
                            'status' => 'error',
                            'details' => json_encode(array_merge($logDetails, ['error_type' => 'Content Filtered', 'message' => 'Konten gambar melanggar kebijakan.'])),
                            'processing_time_ms' => $processingTimeMs,
                        ]);
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Gagal menghapus background: Konten gambar melanggar kebijakan dan telah diburamkan oleh API.',
                            'details' => $responseData
                        ], 403);
                    }

                    if ($finishReason !== 'SUCCESS') {
                        Log::warning('Stability AI Remove Background - Finish reason not SUCCESS: ' . $finishReason, $responseData);
                        // Tetap lanjutkan jika ada base64, tapi log warning sudah cukup.
                    }

                    $contentTypeHeader = $response->header('Content-Type');
                    $imageType = 'png';
                    if ($contentTypeHeader && str_contains($contentTypeHeader, 'type=image/')) {
                        $imageType = explode('type=image/', $contentTypeHeader)[1];
                    }
                    $logDetails['output_image_type'] = $imageType;

                    $formattedBase64 = 'data:image/' . $imageType . ';base64,' . $base64Image;
                    $processed_dimensions = null;
                    try {
                        $tempImage = imagecreatefromstring(base64_decode($base64Image));
                        if ($tempImage !== false) {
                            $width = imagesx($tempImage);
                            $height = imagesy($tempImage);
                            imagedestroy($tempImage);
                            $processed_dimensions = ['width' => $width, 'height' => $height];
                            $logDetails['output_dimensions'] = $processed_dimensions;
                        } else {
                            Log::warning('Gagal membuat gambar dari base64 untuk mendapatkan dimensi (Remove BG).');
                        }
                    } catch (\Exception $e) {
                        Log::error('Error saat mendapatkan dimensi gambar (Remove BG): ' . $e->getMessage());
                    }

                    $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                    // (Langkah 3) Catat penggunaan sukses
                    ToolUsageLog::create([
                        'tool_name' => 'Remove Background',
                        'user_id' => Auth::id(),
                        'status' => 'success',
                        'details' => json_encode(array_merge($logDetails, ['original_filename' => $originalFileNameForLog])),
                        'processing_time_ms' => $processingTimeMs,
                    ]);
                    return response()->json([
                        'status' => 'success',
                        'processed_image_base64' => $formattedBase64,
                        'processed_dimensions' => $processed_dimensions
                    ]);
                } else {
                    // Fallback jika struktur 'artifacts' tidak ada
                    $possibleBase64Field = null;
                    if (isset($responseData['image_base64'])) $possibleBase64Field = $responseData['image_base64'];
                    elseif (isset($responseData['image'])) $possibleBase64Field = $responseData['image'];

                    if ($possibleBase64Field) {
                        $formattedBase64 = (str_starts_with($possibleBase64Field, 'data:image')) ? $possibleBase64Field : 'data:image/png;base64,' . $possibleBase64Field;
                        // Sulit mendapatkan dimensi di sini, jadi log sukses dengan info terbatas
                        $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                        ToolUsageLog::create([
                            'tool_name' => 'Remove Background',
                            'user_id' => Auth::id(),
                            'status' => 'success', // Anggap sukses jika ada base64, meskipun struktur berbeda
                            'details' => json_encode(array_merge($logDetails, ['original_filename' => $originalFileNameForLog, 'message' => 'Struktur API berbeda, namun gambar base64 ditemukan.'])),
                            'processing_time_ms' => $processingTimeMs,
                        ]);
                        return response()->json([
                            'status' => 'success',
                            'processed_image_base64' => $formattedBase64,
                            'processed_dimensions' => null
                        ]);
                    }

                    Log::error('Stability AI Remove Background - Unexpected response structure: ', $responseData);
                    $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                    ToolUsageLog::create([
                        'tool_name' => 'Remove Background',
                        'user_id' => Auth::id(),
                        'status' => 'error',
                        'details' => json_encode(array_merge($logDetails, ['error_type' => 'Unexpected API Response', 'message' => 'Struktur respons API tidak sesuai.'])),
                        'processing_time_ms' => $processingTimeMs,
                    ]);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Struktur respons dari API Stability AI tidak sesuai atau tidak ada hasil gambar.',
                        'details' => $responseData
                    ], 500);
                }
            } else {
                $errorBody = $response->json();
                Log::error('Stability AI Remove Background - API request failed: ', ['status' => $response->status(), 'body' => $errorBody]);
                $apiErrorMessage = 'Gagal menghubungi API Stability AI (Remove Background).';
                if (isset($errorBody['message'])) {
                    $apiErrorMessage = $errorBody['message'];
                } elseif (isset($errorBody['errors']) && is_array($errorBody['errors'])) {
                    $apiErrorMessage = implode(", ", $errorBody['errors']);
                }
                $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                ToolUsageLog::create([
                    'tool_name' => 'Remove Background',
                    'user_id' => Auth::id(),
                    'status' => 'error',
                    'details' => json_encode(['error_type' => 'API Request Failed', 'message' => $apiErrorMessage, 'status_code' => $response->status(), 'api_response_body' => $errorBody]),
                    'processing_time_ms' => $processingTimeMs,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => $apiErrorMessage . ' (Error ' . $response->status() . ')',
                    'details' => $errorBody
                ], $response->status());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('ConnectionException during Stability AI (Remove BG) call: ' . $e->getMessage());
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            ToolUsageLog::create([
                'tool_name' => 'Remove Background',
                'user_id' => Auth::id(),
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Connection Exception', 'message' => Str::limit($e->getMessage(), 255)]),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal terhubung ke API Stability AI (Remove BG): ' . Str::limit($e->getMessage(), 100)], 500);
        } catch (\Exception $e) {
            Log::error('Generic Exception during Stability AI (Remove BG) call: ' . $e->getMessage(), ['trace' => Str::limit($e->getTraceAsString(), 500)]);
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            ToolUsageLog::create([
                'tool_name' => 'Remove Background',
                'user_id' => Auth::id(),
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Exception', 'message' => Str::limit($e->getMessage(), 255), 'file' => $e->getFile(), 'line' => $e->getLine()]),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan internal: ' . Str::limit($e->getMessage(), 100)], 500);
        }
    }
}

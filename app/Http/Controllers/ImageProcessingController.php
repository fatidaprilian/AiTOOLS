<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Log; // Untuk logging error
use Illuminate\Support\Facades\Storage; // Untuk menyimpan file sementara jika diperlukan
use App\Models\ToolUsageLog; // (Langkah 1) Import model ToolUsageLog
use Illuminate\Support\Facades\Auth; // (Langkah 1) Import Auth
use Illuminate\Support\Str; // (Langkah 1) Import Str
use Illuminate\Support\Facades\Validator;

class ImageProcessingController extends Controller
{
    /**
     * Memproses gambar untuk upscaling menggunakan Stability AI Fast Upscaler API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upscale(Request $request)
    {
        $startTime = microtime(true); // (Langkah 2) Catat waktu mulai
        $processingTimeMs = 0; // Inisialisasi

        $validator = Validator::make($request->all(), [
            'image_file' => 'required|image|mimes:jpeg,png,jpg,webp|max:5000', // max 5MB
            'output_format' => 'required|in:jpeg,png,webp',
        ]);

        if ($validator->fails()) {
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            // (Langkah 4) Catat error validasi
            ToolUsageLog::create([
                'tool_name' => 'Upscaling Image',
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

        $imageFile = $request->file('image_file');
        $outputFormat = $request->input('output_format');
        $originalFileNameForLog = $imageFile->getClientOriginalName();

        $upscalingApiUrl = env('UPSCALING_API_URL');
        $upscalingApiKey = env('UPSCALING_API_KEY');

        if (empty($upscalingApiKey)) {
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            // (Langkah 4) Catat error API Key tidak ada
            ToolUsageLog::create([
                'tool_name' => 'Upscaling Image',
                'user_id' => Auth::id(),
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Configuration', 'message' => 'Stability AI API Key (Upscaling) tidak ditemukan.']),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Stability AI API Key tidak ditemukan di .env.',
            ], 500);
        }

        $client = new Client([
            'verify' => false, // Solusi sementara untuk masalah SSL di lokal. HAPUS ini di lingkungan produksi!
            'timeout' => 120,  // Tambahkan timeout lebih besar, upscaling bisa lama
        ]);

        try {
            list($originalWidth, $originalHeight) = getimagesize($imageFile->getPathname());

            $response = $client->post($upscalingApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $upscalingApiKey,
                    'Accept' => 'application/json',
                ],
                'multipart' => [
                    [
                        'name'     => 'image',
                        'contents' => fopen($imageFile->getPathname(), 'r'),
                        'filename' => $imageFile->getClientOriginalName(),
                        'headers'  => [
                            'Content-Type' => $imageFile->getMimeType(),
                        ],
                    ],
                    [
                        'name'     => 'output_format',
                        'contents' => $outputFormat,
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);
            $logDetails = ['api_response_snippet' => Str::limit($responseBody, 250)];


            if ($statusCode == 200) {
                if (isset($responseData['image'])) { // Stability AI Fast Upscaler (non-enterprise)
                    $upscaledImageBase64 = 'data:image/' . $outputFormat . ';base64,' . $responseData['image'];
                    $enhancedWidth = $originalWidth * 4; // Asumsi 4x upscale
                    $enhancedHeight = $originalHeight * 4;

                    $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                    // (Langkah 3) Catat penggunaan sukses
                    ToolUsageLog::create([
                        'tool_name' => 'Upscaling Image',
                        'user_id' => Auth::id(),
                        'status' => 'success',
                        'details' => json_encode(array_merge($logDetails, [
                            'original_filename' => $originalFileNameForLog,
                            'output_format' => $outputFormat,
                            'original_dimensions' => ['width' => $originalWidth, 'height' => $originalHeight],
                            'enhanced_dimensions' => ['width' => $enhancedWidth, 'height' => $enhancedHeight]
                        ])),
                        'processing_time_ms' => $processingTimeMs,
                    ]);
                    return response()->json([
                        'status' => 'success',
                        'upscaled_image_base64' => $upscaledImageBase64,
                        'original_filename' => $imageFile->getClientOriginalName(),
                        'output_extension' => $outputFormat,
                        'original_dimensions' => ['width' => $originalWidth, 'height' => $originalHeight],
                        'enhanced_dimensions' => ['width' => $enhancedWidth, 'height' => $enhancedHeight], // Perbaikan urutan
                    ]);
                } elseif (isset($responseData['artifacts']) && count($responseData['artifacts']) > 0 && isset($responseData['artifacts'][0]['base64'])) {
                    // Struktur untuk Stability AI Enterprise Upscaler atau API lain yang serupa
                    $base64Image = $responseData['artifacts'][0]['base64'];
                    $finishReason = $responseData['artifacts'][0]['finishReason'] ?? 'UNKNOWN';
                    $logDetails['finish_reason'] = $finishReason;

                    if ($finishReason === 'CONTENT_FILTERED') {
                        Log::warning('Stability AI Upscale - Content filtered: ', $responseData);
                        $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                        ToolUsageLog::create([
                            'tool_name' => 'Upscaling Image',
                            'user_id' => Auth::id(),
                            'status' => 'error',
                            'details' => json_encode(array_merge($logDetails, ['error_type' => 'Content Filtered', 'message' => 'Konten gambar melanggar kebijakan.'])),
                            'processing_time_ms' => $processingTimeMs,
                        ]);
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Gagal upscale gambar: Konten gambar melanggar kebijakan.',
                            'details' => $responseData
                        ], 403);
                    }
                    if ($finishReason !== 'SUCCESS') {
                        Log::warning('Stability AI Upscale - Finish reason not SUCCESS: ' . $finishReason, $responseData);
                    }

                    $upscaledImageBase64 = 'data:image/' . $outputFormat . ';base64,' . $base64Image;
                    $enhancedWidth = $originalWidth * 4; // Asumsi 4x upscale
                    $enhancedHeight = $originalHeight * 4;

                    $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                    ToolUsageLog::create([
                        'tool_name' => 'Upscaling Image',
                        'user_id' => Auth::id(),
                        'status' => 'success',
                        'details' => json_encode(array_merge($logDetails, [
                            'original_filename' => $originalFileNameForLog,
                            'output_format' => $outputFormat,
                            'original_dimensions' => ['width' => $originalWidth, 'height' => $originalHeight],
                            'enhanced_dimensions' => ['width' => $enhancedWidth, 'height' => $enhancedHeight]
                        ])),
                        'processing_time_ms' => $processingTimeMs,
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'upscaled_image_base64' => $upscaledImageBase64,
                        'original_filename' => $imageFile->getClientOriginalName(),
                        'output_extension' => $outputFormat,
                        'original_dimensions' => ['width' => $originalWidth, 'height' => $originalHeight],
                        'enhanced_dimensions' => ['width' => $enhancedWidth, 'height' => $enhancedHeight],
                    ]);
                } else {
                    $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                    // (Langkah 4) Catat error struktur API tidak sesuai
                    ToolUsageLog::create([
                        'tool_name' => 'Upscaling Image',
                        'user_id' => Auth::id(),
                        'status' => 'error',
                        'details' => json_encode(array_merge($logDetails, ['error_type' => 'Unexpected API Response', 'message' => 'API tidak mengembalikan gambar hasil yang diharapkan.'])),
                        'processing_time_ms' => $processingTimeMs,
                    ]);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'API Stability AI tidak mengembalikan gambar hasil yang diharapkan.',
                        'details' => $responseData,
                    ], 500);
                }
            } else {
                $errorMessage = 'Unknown API error';
                if (is_array($responseData) && isset($responseData['errors']) && is_array($responseData['errors']) && count($responseData['errors']) > 0 && isset($responseData['errors'][0]['message'])) {
                    $errorMessage = $responseData['errors'][0]['message'];
                } elseif (is_array($responseData) && isset($responseData['message'])) {
                    $errorMessage = $responseData['message'];
                }
                Log::error("Stability AI API Error ($statusCode): " . $errorMessage, ['response_body' => $responseData]);
                $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                // (Langkah 4) Catat error dari API
                ToolUsageLog::create([
                    'tool_name' => 'Upscaling Image',
                    'user_id' => Auth::id(),
                    'status' => 'error',
                    'details' => json_encode(array_merge($logDetails, ['error_type' => 'API Error', 'message' => $errorMessage, 'status_code' => $statusCode])),
                    'processing_time_ms' => $processingTimeMs,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kesalahan dari API Stability AI: ' . $errorMessage,
                ], $statusCode);
            }
        } catch (Exception $e) {
            Log::error("Stability AI Request Exception: " . $e->getMessage(), ['trace' => Str::limit($e->getTraceAsString(), 500)]);
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            // (Langkah 4) Catat error exception umum
            ToolUsageLog::create([
                'tool_name' => 'Upscaling Image',
                'user_id' => Auth::id(),
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Exception', 'message' => Str::limit($e->getMessage(), 255), 'file' => $e->getFile(), 'line' => $e->getLine()]),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses gambar: ' . Str::limit($e->getMessage(), 100),
            ], 500);
        }
    }
}

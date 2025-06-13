<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Untuk Str::slug
use App\Models\ToolUsageLog; // Import model ToolUsageLog
use Illuminate\Support\Facades\Auth; // Import Auth untuk mendapatkan user ID jika ada

class DocumentConversionController extends Controller
{
    private function getCloudConvertHeaders($apiKey)
    {
        return [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function convertToPdf(Request $request)
    {
        $startTime = microtime(true); // Catat waktu mulai proses

        $validator = Validator::make($request->all(), [
            'word_file' => 'required|file|mimes:doc,docx,rtf,odt|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            ToolUsageLog::create([
                'tool_name' => 'Word to PDF Converter',
                'user_id' => Auth::id(), // Akan null jika tidak ada user login
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Validation', 'message' => $validator->errors()->first()]),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $apiKey = env('CLOUDCONVERT_API_KEY');
        $apiBaseUrl = env('CLOUDCONVERT_API_BASE_URL', 'https://api.cloudconvert.com/v2');

        if (!$apiKey) {
            Log::error('Kunci API CloudConvert tidak dikonfigurasi di .env');
            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
            ToolUsageLog::create([
                'tool_name' => 'Word to PDF Converter',
                'user_id' => Auth::id(),
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Configuration', 'message' => 'Kunci API layanan konversi tidak dikonfigurasi.']),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json(['status' => 'error', 'message' => 'Kunci API layanan konversi tidak dikonfigurasi.'], 500);
        }

        $file = $request->file('word_file');
        $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $outputPdfName = Str::slug($originalFileName) . '.pdf';

        $jobIdForLog = null; // Inisialisasi jobId untuk logging

        try {
            // Step 1: Definisikan dan buat job di CloudConvert
            $jobDefinition = [
                'tasks' => [
                    'upload-my-word-file' => [
                        'operation' => 'import/upload',
                    ],
                    'convert-to-pdf' => [
                        'operation' => 'convert',
                        'input' => "upload-my-word-file",
                        'output_format' => 'pdf',
                        'engine' => 'libreoffice',
                        'filename' => $outputPdfName,
                    ],
                    'export-pdf-result' => [
                        'operation' => 'export/url',
                        'input' => "convert-to-pdf",
                        'inline' => false,
                    ]
                ]
            ];

            $jobResponse = Http::withoutVerifying() // Menonaktifkan verifikasi SSL
                ->withHeaders($this->getCloudConvertHeaders($apiKey))
                ->post($apiBaseUrl . '/jobs', $jobDefinition);

            if (!$jobResponse->successful() || !isset($jobResponse->json()['data']['id'])) {
                Log::error('CloudConvert Create Job Failed: ', $jobResponse->json() ?? ['body' => $jobResponse->body()]);
                $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                ToolUsageLog::create([
                    'tool_name' => 'Word to PDF Converter',
                    'user_id' => Auth::id(),
                    'status' => 'error',
                    'details' => json_encode(['error_type' => 'CloudConvert API', 'message' => 'Gagal membuat job konversi.', 'api_response' => $jobResponse->json() ?? ['body' => $jobResponse->body()]]),
                    'processing_time_ms' => $processingTimeMs,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Gagal membuat job konversi di CloudConvert.'], 500);
            }

            $jobData = $jobResponse->json()['data'];
            $jobId = $jobData['id'];
            $jobIdForLog = $jobId;

            $uploadTaskUrl = null;
            $uploadFormParams = [];
            foreach ($jobData['tasks'] as $task) {
                if ($task['name'] === 'upload-my-word-file' && isset($task['result']['form']['url'])) {
                    $uploadTaskUrl = $task['result']['form']['url'];
                    $uploadFormParams = $task['result']['form']['parameters'] ?? [];
                    break;
                }
            }

            if (!$uploadTaskUrl) {
                Log::error('CloudConvert: Upload task URL not found after job creation.', $jobData);
                $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                ToolUsageLog::create([
                    'tool_name' => 'Word to PDF Converter',
                    'user_id' => Auth::id(),
                    'status' => 'error',
                    'details' => json_encode(['error_type' => 'CloudConvert API', 'message' => 'Upload task URL not found.', 'job_id' => $jobId]),
                    'processing_time_ms' => $processingTimeMs,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Gagal mendapatkan URL untuk mengunggah file ke job.'], 500);
            }

            $multipartData = [];
            foreach ($uploadFormParams as $name => $value) {
                $multipartData[] = ['name' => $name, 'contents' => $value];
            }
            $multipartData[] = [
                'name'     => 'file',
                'contents' => fopen($file->getRealPath(), 'r'),
                'filename' => $file->getClientOriginalName()
            ];

            $fileUploadResponse = Http::withoutVerifying() // Menonaktifkan verifikasi SSL
                ->asMultipart()
                ->post($uploadTaskUrl, $multipartData);

            if (!$fileUploadResponse->successful()) {
                Log::error('CloudConvert File Upload to Job Failed: ', [
                    'status' => $fileUploadResponse->status(),
                    'body' => $fileUploadResponse->body()
                ]);
                $errorBodyText = is_string($fileUploadResponse->body()) ? $fileUploadResponse->body() : json_encode($fileUploadResponse->body());
                $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                ToolUsageLog::create([
                    'tool_name' => 'Word to PDF Converter',
                    'user_id' => Auth::id(),
                    'status' => 'error',
                    'details' => json_encode(['error_type' => 'CloudConvert API', 'message' => 'Gagal mengunggah file ke job.', 'job_id' => $jobId, 'status_code' => $fileUploadResponse->status(), 'api_response_body' => substr($errorBodyText, 0, 500)]),
                    'processing_time_ms' => $processingTimeMs,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Gagal mengunggah file ke layanan konversi. Status: ' . $fileUploadResponse->status() . ' Respons: ' . substr($errorBodyText, 0, 200)], 500);
            }

            $pdfUrl = null;
            $finalOutputPdfName = $outputPdfName;
            $attempts = 0;
            $maxAttempts = 25;
            $pollDelaySeconds = 4;

            while ($attempts < $maxAttempts) {
                sleep($pollDelaySeconds);
                $statusResponse = Http::withoutVerifying() // Menonaktifkan verifikasi SSL
                    ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->get("{$apiBaseUrl}/jobs/{$jobId}");

                if (!$statusResponse->successful()) {
                    Log::warning('CloudConvert Job Status Check Failed (attempt ' . ($attempts + 1) . '): ', $statusResponse->json() ?? ['body' => $statusResponse->body()]);
                } else {
                    $statusData = $statusResponse->json()['data'];
                    if ($statusData['status'] === 'finished') {
                        foreach ($statusData['tasks'] as $task) {
                            if ($task['name'] === 'export-pdf-result' && $task['status'] === 'finished' && isset($task['result']['files'][0]['url'])) {
                                $pdfUrl = $task['result']['files'][0]['url'];
                                foreach ($statusData['tasks'] as $innerTask) {
                                    if ($innerTask['name'] === 'convert-to-pdf' && isset($innerTask['result']['files'][0]['filename'])) {
                                        $finalOutputPdfName = $innerTask['result']['files'][0]['filename'];
                                        break;
                                    }
                                }
                                break 2;
                            }
                        }
                        if (!$pdfUrl) { // Jika job 'finished' tapi URL tidak ditemukan
                            Log::error('CloudConvert Job Finished but no export URL: ', $statusData);
                            $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                            ToolUsageLog::create([
                                'tool_name' => 'Word to PDF Converter',
                                'user_id' => Auth::id(),
                                'status' => 'error',
                                'details' => json_encode(['error_type' => 'CloudConvert Job Logic', 'message' => 'Job selesai tapi URL ekspor tidak ditemukan.', 'job_id' => $jobId, 'status_data' => $statusData]),
                                'processing_time_ms' => $processingTimeMs,
                            ]);
                            return response()->json(['status' => 'error', 'message' => 'Gagal mendapatkan URL hasil dari CloudConvert setelah job selesai.'], 500);
                        }
                    } elseif ($statusData['status'] === 'error') {
                        Log::error('CloudConvert Job Error: ', $statusData);
                        $errorMessage = 'Konversi gagal di CloudConvert.';
                        foreach ($statusData['tasks'] as $task) { // Coba ambil pesan error lebih detail
                            if ($task['status'] === 'error' && isset($task['message'])) {
                                $errorMessage .= ' Detail: ' . Str::limit($task['message'], 150);
                                break;
                            }
                        }
                        $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                        ToolUsageLog::create([
                            'tool_name' => 'Word to PDF Converter',
                            'user_id' => Auth::id(),
                            'status' => 'error',
                            'details' => json_encode(['error_type' => 'CloudConvert Job Error', 'message' => $errorMessage, 'job_id' => $jobId, 'job_status_data' => $statusData]),
                            'processing_time_ms' => $processingTimeMs,
                        ]);
                        return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
                    }
                }
                $attempts++;
            }

            if (!$pdfUrl) {
                $processingTimeMs = round((microtime(true) - $startTime) * 1000);
                ToolUsageLog::create([
                    'tool_name' => 'Word to PDF Converter',
                    'user_id' => Auth::id(),
                    'status' => 'error',
                    'details' => json_encode(['error_type' => 'CloudConvert Timeout/No URL', 'message' => 'Konversi memakan waktu terlalu lama atau gagal mendapatkan URL hasil.', 'job_id' => $jobId]),
                    'processing_time_ms' => $processingTimeMs,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Konversi memakan waktu terlalu lama atau gagal mendapatkan URL hasil dari CloudConvert.'], 500);
            }

            $pdfContent = Http::withoutVerifying()->get($pdfUrl)->body(); // Menonaktifkan verifikasi SSL

            $totalProcessingTimeMs = round((microtime(true) - $startTime) * 1000);
            ToolUsageLog::create([
                'tool_name' => 'Word to PDF Converter',
                'user_id' => Auth::id(),
                'status' => 'success',
                'details' => json_encode([
                    'original_filename' => $file->getClientOriginalName(),
                    'output_filename' => $finalOutputPdfName,
                    'cloudconvert_job_id' => $jobIdForLog
                ]),
                'processing_time_ms' => $totalProcessingTimeMs,
            ]);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $finalOutputPdfName . '"');
        } catch (\Exception $e) {
            Log::error('Word to PDF Conversion Exception: ' . $e->getMessage(), ['trace' => Str::limit($e->getTraceAsString(), 1000)]);
            // Pastikan $startTime sudah terdefinisi sebelum digunakan
            $processingTimeMs = isset($startTime) ? round((microtime(true) - $startTime) * 1000) : null;
            ToolUsageLog::create([
                'tool_name' => 'Word to PDF Converter',
                'user_id' => Auth::id(),
                'status' => 'error',
                'details' => json_encode(['error_type' => 'Exception', 'message' => Str::limit($e->getMessage(), 255), 'file' => $e->getFile(), 'line' => $e->getLine()]),
                'processing_time_ms' => $processingTimeMs,
            ]);
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan internal saat memproses: ' . Str::limit($e->getMessage(), 100)], 500);
        }
    }
}

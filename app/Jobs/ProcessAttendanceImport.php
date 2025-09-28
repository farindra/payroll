<?php

namespace App\Jobs;

use App\Services\AttendanceImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessAttendanceImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $userId;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct($filePath, $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    public function handle()
    {
        try {
            $fullPath = Storage::disk('local')->path($this->filePath);

            if (!file_exists($fullPath)) {
                throw new \Exception('File not found: ' . $this->filePath);
            }

            $results = AttendanceImportService::importFromCsv($fullPath);

            // Clean up the file
            Storage::disk('local')->delete($this->filePath);

            // Log results
            Log::info('Attendance import completed', [
                'user_id' => $this->userId,
                'file_path' => $this->filePath,
                'results' => $results
            ]);

            return $results;

        } catch (\Exception $e) {
            Log::error('Attendance import job failed', [
                'user_id' => $this->userId,
                'file_path' => $this->filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up the file even on failure
            if (Storage::disk('local')->exists($this->filePath)) {
                Storage::disk('local')->delete($this->filePath);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Attendance import job failed permanently', [
            'user_id' => $this->userId,
            'file_path' => $this->filePath,
            'error' => $exception->getMessage()
        ]);
    }
}
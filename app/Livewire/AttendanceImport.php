<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\AttendanceImportService;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class AttendanceImport extends Component
{
    use WithFileUploads;

    public $file = null;
    public $isProcessing = false;
    public $importResults = null;
    public $showResults = false;

    protected $rules = [
        'file' => 'required|file|mimes:csv,txt|max:5120',
    ];

    public function submit()
    {
        $this->validate();

        $this->isProcessing = true;
        $this->showResults = false;
        $this->importResults = null;

        try {
            // Ensure file is properly handled
            if (!$this->file) {
                throw new \Exception('No file uploaded.');
            }

            // Store the file
            $filePath = $this->file->store('attendance-imports', 'local');
            $fullPath = Storage::disk('local')->path($filePath);

            // Process the import immediately
            $importService = new AttendanceImportService();
            $this->importResults = $importService->importFromCsv($fullPath);

            // Clean up the file
            Storage::disk('local')->delete($filePath);

            $this->showResults = true;
            $this->reset('file');

            // Show appropriate notification
            if ($this->importResults['success_count'] > 0) {
                Notification::make()
                    ->title('Import Completed')
                    ->body("Successfully imported {$this->importResults['success_count']} of {$this->importResults['total_rows']} records.")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Import Completed with Errors')
                    ->body("Failed to import any records. Please check the error details.")
                    ->warning()
                    ->send();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->body('Failed to process import: ' . $e->getMessage())
                ->danger()
                ->send();

            $this->importResults = [
                'total_rows' => 0,
                'success_count' => 0,
                'error_count' => 1,
                'results' => [
                    [
                        'row' => 1,
                        'status' => 'error',
                        'message' => 'System error: ' . $e->getMessage()
                    ]
                ]
            ];
            $this->showResults = true;
        }

        $this->isProcessing = false;
    }

    public function downloadSample()
    {
        $samplePath = resource_path('samples/attendance_sample.csv');

        if (!file_exists($samplePath)) {
            Notification::make()
                ->title('Download Failed')
                ->body('Sample file not found.')
                ->danger()
                ->send();
            return;
        }

        return response()->download($samplePath, 'attendance_sample.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_sample.csv"',
        ]);
    }

    public function clearResults()
    {
        $this->showResults = false;
        $this->importResults = null;
    }

    public function render()
    {
        return view('livewire.attendance-import');
    }
}

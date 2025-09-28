<?php

namespace App\Filament\Pages;

use App\Jobs\ProcessAttendanceImport;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class ImportAttendance extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static ?string $navigationLabel = 'Impor Kehadiran';
    protected static ?string $navigationGroup = 'Operasional Payroll';
    protected static string $view = 'filament.pages.import-attendance';

    public $file = null;
    public $isProcessing = false;
    public $importResults = null;

    protected $rules = [
        'file' => 'required|file|mimes:csv,txt|max:5120',
    ];

    public function mount()
    {
        // $this->authorize('import', \App\Models\Attendance::class);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Impor Kehadiran')
                    ->description('Unggah file CSV yang berisi data kehadiran')
                    ->schema([
                        FileUpload::make('file')
                            ->label('File CSV Kehadiran')
                            ->required()
                            ->acceptedFileTypes(['.csv', 'text/csv'])
                            ->maxSize(5120) // 5MB max
                            ->helperText('Format CSV: employee_id, date, status, clock_in, clock_out, total_hours, overtime_hours, note')
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('file', $state)),
                    ]),
            ]);
    }

    public function submit()
    {
        $this->validate();

        $this->isProcessing = true;

        try {
            // Ensure file is properly handled
            if (!$this->file) {
                throw new \Exception('No file uploaded.');
            }

            // Store the file
            $filePath = $this->file->store('attendance-imports', 'local');

            // Dispatch the job
            ProcessAttendanceImport::dispatch($filePath, auth()->id());

            Notification::make()
                ->title('Impor Dimulai')
                ->body('Impor kehadiran telah diantrekan untuk diproses. Anda akan diberitahu saat selesai.')
                ->success()
                ->send();

            $this->reset('file');
            $this->isProcessing = false;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Impor Gagal')
                ->body('Gagal memulai impor: ' . $e->getMessage())
                ->danger()
                ->send();

            $this->isProcessing = false;
        }
    }

    public function downloadSample()
    {
        $samplePath = resource_path('samples/attendance_sample.csv');

        if (!file_exists($samplePath)) {
            Notification::make()
                ->title('Unduhan Gagal')
                ->body('File sampel tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }

        return response()->download($samplePath, 'attendance_sample.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_sample.csv"',
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Impor Kehadiran')
                ->action('submit')
                ->keyBindings(['mod+s'])
                ->disabled($this->isProcessing),
        ];
    }

    public function getHeading(): string
    {
        return 'Impor Data Kehadiran';
    }

    public function getSubheading(): string
    {
        return 'Unggah file CSV untuk mengimpor data kehadiran';
    }
}

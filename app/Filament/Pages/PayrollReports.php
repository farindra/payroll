<?php

namespace App\Filament\Pages;

use App\Models\PayrollPeriod;
use App\Models\Department;
use App\Services\ReportService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Penggajian';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.payroll-reports';

    public $selectedPeriod = null;
    public $selectedDepartment = null;
    public $startDate = null;
    public $endDate = null;
    public $reportType = 'payroll_summary';

    public $reportData = null;
    public $showResults = false;

    public function mount()
    {
        // $this->authorize('viewReports', \App\Models\PayrollDetail::class);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Report Parameters')
                    ->description('Select parameters for generating reports')
                    ->schema([
                        Select::make('reportType')
                            ->label('Jenis Laporan')
                            ->options([
                                'payroll_summary' => 'Ringkasan Penggajian',
                                'pph21' => 'Laporan PPh 21',
                                'bpjs' => 'Laporan BPJS',
                                'attendance' => 'Laporan Kehadiran',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn($state, $set) => $this->updateFormVisibility($set)),

                        Select::make('selectedPeriod')
                            ->label('Periode Penggajian')
                            ->options(PayrollPeriod::where('status', 'Calculated')->pluck('period_name', 'id'))
                            ->required(fn($get) => in_array($get('reportType'), ['payroll_summary', 'pph21', 'bpjs']))
                            ->hidden(fn($get) => !in_array($get('reportType'), ['payroll_summary', 'pph21', 'bpjs']))
                            ->searchable()
                            ->preload(),

                        DatePicker::make('startDate')
                            ->label('Tanggal Mulai')
                            ->required(fn($get) => $get('reportType') === 'attendance')
                            ->hidden(fn($get) => $get('reportType') !== 'attendance')
                            ->date(),

                        DatePicker::make('endDate')
                            ->label('Tanggal Selesai')
                            ->required(fn($get) => $get('reportType') === 'attendance')
                            ->hidden(fn($get) => $get('reportType') !== 'attendance')
                            ->date()
                            ->afterOrEqual('startDate'),

                        Select::make('selectedDepartment')
                            ->label('Departemen')
                            ->options(Department::pluck('name', 'id'))
                            ->hidden(fn($get) => $get('reportType') !== 'attendance')
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }

    protected function updateFormVisibility($set)
    {
        // Reset dependent fields when report type changes to avoid validation issues
        $set('selectedPeriod', null);
        $set('startDate', null);
        $set('endDate', null);
        $set('selectedDepartment', null);
        $this->showResults = false;
        $this->reportData = null;
    }

    public function generateReport()
    {
        \Log::info('GenerateReport called', [
            'reportType' => $this->reportType,
            'selectedPeriod' => $this->selectedPeriod,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);

        \Log::info('About to validate', [
            'validation_rules' => [
                'reportType' => 'required',
                'selectedPeriod' => 'required_if:reportType,payroll_summary,pph21,bpjs',
                'startDate' => 'required_if:reportType,attendance|date',
                'endDate' => 'required_if:reportType,attendance|date|after_or_equal:startDate',
            ]
        ]);

        try {
            $rules = [
                'reportType' => 'required',
            ];

            if (in_array($this->reportType, ['payroll_summary', 'pph21', 'bpjs'])) {
                $rules['selectedPeriod'] = 'required';
            }

            if ($this->reportType === 'attendance') {
                $rules['startDate'] = 'required|date';
                $rules['endDate'] = 'required|date|after_or_equal:startDate';
            }

            $this->validate($rules);
            \Log::info('Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        }

        try {
            $reportService = new ReportService();

            switch ($this->reportType) {
                case 'payroll_summary':
                    $this->reportData = $reportService->generatePayrollSummary($this->selectedPeriod);
                    break;
                case 'pph21':
                    $this->reportData = $reportService->generatePPh21Report($this->selectedPeriod);
                    break;
                case 'bpjs':
                    $this->reportData = $reportService->generateBPJSReport($this->selectedPeriod);
                    break;
                case 'attendance':
                    $this->reportData = $reportService->generateAttendanceReport(
                        $this->startDate,
                        $this->endDate,
                        $this->selectedDepartment
                    );
                    break;
                default:
                    throw new \Exception('Invalid report type');
            }

            \Log::info('Report generated successfully', [
                'showResults' => $this->showResults,
                'employee_count' => isset($this->reportData['employees']) ? count($this->reportData['employees']) : 0,
            ]);

            $this->showResults = true;

            Notification::make()
                ->title('Laporan Berhasil Dibuat')
                ->body('Laporan telah berhasil dibuat dengan ' . (isset($this->reportData['employees']) ? count($this->reportData['employees']) : 0) . ' karyawan.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            \Log::error('Report generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('Pembuatan Laporan Gagal')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function exportExcel()
    {
        if (!$this->reportData) {
            Notification::make()
                ->title('Tidak Ada Data untuk Diekspor')
                ->body('Silakan hasilkan laporan terlebih dahulu.')
                ->warning()
                ->send();
            return;
        }

        try {
            $reportService = new ReportService();
            $spreadsheet = $reportService->exportToExcel($this->reportData, $this->reportType);

            $filename = $this->getExportFilename();

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ekspor Gagal')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getExportFilename()
    {
        $date = now()->format('Y-m-d');

        switch ($this->reportType) {
            case 'payroll_summary':
                return "payroll_summary_{$date}.xlsx";
            case 'pph21':
                return "pph21_report_{$date}.xlsx";
            case 'bpjs':
                return "bpjs_report_{$date}.xlsx";
            case 'attendance':
                return "attendance_report_{$date}.xlsx";
            default:
                return "report_{$date}.xlsx";
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('generateReport')
                ->label('Hasilkan Laporan')
                ->action('generateReport')
                ->keyBindings(['mod+s']),
        ];
    }

    public function getHeading(): string
    {
        return 'Laporan Penggajian';
    }

    public function getSubheading(): string
    {
        return 'Hasilkan dan ekspor berbagai laporan penggajian';
    }
}

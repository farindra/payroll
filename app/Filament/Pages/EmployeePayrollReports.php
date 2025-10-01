<?php

namespace App\Filament\Pages;

use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Services\ReportService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class EmployeePayrollReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Penggajian Saya';
    protected static string $view = 'filament.pages.employee-payroll-reports';
    protected static ?string $title = 'Laporan Penggajian Saya';

    public $selectedPeriod = null;
    public $startDate = null;
    public $endDate = null;
    public $reportType = 'payroll_summary';

    public $reportData = null;
    public $showResults = false;

    public function mount()
    {
        // Ensure user is authenticated and has employee relationship
        if (!Auth::check() || !Auth::user()->employee) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini');
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Parameter Laporan')
                    ->description('Pilih parameter untuk menghasilkan laporan penggajian Anda')
                    ->schema([
                        Select::make('reportType')
                            ->label('Jenis Laporan')
                            ->options([
                                'payroll_summary' => 'Ringkasan Penggajian Saya',
                                'attendance' => 'Laporan Kehadiran Saya',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn($state, $set) => $this->updateFormVisibility($set)),

                        Select::make('selectedPeriod')
                            ->label('Periode Penggajian')
                            ->options(function () {
                                return PayrollPeriod::where('status', 'Calculated')
                                    ->whereHas('payrollDetails', function ($query) {
                                        $query->where('employee_id', Auth::user()->employee->id);
                                    })
                                    ->pluck('period_name', 'id');
                            })
                            ->required(fn($get) => $get('reportType') === 'payroll_summary')
                            ->hidden(fn($get) => $get('reportType') !== 'payroll_summary')
                            ->searchable()
                            ->preload(),

                        DatePicker::make('startDate')
                            ->label('Tanggal Mulai')
                            ->required(fn($get) => $get('reportType') === 'attendance')
                            ->hidden(fn($get) => $get('reportType') !== 'attendance')
                            ->date()
                            ->default(now()->startOfMonth()),

                        DatePicker::make('endDate')
                            ->label('Tanggal Selesai')
                            ->required(fn($get) => $get('reportType') === 'attendance')
                            ->hidden(fn($get) => $get('reportType') !== 'attendance')
                            ->date()
                            ->default(now()->endOfMonth())
                            ->afterOrEqual('startDate'),
                    ]),
            ]);
    }

    protected function updateFormVisibility($set)
    {
        $set('selectedPeriod', null);
        $set('startDate', null);
        $set('endDate', null);
        $this->showResults = false;
        $this->reportData = null;
    }

    public function generateReport()
    {
        try {
            // Debug: Log the report generation
            \Log::info('generateReport called', [
                'reportType' => $this->reportType,
                'selectedPeriod' => $this->selectedPeriod,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate
            ]);

            $rules = [
                'reportType' => 'required',
            ];

            if ($this->reportType === 'payroll_summary') {
                $rules['selectedPeriod'] = 'required';
            }

            if ($this->reportType === 'attendance') {
                $rules['startDate'] = 'required|date';
                $rules['endDate'] = 'required|date|after_or_equal:startDate';
            }

            $this->validate($rules);

            $reportService = new ReportService();
            $employeeId = Auth::user()->employee->id;

            switch ($this->reportType) {
                case 'payroll_summary':
                    $this->reportData = $reportService->generateEmployeePayrollSummary($this->selectedPeriod, $employeeId);
                    break;
                case 'attendance':
                    $this->reportData = $reportService->generateEmployeeAttendanceReport(
                        $this->startDate,
                        $this->endDate,
                        $employeeId
                    );
                    break;
                default:
                    throw new \Exception('Invalid report type');
            }

            $this->showResults = true;

            // Debug: Log successful report generation
            \Log::info('Report generated successfully', [
                'reportType' => $this->reportType,
                'showResults' => $this->showResults,
                'hasReportData' => !empty($this->reportData)
            ]);

            // Use session flash to avoid JSON UTF-8 issues
            session()->flash('success', 'Laporan penggajian Anda telah berhasil dibuat.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in generateReport: ' . json_encode($e->errors()));
            session()->flash('error', 'Silakan periksa input Anda.');
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error in generateReport: ' . $e->getMessage());
            session()->flash('error', 'Gagal membuat laporan. Silakan coba lagi.');
        }
    }

    public function exportExcel()
    {
        if (!$this->reportData) {
            return redirect()->back()->with('error', 'Silakan hasilkan laporan terlebih dahulu.');
        }

        try {
            $reportService = new ReportService();
            $spreadsheet = $reportService->exportEmployeeReportToExcel($this->reportData, $this->reportType);

            $filename = $this->getExportFilename();

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengekspor laporan. Silakan coba lagi.');
        }
    }

    public function downloadPayslip()
    {
        try {
            if (!$this->reportData || !isset($this->selectedPeriod)) {
                throw new \Exception('Silakan pilih periode dan hasilkan laporan terlebih dahulu.');
            }

            $payslipService = new \App\Services\PayslipService();

            // Find payroll detail for this employee and period
            $payrollDetail = \App\Models\PayrollDetail::where('employee_id', Auth::user()->employee->id)
                ->where('payroll_period_id', $this->selectedPeriod)
                ->first();

            if (!$payrollDetail) {
                throw new \Exception('Tidak ada data penggajian untuk periode yang dipilih.');
            }

            // Return direct PDF download without any JSON response
            return $payslipService->downloadPayslip($payrollDetail->id);
        } catch (\Exception $e) {
            // Create a simple text response instead of JSON
            return response('Error: ' . $e->getMessage(), 500, [
                'Content-Type' => 'text/plain'
            ]);
        }
    }

    
    
    protected function getExportFilename()
    {
        $date = now()->format('Y-m-d');
        $employeeName = Auth::user()->employee->full_name ?? 'employee';

        switch ($this->reportType) {
            case 'payroll_summary':
                return "payroll_summary_{$employeeName}_{$date}.xlsx";
            case 'attendance':
                return "attendance_report_{$employeeName}_{$date}.xlsx";
            default:
                return "employee_report_{$employeeName}_{$date}.xlsx";
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

    /**
     * Get the direct download URL for the current selected period
     */
    public function getDirectDownloadUrl()
    {
        if (!$this->selectedPeriod) {
            return null;
        }

        return route('payslip.download.direct', ['periodId' => $this->selectedPeriod]);
    }

    /**
     * Get the print URL for the current selected period
     */
    public function getPrintUrl()
    {
        if (!$this->selectedPeriod) {
            return null;
        }

        try {
            // Use secure share URL for print button (same as QR code)
            $employeeId = Auth::user()->employee->id;
            $payslipSecurityService = new \App\Services\PayslipSecurityService();
            $secureUrl = $payslipSecurityService->generateSecureQrUrl($employeeId, $this->selectedPeriod);

            // Debug logging
            \Log::info('Generated secure share URL for print button', [
                'employee_id' => $employeeId,
                'period_id' => $this->selectedPeriod,
                'secure_url' => $secureUrl
            ]);

            return $secureUrl;
        } catch (\Exception $e) {
            \Log::error('Failed to generate secure print URL for print button: ' . $e->getMessage());
            // Fallback to old URL format if secure generation fails
            return route('payslip.print', ['periodId' => $this->selectedPeriod]);
        }
    }

    public function getHeading(): string
    {
        return 'Laporan Penggajian Saya';
    }

    public function getSubheading(): string
    {
        return 'Lihat dan ekspor laporan penggajian pribadi Anda';
    }

    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->employee !== null;
    }
}
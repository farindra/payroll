<?php

namespace App\Filament\Pages;

use App\Models\PayrollPeriod;
use App\Services\PayrollCalculationService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;

class ProcessPayroll extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Proses Payroll';
    protected static ?string $navigationGroup = 'Operasional Payroll';
    protected static string $view = 'filament.pages.process-payroll';

    public $selectedPeriod = null;
    public $isProcessing = false;
    public $processResults = null;

    public function mount()
    {
        // $this->authorize('process', PayrollPeriod::class);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Proses Payroll')
                    ->description('Pilih periode payroll untuk diproses')
                    ->schema([
                        Select::make('selectedPeriod')
                            ->label('Periode Payroll')
                            ->options(PayrollPeriod::where('status', 'Draft')->pluck('period_name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('Hanya periode draft yang dapat diproses'),
                    ]),
            ]);
    }

    public function processPayroll()
    {
        $this->validate([
            'selectedPeriod' => 'required|exists:payroll_periods,id',
        ]);

        $this->isProcessing = true;

        try {
            $payrollPeriod = PayrollPeriod::find($this->selectedPeriod);

            if (!$payrollPeriod->canBeProcessed()) {
                throw new \Exception('Periode payroll ini tidak dapat diproses. Status harus Draft.');
            }

            $payrollService = new PayrollCalculationService();
            $results = $payrollService->processPayroll($payrollPeriod);

            if ($results['success']) {
                Notification::make()
                    ->title('Payroll Berhasil Diproses')
                    ->body($results['message'])
                    ->success()
                    ->send();

                $this->processResults = $results;
            } else {
                throw new \Exception($results['message']);
            }
        } catch (\Exception $e) {
            Log::error('Payroll processing failed', [
                'payroll_period_id' => $this->selectedPeriod,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            Notification::make()
                ->title('Proses Payroll Gagal')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();

            $this->processResults = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        $this->isProcessing = false;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('processPayroll')
                ->label('Proses Payroll')
                ->action('processPayroll')
                ->disabled($this->isProcessing || !$this->selectedPeriod),
        ];
    }

    public function getHeading(): string
    {
        return 'Proses Payroll';
    }

    public function getSubheading(): string
    {
        return 'Hitung dan hasilkan payroll untuk periode yang dipilih';
    }
}

<x-filament-panels::page>
    <x-filament-panels::form wire:submit="generateReport">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>

    @if ($showResults && $reportData)
        <div class="mt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Hasil Laporan</h3>
                <x-filament::button
                    wire:click="exportExcel"
                    icon="heroicon-m-arrow-down-tray"
                >
                    Ekspor Excel
                </x-filament::button>
            </div>

            @switch($reportType)
                @case('payroll_summary')
                    @include('filament.pages.reports.payroll-summary')
                    @break

                @case('pph21')
                    @include('filament.pages.reports.pph21')
                    @break

                @case('bpjs')
                    @include('filament.pages.reports.bpjs')
                    @break

                @case('attendance')
                    @include('filament.pages.reports.attendance')
                    @break
            @endswitch
        </div>
    @endif

    <div class="mt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Laporan Tersedia</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-gray-900">Ringkasan Penggajian</h4>
                        <p class="text-sm text-gray-500">Ringkasan penggajian komprehensif dengan detail karyawan</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-gray-900">Laporan PPh 21</h4>
                        <p class="text-sm text-gray-500">Laporan kepatuhan pajak untuk pelaporan e-SPT</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-gray-900">Laporan BPJS</h4>
                        <p class="text-sm text-gray-500">Rincian kontribusi asuransi</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-gray-900">Laporan Kehadiran</h4>
                        <p class="text-sm text-gray-500">Ringkasan kehadiran dan lembur karyawan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
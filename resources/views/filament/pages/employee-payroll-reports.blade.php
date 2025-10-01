<x-filament-panels::page>
            <x-filament-panels::form wire:submit="generateReport">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>

    @push('scripts')
        <script>
            function copyToClipboard(url) {
                try {
                    navigator.clipboard.writeText(url).then(() => {
                        alert('Link tersalin! Silakan paste di browser untuk membuka slip gaji.');
                    }).catch(() => {
                        // Fallback for older browsers
                        const textArea = document.createElement('textarea');
                        textArea.value = url;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
                        alert('Link tersalin! Silakan paste di browser untuk membuka slip gaji.');
                    });
                } catch (error) {
                    console.error('Error copying to clipboard:', error);
                    alert('Gagal menyalin link. Silakan copy manual dari link di bawah.');
                }
            }

            // Listen for Livewire events
            document.addEventListener('livewire:init', () => {
                Livewire.on('open-print-window', (event) => {
                    console.log('Opening print window:', event.url);
                    if (event.url) {
                        window.open(event.url, '_blank');
                    } else {
                        console.error('No URL provided in event');
                        alert('Error: No URL provided');
                    }
                });

                Livewire.on('show-notification', (event) => {
                    console.log('Notification:', event.message);
                    // You can replace this with a nicer notification system
                    alert(event.message);
                });
            });
        </script>
    @endpush

    @if ($this->showResults && $this->reportData)
        <div class="mt-6 space-y-6">
            <!-- Summary Information -->
            @if ($this->reportType === 'payroll_summary')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Ringkasan Penggajian</h3>

                    @if (isset($this->reportData['period']))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Periode</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $this->reportData['period']['period_name'] }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $this->reportData['period']['status'] }}</p>
                            </div>
                        </div>
                    @endif

                    @if (isset($this->reportData['employee']))
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h4 class="font-medium mb-2 text-gray-900 dark:text-gray-100">Informasi Karyawan</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600 dark:text-gray-300">Nama:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $this->reportData['employee']['full_name'] }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-300">NIK:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $this->reportData['employee']['nip'] }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-300">Departemen:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $this->reportData['employee']['department'] ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-300">Jabatan:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $this->reportData['employee']['position'] ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (isset($this->reportData['payroll_detail']))
                        <div class="mt-4">
                            <h4 class="font-medium mb-3 text-gray-900 dark:text-gray-100">Rincian Penggajian</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pendapatan</h5>
                                    <div class="space-y-1 text-sm">
                                        @foreach ($this->reportData['payroll_detail']['earnings'] ?? [] as $earning)
                                            <div class="flex justify-between">
                                                <span class="text-gray-700 dark:text-gray-300">{{ $earning['name'] }}</span>
                                                <span class="font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($earning['amount'], 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Potongan</h5>
                                    <div class="space-y-1 text-sm">
                                        @foreach ($this->reportData['payroll_detail']['deductions'] ?? [] as $deduction)
                                            <div class="flex justify-between">
                                                <span class="text-gray-700 dark:text-gray-300">{{ $deduction['name'] }}</span>
                                                <span class="font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($deduction['amount'], 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t dark:border-gray-600">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">Take Home Pay</span>
                                    <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                        Rp {{ number_format($this->reportData['payroll_detail']['take_home_pay'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if ($this->reportType === 'attendance')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                            <svg class="w-6 h-6 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Laporan Kehadiran
                        </h3>
                    </div>

                    @if (isset($this->reportData['period']))
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6 border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Periode Laporan</p>
                                    <p class="text-lg font-semibold text-blue-900 dark:text-blue-100">{{ $this->reportData['period']['start_date'] }} - {{ $this->reportData['period']['end_date'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (isset($this->reportData['summary']))
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                            <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-xl p-6 border border-green-200 dark:border-green-700 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-green-600 dark:text-green-300">Hadir</p>
                                        <p class="text-3xl font-bold text-green-800 dark:text-green-100">{{ $this->reportData['summary']['present'] ?? 0 }}</p>
                                    </div>
                                    <div class="bg-green-500/20 p-3 rounded-lg">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/30 dark:to-yellow-800/30 rounded-xl p-6 border border-yellow-200 dark:border-yellow-700 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-yellow-600 dark:text-yellow-300">Terlambat</p>
                                        <p class="text-3xl font-bold text-yellow-800 dark:text-yellow-100">{{ $this->reportData['summary']['late'] ?? 0 }}</p>
                                    </div>
                                    <div class="bg-yellow-500/20 p-3 rounded-lg">
                                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 rounded-xl p-6 border border-red-200 dark:border-red-700 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-red-600 dark:text-red-300">Tidak Hadir</p>
                                        <p class="text-3xl font-bold text-red-800 dark:text-red-100">{{ $this->reportData['summary']['absent'] ?? 0 }}</p>
                                    </div>
                                    <div class="bg-red-500/20 p-3 rounded-lg">
                                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-xl p-6 border border-blue-200 dark:border-blue-700 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-blue-600 dark:text-blue-300">Cuti/Sakit</p>
                                        <p class="text-3xl font-bold text-blue-800 dark:text-blue-100">{{ $this->reportData['summary']['leave'] ?? 0 }}</p>
                                    </div>
                                    <div class="bg-blue-500/20 p-3 rounded-lg">
                                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (isset($this->reportData['attendances']))
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6">
                            <h4 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Detail Kehadiran
                            </h4>
                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-600">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-100 dark:bg-gray-600">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Tanggal</th>
                                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Check In</th>
                                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Check Out</th>
                                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($this->reportData['attendances'] as $attendance)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($attendance['date'])->format('d/m/Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    @if ($attendance['check_in'])
                                                        {{ \Carbon\Carbon::parse($attendance['check_in'])->format('H:i') }}
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    @if ($attendance['check_out'])
                                                        {{ \Carbon\Carbon::parse($attendance['check_out'])->format('H:i') }}
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full
                                                        @if ($attendance['status'] === 'present') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                                        @elseif ($attendance['status'] === 'late') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                                        @elseif ($attendance['status'] === 'absent') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                                                        @elseif ($attendance['status'] === 'leave') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                                        @elseif ($attendance['status'] === 'sick') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300
                                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                                        @endif">
                                                        {{ ucfirst($attendance['status']) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $attendance['notes'] ?? '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

                    </div>
    @endif

    <!-- Export Buttons -->
    <div class="flex justify-end space-x-3 mt-4">
        @if ($this->reportType === 'payroll_summary' && $this->selectedPeriod)
            <a href="{{ $this->getPrintUrl() }}"
               target="_blank"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 rounded-md transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Buka Slip Gaji
            </a>
        @endif

        @if ($this->reportType === 'attendance' && $this->showResults && $this->reportData)
            <button wire:click="exportExcel"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition-colors border-2 border-green-700"
                style="background-color: #16a34a !important; color: white !important; border-color: #15803d !important;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </button>

            <form action="{{ route('attendance.print.secure', ['token' => 'temp']) }}" method="POST" target="_blank">
                @csrf
                <input type="hidden" name="report_data" value="{{ base64_encode(json_encode($this->reportData)) }}">
                <input type="hidden" name="employee_name" value="{{ Auth::user()->employee->full_name ?? 'Employee' }}">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition-colors border-2 border-red-700 text-white hover:text-white"
                        style="background-color: #dc2626 !important; color: white !important; border-color: #b91c1c !important; text-decoration: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print
                </button>
            </form>
        @endif
    </div>
</x-filament-panels::page>

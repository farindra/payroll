<div class="bg-white border border-gray-200 rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-medium text-gray-900">Laporan PPh 21</h4>
        <p class="text-sm text-gray-600">Periode: {{ $reportData['period']->period_name }}</p>
    </div>

    <div class="p-6">
        <!-- PPh 21 Summary Section -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h5 class="text-md font-medium text-gray-900 mb-4">Ringkasan PPh 21</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-700">
                        {{ number_format($reportData['summary']['total_employees'], 0) }}
                    </div>
                    <div class="text-sm text-blue-600">Total Karyawan</div>
                </div>

                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-700">
                        {{ 'Rp ' . number_format($reportData['summary']['total_gross_salary'], 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-green-600">Total Penghasilan Bruto</div>
                </div>

                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-700">
                        {{ 'Rp ' . number_format($reportData['summary']['total_ptkp'], 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-yellow-600">Total PTKP</div>
                </div>

                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-red-700">
                        {{ 'Rp ' . number_format($reportData['summary']['total_pph21'], 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-red-600">Total PPh 21</div>
                </div>
            </div>
        </div>

        <!-- PPh 21 Details Section -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h5 class="text-md font-medium text-gray-900 mb-4">Detail Perhitungan PPh 21</h5>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NPWP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status PTKP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penghasilan Bruto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PTKP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PKP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PPh 21 Terutang</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['employees'] as $employee)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $employee->employee->nip }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee->employee->full_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee->employee->npwp ?: '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee->employee->ptkp_status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($employee->gross_salary, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($employee->ptkp_amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format(max(0, $employee->gross_salary - $employee->ptkp_amount), 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">{{ 'Rp ' . number_format($employee->pph_21, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PPh 21 by Status PTKP -->
        @if(isset($reportData['ptkp_summary']) && $reportData['ptkp_summary']->count() > 0)
            <div class="bg-white border border-gray-200 rounded-lg p-6 mt-6">
                <h5 class="text-md font-medium text-gray-900 mb-4">Ringkasan PPh 21 per Status PTKP</h5>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status PTKP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Karyawan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total PPh 21</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata PPh 21</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reportData['ptkp_summary'] as $ptkp_status => $summary)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ptkp_status }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $summary['employee_count'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($summary['total_pph21'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($summary['average_pph21'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
<div class="bg-white border border-gray-200 rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-medium text-gray-900">Ringkasan Laporan Penggajian</h4>
        <p class="text-sm text-gray-600">Periode: {{ $reportData['period']->period_name }}</p>
    </div>

    <div class="p-6">
        <!-- Payroll Summary Section -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h5 class="text-md font-medium text-gray-900 mb-4">Ringkasan Penggajian</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-700">
                        {{ number_format($reportData['summary']['total_employees'] ?? 0, 0) }}
                    </div>
                    <div class="text-sm text-blue-600">Total Karyawan</div>
                </div>

                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-700">
                        {{ 'Rp ' . number_format($reportData['summary']['total_gross_salary'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-green-600">Total Gaji Kotor</div>
                </div>

                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-purple-700">
                        {{ 'Rp ' . number_format($reportData['summary']['total_net_salary'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-purple-600">Total Gaji Bersih</div>
                </div>

                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-red-700">
                        {{ 'Rp ' . number_format($reportData['summary']['total_pph21'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-red-600">Total PPh 21</div>
                </div>
            </div>
        </div>

        <!-- Department Summary Section -->
        @if(isset($reportData['department_summary']) && $reportData['department_summary'] && $reportData['department_summary']->count() > 0)
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h5 class="text-md font-medium text-gray-900 mb-4">Ringkasan Departemen</h5>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departemen</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji Kotor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji Bersih</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PPh 21</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reportData['department_summary'] as $department => $summary)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $department }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $summary['employee_count'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($summary['total_gross_salary'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($summary['total_net_salary'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($summary['total_pph21'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Employee Details Section -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h5 class="text-md font-medium text-gray-900 mb-4">Detail Karyawan</h5>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departemen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji Pokok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tunjangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji Kotor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Potongan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PPh 21</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji Bersih</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['employees'] as $employee)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $employee->employee->nip }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee->employee->full_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee->employee->department->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($employee->basic_salary, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($employee->total_allowances, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($employee->gross_salary, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($employee->total_deductions, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format($employee->pph_21, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">{{ 'Rp ' . number_format($employee->net_salary, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
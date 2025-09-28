<div class="bg-white border border-gray-200 rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-medium text-gray-900">Laporan BPJS</h4>
        <p class="text-sm text-gray-600">Periode: {{ $reportData['period']->period_name }}</p>
    </div>

    <div class="p-6">
        <!-- BPJS Summary Section -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h5 class="text-md font-medium text-gray-900 mb-4">Ringkasan BPJS</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-700">
                        {{ number_format($reportData['summary']['total_employees'], 0) }}
                    </div>
                    <div class="text-sm text-blue-600">Total Karyawan</div>
                </div>

                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-700">
                        {{ 'Rp ' . number_format($reportData['summary']['total_bpjs_kesehatan'], 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-green-600">Total BPJS Kesehatan</div>
                </div>

                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-purple-700">
                        {{ 'Rp ' . number_format($reportData['summary']['total_bpjs_tk'], 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-purple-600">Total BPJS TK</div>
                </div>

                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-red-700">
                        {{ 'Rp ' . number_format($reportData['summary']['total_company_bpjs'], 0, ',', '.') }}
                    </div>
                    <div class="text-sm text-red-600">Total Iuran Perusahaan</div>
                </div>
            </div>
        </div>

        <!-- BPJS Details Section -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h5 class="text-md font-medium text-gray-900 mb-4">Detail BPJS</h5>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. BPJS Kesehatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. BPJS TK</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Iuran Kesehatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Iuran TK</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Iuran</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['employees'] as $employee)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $employee->employee->nip }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee->employee->full_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee->employee->bpjs_kesehatan_no ?: '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee->employee->bpjs_tk_no ?: '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format(($employee->bpjs_kesehatan_emp ?? 0) + ($employee->bpjs_kesehatan_comp ?? 0), 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'Rp ' . number_format(($employee->bpjs_tk_emp ?? 0) + ($employee->bpjs_tk_comp ?? 0), 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">{{ 'Rp ' . number_format(($employee->bpjs_kesehatan_emp ?? 0) + ($employee->bpjs_kesehatan_comp ?? 0) + ($employee->bpjs_tk_emp ?? 0) + ($employee->bpjs_tk_comp ?? 0), 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BPJS Company Contribution Summary -->
        @if(isset($reportData['company_summary']))
            <div class="bg-white border border-gray-200 rounded-lg p-6 mt-6">
                <h5 class="text-md font-medium text-gray-900 mb-4">Ringkasan Iuran Perusahaan</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-xl font-bold text-blue-700">
                            {{ 'Rp ' . number_format($reportData['company_summary']['bpjs_kesehatan_company'], 0, ',', '.') }}
                        </div>
                        <div class="text-sm text-blue-600">BPJS Kesehatan Perusahaan</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-xl font-bold text-purple-700">
                            {{ 'Rp ' . number_format($reportData['company_summary']['bpjs_tk_company'], 0, ',', '.') }}
                        </div>
                        <div class="text-sm text-purple-600">BPJS TK Perusahaan</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
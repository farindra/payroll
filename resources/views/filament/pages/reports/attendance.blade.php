<div class="bg-white border border-gray-200 rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-medium text-gray-900">Laporan Kehadiran</h4>
        <p class="text-sm text-gray-600">Periode: {{ $reportData['period']['start_date']->format('d/m/Y') }} - {{ $reportData['period']['end_date']->format('d/m/Y') }}</p>
        @if(isset($reportData['department']) && $reportData['department'])
            <p class="text-sm text-gray-600">Departemen: {{ $reportData['department']->name }}</p>
        @endif
    </div>

    <div class="p-6">
        <!-- Attendance Summary Section -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h5 class="text-md font-medium text-gray-900 mb-4">Ringkasan Kehadiran</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-700">
                        {{ number_format($reportData['summary']['total_employees'] ?? 0, 0) }}
                    </div>
                    <div class="text-sm text-blue-600">Total Karyawan</div>
                </div>

                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-700">
                        {{ number_format($reportData['summary']['total_present'] ?? 0, 0) }}
                    </div>
                    <div class="text-sm text-green-600">Total Hadir</div>
                </div>

                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-700">
                        {{ number_format($reportData['summary']['total_overtime_hours'] ?? 0, 1) }}
                    </div>
                    <div class="text-sm text-yellow-600">Total Jam Lembur</div>
                </div>

                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-red-700">
                        {{ number_format($reportData['summary']['total_absent'] ?? 0, 0) }}
                    </div>
                    <div class="text-sm text-red-600">Total Tidak Hadir</div>
                </div>
            </div>
        </div>

        <!-- Attendance Details Section -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h5 class="text-md font-medium text-gray-900 mb-4">Detail Kehadiran</h5>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departemen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hari</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hadir</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sakit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Izin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lembur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kehadiran (%)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['employees'] as $employee)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $employee['employee']->nip }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee['employee']->full_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee['employee']->department->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee['total_days'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee['present_days'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee['sick_days'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee['leave_days'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($employee['overtime_hours'], 1) }} jam</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $employee['attendance_rate'] >= 90 ? 'text-green-600' : ($employee['attendance_rate'] >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ number_format($employee['attendance_rate'], 1) }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Attendance by Department -->
        @if(isset($reportData['department_summary']) && $reportData['department_summary'] && count($reportData['department_summary']) > 0)
            <div class="bg-white border border-gray-200 rounded-lg p-6 mt-6">
                <h5 class="text-md font-medium text-gray-900 mb-4">Ringkasan Kehadiran per Departemen</h5>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departemen</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Karyawan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hadir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Lembur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reportData['department_summary'] as $department => $summary)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $department }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $summary['employee_count'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $summary['total_present'] ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($summary['total_overtime'] ?? 0, 1) }} jam</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ ($summary['avg_attendance_rate'] ?? 0) >= 90 ? 'text-green-600' : (($summary['avg_attendance_rate'] ?? 0) >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ number_format($summary['avg_attendance_rate'] ?? 0, 1) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
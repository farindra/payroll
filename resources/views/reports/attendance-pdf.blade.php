<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kehadiran - {{ $employeeName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        .period {
            font-size: 16px;
            font-weight: bold;
            color: #0066cc;
            margin: 10px 0;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
        }
        .summary-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-label {
            font-size: 12px;
            color: #666;
        }
        .present { color: #28a745; border-left: 4px solid #28a745; }
        .late { color: #ffc107; border-left: 4px solid #ffc107; }
        .absent { color: #dc3545; border-left: 4px solid #dc3545; }
        .leave { color: #007bff; border-left: 4px solid #007bff; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            font-size: 10px;
        }
        .status-present { background-color: #d4edda; color: #155724; font-weight: bold; }
        .status-late { background-color: #fff3cd; color: #856404; font-weight: bold; }
        .status-absent { background-color: #f8d7da; color: #721c24; font-weight: bold; }
        .status-leave { background-color: #d1ecf1; color: #0c5460; font-weight: bold; }
        .status-sick { background-color: #e2e3e5; color: #383d41; font-weight: bold; }
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN KEHADIRAN KARYAWAN</div>
        <div class="subtitle">PT. PAYROLL SYSTEM</div>
        <div class="period">Periode: {{ $reportData['period']['start_date'] }} - {{ $reportData['period']['end_date'] }}</div>
    </div>

    @if (isset($reportData['summary']))
        <div class="summary-grid">
            <div class="summary-card present">
                <div class="summary-number">{{ $reportData['summary']['present'] ?? 0 }}</div>
                <div class="summary-label">Hadir</div>
            </div>
            <div class="summary-card late">
                <div class="summary-number">{{ $reportData['summary']['late'] ?? 0 }}</div>
                <div class="summary-label">Terlambat</div>
            </div>
            <div class="summary-card absent">
                <div class="summary-number">{{ $reportData['summary']['absent'] ?? 0 }}</div>
                <div class="summary-label">Tidak Hadir</div>
            </div>
            <div class="summary-card leave">
                <div class="summary-number">{{ $reportData['summary']['leave'] ?? 0 }}</div>
                <div class="summary-label">Cuti/Sakit</div>
            </div>
        </div>
    @endif

    @if (isset($reportData['attendances']) && count($reportData['attendances']) > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reportData['attendances'] as $index => $attendance)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance['date'])->format('d/m/Y') }}</td>
                        <td>{{ $attendance['check_in'] ? \Carbon\Carbon::parse($attendance['check_in'])->format('H:i') : '-' }}</td>
                        <td>{{ $attendance['check_out'] ? \Carbon\Carbon::parse($attendance['check_out'])->format('H:i') : '-' }}</td>
                        <td>
                            <span class="status-{{ $attendance['status'] }}">
                                {{ ucfirst($attendance['status']) }}
                            </span>
                        </td>
                        <td>{{ $attendance['notes'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #666; font-style: italic;">Tidak ada data kehadiran untuk periode ini.</p>
    @endif

    <div class="footer">
        <p>Dibuat pada: {{ $generationDate->format('d/m/Y H:i') }}</p>
        <p>Generated by Payroll System</p>
    </div>
</body>
</html>
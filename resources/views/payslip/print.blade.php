<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $payrollDetail->employee->full_name ?? 'Employee' }}</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f5f5f5;
            color: #000000;
        }
        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 20px;
            border-bottom: 2px solid #333;
            background: #f9f9f9;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            color: #000;
        }
        .period {
            font-size: 12px;
            margin: 5px 0;
            color: #666;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
            color: #000;
        }
        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            font-size: 12px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .salary-breakdown {
            font-size: 12px;
        }
        .salary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .salary-row.total {
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
            background: #f9f9f9;
        }
        .deductions {
            font-size: 12px;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 30px;
            border-top: 1px solid #ccc;
            margin-top: 20px;
            background: #f9f9f9;
        }
        .signature {
            text-align: right;
            font-size: 11px;
        }
        .signature-line {
            border-top: 2px solid #333;
            width: 150px;
            margin: 10px 0;
        }
        .qr-code {
            text-align: center;
            font-size: 10px;
            background: white;
            padding: 15px;
            border: 2px solid #333;
            border-radius: 5px;
        }
        .generation-info {
            font-size: 10px;
            text-align: center;
            margin-top: 10px;
            color: #666;
        }
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 20px 0;
        }
        .take-home {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            padding: 15px;
            border: 3px solid #333;
            background: #f0f0f0;
            color: #000;
        }
        .print-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px;
            display: inline-block;
            text-decoration: none;
        }
        .print-button:hover {
            background: #0056b3;
        }
        .qr-visual {
            width: 100px;
            height: 100px;
            background: white;
            border: 3px solid #000;
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            grid-template-rows: repeat(8, 1fr);
            gap: 1px;
            margin: 10px auto;
            padding: 5px;
        }
        .qr-cell {
            background: #000;
        }
        .qr-cell.white {
            background: #fff;
            border: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin: 20px 0;">
        <button onclick="window.print()" class="print-button">🖨️ Cetak Slip Gaji</button>
        <button onclick="window.close()" class="print-button" style="background: #6c757d; margin-left: 10px;">✖️ Tutup</button>
    </div>

    <div class="container">
        <div class="header">
            <div class="title">SLIP GAJI</div>
            <div class="period">Periode: {{ $payrollDetail->payrollPeriod->period_name ?? '-' }}</div>
        </div>

        <div class="content">
            <div class="section">
                <div class="section-title">Informasi Karyawan</div>
                <div class="employee-info">
                    <div>
                        <div class="info-row">
                            <span class="info-label">Nama:</span>
                            <span>{{ $payrollDetail->employee->full_name ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">ID Karyawan:</span>
                            <span>{{ $payrollDetail->employee->employee_id ?? $payrollDetail->employee->nip ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Departemen:</span>
                            <span>{{ $payrollDetail->employee->department->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="info-row">
                            <span class="info-label">Jabatan:</span>
                            <span>{{ $payrollDetail->employee->position ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status:</span>
                            <span>{{ $payrollDetail->employee->employment_status ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Bank:</span>
                            <span>{{ $payrollDetail->employee->bank_name ?? '-' }} - {{ $payrollDetail->employee->bank_account ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="two-column">
                <div class="section">
                    <div class="section-title">Pendapatan</div>
                    <div class="salary-breakdown">
                        @php
                            $earnings = [];
                            if ($payrollDetail->basic_salary > 0) {
                                $earnings[] = ['name' => 'Gaji Pokok', 'amount' => $payrollDetail->basic_salary];
                            }
                            if ($payrollDetail->total_allowances > 0) {
                                $earnings[] = ['name' => 'Tunjangan', 'amount' => $payrollDetail->total_allowances];
                            }
                            try {
                                $dynamicEarnings = $payrollDetail->earnings;
                                if (!empty($dynamicEarnings) && is_array($dynamicEarnings)) {
                                    foreach ($dynamicEarnings as $earning) {
                                        if (isset($earning['name']) && isset($earning['amount']) && $earning['amount'] > 0) {
                                            $earnings[] = ['name' => $earning['name'], 'amount' => $earning['amount']];
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                // Ignore dynamic earnings on error
                            }
                        @endphp
                        @foreach($earnings as $earning)
                            <div class="salary-row">
                                <span>{{ $earning['name'] }}:</span>
                                <span>Rp {{ number_format($earning['amount'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        <div class="salary-row total">
                            <span>Total Pendapatan:</span>
                            <span>Rp {{ number_format($payrollDetail->gross_salary, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">Potongan</div>
                    <div class="deductions">
                        @php
                            $deductions = [];
                            if ($payrollDetail->pph_21 > 0) {
                                $deductions[] = ['name' => 'PPh 21', 'amount' => $payrollDetail->pph_21];
                            }
                            if ($payrollDetail->bpjs_kesehatan_emp > 0) {
                                $deductions[] = ['name' => 'BPJS Kesehatan', 'amount' => $payrollDetail->bpjs_kesehatan_emp];
                            }
                            if ($payrollDetail->bpjs_tk_emp > 0) {
                                $deductions[] = ['name' => 'BPJS Ketenagakerjaan', 'amount' => $payrollDetail->bpjs_tk_emp];
                            }
                            try {
                                $dynamicDeductions = $payrollDetail->deductions;
                                if (!empty($dynamicDeductions) && is_array($dynamicDeductions)) {
                                    foreach ($dynamicDeductions as $deduction) {
                                        if (isset($deduction['name']) && isset($deduction['amount']) && $deduction['amount'] > 0) {
                                            $deductions[] = ['name' => $deduction['name'], 'amount' => $deduction['amount']];
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                // Ignore dynamic deductions on error
                            }
                        @endphp
                        @foreach($deductions as $deduction)
                            <div class="salary-row">
                                <span>{{ $deduction['name'] }}:</span>
                                <span>Rp {{ number_format($deduction['amount'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        <div class="salary-row total">
                            <span>Total Potongan:</span>
                            <span>Rp {{ number_format($payrollDetail->total_deductions, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="take-home">
                TAKE HOME PAY: Rp {{ number_format($payrollDetail->net_salary, 0, ',', '.') }}
            </div>
        </div>

        <div class="footer">
            <div class="signature">
                <div class="generation-info">
                    Dibuat pada: {{ $generationDate->format('d/m/Y H:i') }}
                </div>
                <div class="signature-line"></div>
                <div>HRD / Manager</div>
            </div>
            <div class="qr-code">
                <div><strong>QR Code</strong></div>
                <div>{!! $qrCodeSvg !!}</div>
                <div>Scan untuk verifikasi slip gaji</div>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };

        // Close window after printing
        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 1000);
        };
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $payrollDetail->employee->name ?? 'Employee' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            background-color: #ffffff;
            color: #000000;
            font-size: 11px;
        }
        .container {
            max-width: 190mm;
            margin: 0 auto;
            background: white;
            border: 1px solid #ccc;
        }
        .header {
            text-align: center;
            padding: 5px;
            border-bottom: 2px solid #333;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }
        .period {
            font-size: 10px;
            margin: 2px 0;
        }
        .content {
            padding: 10px;
        }
        .section {
            margin-bottom: 8px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
            border-bottom: 1px solid #333;
            padding-bottom: 1px;
        }
        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            font-size: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }
        .salary-breakdown {
            font-size: 10px;
        }
        .salary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
            padding: 1px 0;
        }
        .salary-row.total {
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 2px;
            margin-top: 2px;
        }
        .deductions {
            font-size: 10px;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 10px;
            border-top: 1px solid #ccc;
            margin-top: 5px;
        }
        .signature {
            text-align: right;
            font-size: 9px;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 120px;
            margin: 2px 0;
        }
        .qr-code {
            text-align: center;
            font-size: 8px;
        }
        .generation-info {
            font-size: 8px;
            text-align: center;
            margin-top: 5px;
            color: #666;
        }
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .take-home {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
            padding: 5px;
            border: 2px solid #333;
            background: #f0f0f0;
        }
    </style>
</head>
<body>
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
                            <span>Nama:</span>
                            <span>{{ $payrollDetail->employee->name ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span>ID Karyawan:</span>
                            <span>{{ $payrollDetail->employee->employee_id ?? $payrollDetail->employee->nip ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span>Departemen:</span>
                            <span>{{ $payrollDetail->employee->department->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="info-row">
                            <span>Jabatan:</span>
                            <span>{{ $payrollDetail->employee->position ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span>Status:</span>
                            <span>{{ $payrollDetail->employee->employment_status ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span>Bank:</span>
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
                            // Add basic salary
                            if ($payrollDetail->basic_salary > 0) {
                                $earnings[] = ['name' => 'Gaji Pokok', 'amount' => $payrollDetail->basic_salary];
                            }
                            // Add allowances
                            if ($payrollDetail->total_allowances > 0) {
                                $earnings[] = ['name' => 'Tunjangan', 'amount' => $payrollDetail->total_allowances];
                            }
                            // Add dynamic earnings if available
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
                            // Add standard deductions
                            if ($payrollDetail->pph_21 > 0) {
                                $deductions[] = ['name' => 'PPh 21', 'amount' => $payrollDetail->pph_21];
                            }
                            if ($payrollDetail->bpjs_kesehatan_emp > 0) {
                                $deductions[] = ['name' => 'BPJS Kesehatan', 'amount' => $payrollDetail->bpjs_kesehatan_emp];
                            }
                            if ($payrollDetail->bpjs_tk_emp > 0) {
                                $deductions[] = ['name' => 'BPJS Ketenagakerjaan', 'amount' => $payrollDetail->bpjs_tk_emp];
                            }
                            // Add dynamic deductions if available
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
                @if($qrCodeSvg)
                    <div style="margin-bottom: 2px; padding: 5px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                        {!! $qrCodeSvg !!}
                    </div>
                    <div style="font-size: 8px; color: #000; font-weight: bold;">SCAN QR CODE</div>
                    <div style="font-size: 7px; color: #666; margin-top: 2px;">untuk verifikasi</div>
                @else
                    <div style="width: 100px; height: 100px; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #999; text-align: center; margin: 0 auto;">
                        QR Code<br>Tidak Tersedia
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payrollDetail->employee->full_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-info {
            margin-bottom: 10px;
        }
        .payslip-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .period-info {
            font-size: 14px;
            color: #666;
        }
        .employee-info {
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        .salary-details {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .salary-details th,
        .salary-details td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .salary-details th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .salary-summary {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        .summary-section {
            width: 48%;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <strong>{{ $company['name'] }}</strong><br>
            {{ $company['address'] }}<br>
            Phone: {{ $company['phone'] }}
        </div>
        <div class="payslip-title">PAYSLIP</div>
        <div class="period-info">
            Period: {{ $payrollDetail->payrollPeriod->period_name }}<br>
            Payment Date: {{ $payrollDetail->payrollPeriod->payment_date->format('d M Y') }}
        </div>
    </div>

    <div class="employee-info">
        <div class="info-row">
            <span class="info-label">Employee ID:</span>
            <span>{{ $payrollDetail->employee->nip }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Name:</span>
            <span>{{ $payrollDetail->employee->full_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Department:</span>
            <span>{{ $payrollDetail->employee->department->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Position:</span>
            <span>{{ $payrollDetail->employee->position }}</span>
        </div>
    </div>

    <table class="salary-details">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Amount (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td style="text-align: right;">{{ number_format($payrollDetail->basic_salary, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Allowances</td>
                <td style="text-align: right;">{{ number_format($payrollDetail->total_allowances, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>Gross Salary</td>
                <td style="text-align: right;">{{ number_format($payrollDetail->gross_salary, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>PPh 21 Tax</td>
                <td style="text-align: right;">{{ number_format($payrollDetail->pph_21, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>BPJS Kesehatan</td>
                <td style="text-align: right;">{{ number_format($payrollDetail->bpjs_kesehatan_emp, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>BPJS TK</td>
                <td style="text-align: right;">{{ number_format($payrollDetail->bpjs_tk_emp, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Other Deductions</td>
                <td style="text-align: right;">{{ number_format($payrollDetail->total_deductions - $payrollDetail->pph_21 - $payrollDetail->bpjs_kesehatan_emp - $payrollDetail->bpjs_tk_emp, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Deductions</td>
                <td style="text-align: right;">{{ number_format($payrollDetail->total_deductions, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row" style="background-color: #e8f5e8; border: 2px solid #4CAF50;">
                <td><strong>NET SALARY</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($payrollDetail->net_salary, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if($payrollDetail->calculation_details)
        <div class="salary-summary">
            <div class="summary-section">
                <h4>Attendance Summary:</h4>
                <p>Working Days: {{ $payrollDetail->calculation_details['working_days'] ?? 'N/A' }}</p>
                <p>Present Days: {{ $payrollDetail->calculation_details['present_days'] ?? 'N/A' }}</p>
            </div>
            <div class="summary-section">
                <h4>Bank Information:</h4>
                <p>Bank: {{ $payrollDetail->employee->bank_name }}</p>
                <p>Account: {{ $payrollDetail->employee->bank_account_no }}</p>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated payslip. No signature required.</p>
        <p>Generated on: {{ now()->format('d M Y H:i:s') }}</p>
    </div>
</body>
</html>
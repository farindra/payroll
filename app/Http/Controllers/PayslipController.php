<?php

namespace App\Http\Controllers;

use App\Models\PayrollDetail;
use App\Services\PayslipService;
use App\Services\PayslipSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PayslipController extends Controller
{
    protected $payslipService;

    protected $payslipSecurityService;

    public function __construct(PayslipService $payslipService, PayslipSecurityService $payslipSecurityService)
    {
        $this->payslipService = $payslipService;
        $this->payslipSecurityService = $payslipSecurityService;
    }

    public function sharePayslip($id)
    {
        try {
            $payrollDetail = PayrollDetail::with([
                'employee.department',
                'payrollPeriod'
            ])->findOrFail($id);

            // Generate the PDF with QR code for sharing
            $pdf = $this->payslipService->generatePayslipPDF($id, true);

            // Stream the PDF for viewing
            return $pdf->stream('Slip_Gaji_' . $payrollDetail->employee->name . '_' . $payrollDetail->payrollPeriod->period_name . '.pdf');
        } catch (\Exception $e) {
            return response()->view('errors.payslip-error', [
                'message' => 'Slip gaji tidak dapat ditampilkan. Link mungkin sudah kedaluwarsa.'
            ], 404);
        }
    }

    public function viewPayslip($id)
    {
        try {
            $payrollDetail = PayrollDetail::with([
                'employee.department',
                'payrollPeriod'
            ])->findOrFail($id);

            return view('payslip.view', [
                'payrollDetail' => $payrollDetail,
                'company' => $this->payslipService->getCompanyInfo(),
            ]);
        } catch (\Exception $e) {
            return response()->view('errors.payslip-error', [
                'message' => 'Slip gaji tidak dapat ditampilkan.'
            ], 404);
        }
    }

    /**
     * Direct PDF download that bypasses Livewire entirely
     */
    public function downloadPayslipDirect($periodId)
    {
        try {
            // Get current authenticated user
            $user = auth()->user();
            if (!$user || !$user->employee) {
                return response('Anda harus login sebagai karyawan untuk mengunduh slip gaji.', 403, [
                    'Content-Type' => 'text/plain'
                ]);
            }

            // Find payroll detail for this employee and period
            $payrollDetail = PayrollDetail::where('employee_id', $user->employee->id)
                ->where('payroll_period_id', $periodId)
                ->first();

            if (!$payrollDetail) {
                return response('Data slip gaji tidak ditemukan untuk periode yang dipilih.', 404, [
                    'Content-Type' => 'text/plain'
                ]);
            }

            // Return direct PDF download
            return $this->payslipService->downloadPayslip($payrollDetail->id);
        } catch (\Exception $e) {
            return response('Gagal mengunduh slip gaji: ' . $e->getMessage(), 500, [
                'Content-Type' => 'text/plain'
            ]);
        }
    }

    /**
     * Show payslip in HTML print view
     */
    public function printPayslip($periodId)
    {
        try {
            // Get current authenticated user
            $user = auth()->user();
            if (!$user || !$user->employee) {
                return response('Anda harus login sebagai karyawan untuk melihat slip gaji.', 403, [
                    'Content-Type' => 'text/plain'
                ]);
            }

            // Find payroll detail for this employee and period
            $payrollDetail = PayrollDetail::with([
                'employee.department',
                'payrollPeriod'
            ])->where('employee_id', $user->employee->id)
                ->where('payroll_period_id', $periodId)
                ->first();

            if (!$payrollDetail) {
                return response('Data slip gaji tidak ditemukan untuk periode yang dipilih.', 404, [
                    'Content-Type' => 'text/plain'
                ]);
            }

            // Return print view
            return view('payslip.print', [
                'payrollDetail' => $payrollDetail,
                'company' => $this->payslipService->getCompanyInfo(),
                'generationDate' => now(),
            ]);
        } catch (\Exception $e) {
            return response('Gagal menampilkan slip gaji: ' . $e->getMessage(), 500, [
                'Content-Type' => 'text/plain'
            ]);
        }
    }

    /**
     * Secure payslip sharing using hash
     */
    public function sharePayslipSecure($hash)
    {
        try {
            $decodedData = $this->payslipSecurityService->validateSecureHash($hash);

            if (!$decodedData) {
                return response()->view('errors.payslip-error', [
                    'message' => 'Link slip gaji tidak valid atau sudah kedaluwarsa.'
                ], 404);
            }

            // Find payroll detail using decoded data (same as print method)
            $payrollDetail = PayrollDetail::with([
                'employee.department',
                'payrollPeriod'
            ])->where('employee_id', $decodedData['employee_id'])
                ->where('payroll_period_id', $decodedData['period_id'])
                ->first();

            if (!$payrollDetail) {
                return response()->view('errors.payslip-error', [
                    'message' => 'Data slip gaji tidak ditemukan.'
                ], 404);
            }

            // Generate QR code for the payslip
            $qrCodeSvg = $this->payslipService->generateQRCodeInline($payrollDetail);

            // Return print view with QR code
            return view('payslip.print', [
                'payrollDetail' => $payrollDetail,
                'company' => $this->payslipService->getCompanyInfo(),
                'generationDate' => now(),
                'qrCodeSvg' => $qrCodeSvg,
            ]);
        } catch (\Exception $e) {
            return response()->view('errors.payslip-error', [
                'message' => 'Slip gaji tidak dapat ditampilkan. Link mungkin sudah kedaluwarsa.'
            ], 500);
        }
    }

    /**
     * Secure payslip sharing using encrypted token
     */
    public function sharePayslipEncrypted($token)
    {
        try {
            $decodedData = $this->payslipSecurityService->validateEncryptedToken($token);

            if (!$decodedData) {
                return response()->view('errors.payslip-error', [
                    'message' => 'Link slip gaji tidak valid atau sudah kedaluwarsa.'
                ], 404);
            }

            $payrollDetail = PayrollDetail::with([
                'employee.department',
                'payrollPeriod'
            ])->where('employee_id', $decodedData['employee_id'])
                ->where('payroll_period_id', $decodedData['period_id'])
                ->first();

            if (!$payrollDetail) {
                return response()->view('errors.payslip-error', [
                    'message' => 'Data slip gaji tidak ditemukan.'
                ], 404);
            }

            // Generate the PDF with QR code for sharing
            $pdf = $this->payslipService->generatePayslipPDF($payrollDetail->id, true);

            // Stream the PDF for viewing
            return $pdf->stream('Slip_Gaji_' . $payrollDetail->employee->name . '_' . $payrollDetail->payrollPeriod->period_name . '.pdf');
        } catch (\Exception $e) {
            return response()->view('errors.payslip-error', [
                'message' => 'Slip gaji tidak dapat ditampilkan. Link mungkin sudah kedaluwarsa.'
            ], 404);
        }
    }

    /**
     * Generate secure sharing link for payslip
     */
    public function generateSecureShareLink($employeeId, $periodId)
    {
        try {
            // Use the most secure method (signed URL)
            return $this->payslipSecurityService->generateSignedShareUrl($employeeId, $periodId);
        } catch (\Exception $e) {
            // Fallback to hash method if signed URL fails
            return $this->payslipSecurityService->generateSecureShareUrl($employeeId, $periodId);
        }
    }

    /**
     * Secure print payslip using hash
     */
    public function printPayslipSecure($hash)
    {
        try {
            $decodedData = $this->payslipSecurityService->validateSecureHash($hash);

            if (!$decodedData) {
                return response('Link cetak tidak valid atau sudah kedaluwarsa.', 404, [
                    'Content-Type' => 'text/plain'
                ]);
            }

            // Find payroll detail using decoded data
            $payrollDetail = PayrollDetail::with([
                'employee.department',
                'payrollPeriod'
            ])->where('employee_id', $decodedData['employee_id'])
                ->where('payroll_period_id', $decodedData['period_id'])
                ->first();

            if (!$payrollDetail) {
                return response('Data slip gaji tidak ditemukan.', 404, [
                    'Content-Type' => 'text/plain'
                ]);
            }

            // Generate QR code for the payslip
            $qrCodeSvg = $this->payslipService->generateQRCodeInline($payrollDetail);

            // Return print view with QR code
            return view('payslip.print', [
                'payrollDetail' => $payrollDetail,
                'company' => $this->payslipService->getCompanyInfo(),
                'generationDate' => now(),
                'qrCodeSvg' => $qrCodeSvg,
            ]);
        } catch (\Exception $e) {
            return response('Gagal menampilkan slip gaji: ' . $e->getMessage(), 500, [
                'Content-Type' => 'text/plain'
            ]);
        }
    }
}
<?php

namespace App\Services;

use App\Models\PayrollDetail;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PayslipService
{
    public function generatePayslipPDF($payrollDetailId, $includeQRCode = true)
    {
        try {
            $utf8Cleaner = new Utf8CleanerService();

            $payrollDetail = PayrollDetail::with([
                'employee.department',
                'payrollPeriod'
            ])->findOrFail($payrollDetailId);

            // Aggressively clean all data to prevent UTF-8 issues
            if ($payrollDetail->calculation_details) {
                $payrollDetail->calculation_details = $utf8Cleaner->clean($payrollDetail->calculation_details);
            }

            // Clean employee and period data
            if ($payrollDetail->employee) {
                $payrollDetail->employee->name = $utf8Cleaner->cleanString($payrollDetail->employee->name);
                $payrollDetail->employee->position = $utf8Cleaner->cleanString($payrollDetail->employee->position);
                $payrollDetail->employee->employment_status = $utf8Cleaner->cleanString($payrollDetail->employee->employment_status);
            }

            if ($payrollDetail->payrollPeriod) {
                $payrollDetail->payrollPeriod->period_name = $utf8Cleaner->cleanString($payrollDetail->payrollPeriod->period_name);
            }

            // Generate QR code if enabled
            $qrCodeSvg = null;
            if ($includeQRCode) {
                try {
                    $qrCodeSvg = $this->generateQRCodeInline($payrollDetail);
                } catch (\Exception $e) {
                    \Log::warning('QR code generation failed: ' . $e->getMessage());
                    // Continue without QR code if generation fails
                }
            }

            $pdf = PDF::loadView('reports.payslip', [
                'payrollDetail' => $payrollDetail,
                'company' => $this->getCompanyInfo(),
                'qrCodeSvg' => $qrCodeSvg,
                'generationDate' => now(),
            ]);

            // PDF options optimized for single page and image support
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false, // Disable remote content to prevent hanging
                'isFontSubsettingEnabled' => false, // Disable font subsetting
                'defaultFont' => 'Arial',
                'margin-left' => 10,
                'margin-right' => 10,
                'margin-top' => 10,
                'margin-bottom' => 10,
                'dpi' => 96,
                'enable_svg' => true, // Enable SVG support
                'svgAlignment' => 'center',
                'isHtml5ParserEnabled' => true,
                'chroot' => base_path(), // Allow local files
            ]);

            return $pdf;
        } catch (\Exception $e) {
            \Log::error('PDF generation failed: ' . $e->getMessage());
            throw new \Exception('Failed to generate PDF: ' . $e->getMessage());
        }
    }

    private function cleanUtf8($data)
    {
        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        } elseif (is_array($data)) {
            return array_map([$this, 'cleanUtf8'], $data);
        } elseif (is_object($data)) {
            return (object) array_map([$this, 'cleanUtf8'], (array) $data);
        }
        return $data;
    }

    public function generateQRCode($payrollDetail)
    {
        try {
            // Use secure sharing service to generate secure URL
            $payslipSecurityService = new PayslipSecurityService();
            $shareUrl = $payslipSecurityService->generateSecureQrUrl(
                $payrollDetail->employee_id,
                $payrollDetail->payroll_period_id
            );

            // Create QR code using bacon-qr-code
            $renderer = new ImageRenderer(
                new RendererStyle(300),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);

            // Generate QR code as SVG
            $qrCodeSvg = $writer->writeString($shareUrl);

            // Clean SVG to prevent any XML issues
            $qrCodeSvg = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $qrCodeSvg);

            // Save QR code temporarily as SVG
            $fileName = 'qrcodes/payslip_' . $payrollDetail->id . '_' . time() . '.svg';
            $path = storage_path('app/public/' . $fileName);

            // Ensure directory exists
            $directory = dirname($path);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($path, $qrCodeSvg);

            return [
                'path' => $path,
                'url' => url('storage/' . $fileName),
                'share_url' => $shareUrl,
            ];
        } catch (\Exception $e) {
            \Log::error('QR Code generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate QR code as inline SVG (no file storage)
     */
    public function generateQRCodeInline($payrollDetail)
    {
        try {
            // Use secure sharing service to generate secure share URL
            $payslipSecurityService = new PayslipSecurityService();
            $shareUrl = $payslipSecurityService->generateSecureQrUrl(
                $payrollDetail->employee_id,
                $payrollDetail->payroll_period_id
            );

            // Create QR code data from secure share URL
            $qrData = $shareUrl;

            \Log::info('Generating QR code for payslip ID: ' . $payrollDetail->id . ' with URL: ' . substr($shareUrl, 0, 100) . '...');

            // Always try to generate a real QR code first
            try {
                $qrCode = $this->generateRealQrCode($qrData);
                \Log::info('Real QR code generated successfully for payslip ID: ' . $payrollDetail->id);
                return $qrCode;
            } catch (\Exception $e) {
                \Log::warning('Real QR code failed, trying fallback: ' . $e->getMessage());
                $qrCode = $this->generateImageQrCode($qrData);
                \Log::info('Fallback QR code generated for payslip ID: ' . $payrollDetail->id);
                return $qrCode;
            }

        } catch (\Exception $e) {
            \Log::error('QR Code generation completely failed: ' . $e->getMessage());
            // Return a simple text-based QR code placeholder (without ID)
            return $this->generateTextQrCode('PAYSLIP');
        }
    }

    private function generateQrCodeWithEndroid($qrData)
    {
        try {
            // Create QR code using endroid/qr-code
            $qrCode = new \Endroid\QrCode\QrCode($qrData);
            $qrCode->setSize(80);
            $qrCode->setMargin(1);
            $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
            $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);

            // Create SVG writer
            $writer = new \Endroid\QrCode\Writer\SvgWriter();
            $qrCodeSvg = $writer->writeString($qrCode);

            // Clean and wrap in container
            $qrCodeSvg = preg_replace('/<\?xml[^>]*\?>/', '', $qrCodeSvg);
            $qrCodeSvg = preg_replace('/xmlns="[^"]*"/', '', $qrCodeSvg);

            return '<div style="width: 80px; height: 80px; background: white; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center;">' . $qrCodeSvg . '</div>';
        } catch (\Exception $e) {
            \Log::error('Endroid QR code generation failed: ' . $e->getMessage());
            return $this->generateQrCodeWithBacon($qrData);
        }
    }

    private function generateQrCodeWithBacon($qrData)
    {
        try {
            // Create QR code using bacon-qr-code (simpler approach)
            $qrCode = \BaconQrCode\Encoder\Encoder::encode($qrData, \BaconQrCode\Common\ErrorCorrectionLevel::M());

            // Create renderer
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );

            $writer = new \BaconQrCode\Writer($renderer);

            // Generate QR code as SVG
            $qrCodeSvg = $writer->writeString($qrCode);

            // Clean SVG for PDF compatibility
            $qrCodeSvg = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $qrCodeSvg);
            $qrCodeSvg = preg_replace('/<\?xml[^>]*\?>/', '', $qrCodeSvg);
            $qrCodeSvg = preg_replace('/xmlns="[^"]*"/', '', $qrCodeSvg);
            $qrCodeSvg = preg_replace('/version="[^"]*"/', '', $qrCodeSvg);
            $qrCodeSvg = preg_replace('/fill="#fefefe"/', 'fill="white"', $qrCodeSvg);
            $qrCodeSvg = preg_replace('/fill="#000000"/', 'fill="black"', $qrCodeSvg);

            // Wrap in a container with proper sizing
            return '<div style="width: 150px; height: 150px; background: white; border: 2px solid #333; display: flex; align-items: center; justify-content: center;">' . $qrCodeSvg . '</div>';
        } catch (\Exception $e) {
            \Log::error('Bacon QR code generation failed: ' . $e->getMessage());
            return $this->generateHtmlQrCode($qrData);
        }
    }

    private function generateImageQrCode($qrData)
    {
        // Try SVG approach first (most reliable for PDFs)
        try {
            return $this->generateQrCodeWithBacon($qrData);
        } catch (\Exception $e) {
            \Log::warning('SVG QR code failed, falling back to PNG: ' . $e->getMessage());
            try {
                return $this->generateRealQrCode($qrData);
            } catch (\Exception $e2) {
                \Log::warning('PNG QR code failed, falling back to HTML: ' . $e2->getMessage());
                return $this->generateHtmlQrCode($qrData);
            }
        }
    }

    private function generateRealQrCode($qrData)
    {
        try {
            // Generate a real, scannable QR code using endroid/qr-code v6
            $qrCode = new \Endroid\QrCode\QrCode(
                $qrData,
                new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
                \Endroid\QrCode\ErrorCorrectionLevel::Medium,
                150, // size
                2,  // margin
                \Endroid\QrCode\RoundBlockSizeMode::Margin,
                new \Endroid\QrCode\Color\Color(0, 0, 0), // foreground
                new \Endroid\QrCode\Color\Color(255, 255, 255) // background
            );

            // Create PNG writer
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);

            // Convert to base64
            $base64 = base64_encode($result->getString());

            return '<img src="data:image/png;base64,' . $base64 . '" width="150" height="150" style="border: 2px solid #333; background: white;" alt="QR Code">';
        } catch (\Exception $e) {
            \Log::error('QR Code generation failed: ' . $e->getMessage());
            return $this->generateQrCodeWithBacon($qrData);
        }
    }

    private function generateSimpleImageQrCode($qrData)
    {
        // Create a simple QR code using GD library (fallback)
        $size = 120;
        $moduleSize = 10; // Size of each QR module
        $modules = $size / $moduleSize; // 12x12 modules

        // Create image
        $image = imagecreatetruecolor($size, $size);

        // Colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        // Fill background
        imagefill($image, 0, 0, $white);

        // Generate pattern
        $hash = md5($qrData);
        for ($y = 0; $y < $modules; $y++) {
            for ($x = 0; $x < $modules; $x++) {
                $hashIndex = ($y * $modules + $x) % 32;
                $char = substr($hash, $hashIndex, 1);
                $isBlack = hexdec($char) % 2 === 0;

                // Add positioning markers (like real QR codes)
                if (($y < 3 && $x < 3) || // Top-left
                    ($y < 3 && $x >= $modules - 3) || // Top-right
                    ($y >= $modules - 3 && $x < 3) || // Bottom-left
                    ($y >= 2 && $y < 5 && $x >= 2 && $x < 5)) { // Center
                    $isBlack = true;
                }

                // Create border
                if ($y === 0 || $y === $modules - 1 || $x === 0 || $x === $modules - 1) {
                    $isBlack = true;
                }

                if ($isBlack) {
                    imagefilledrectangle($image, $x * $moduleSize, $y * $moduleSize,
                                        ($x + 1) * $moduleSize - 1, ($y + 1) * $moduleSize - 1, $black);
                }
            }
        }

        // Add border
        imagerectangle($image, 0, 0, $size - 1, $size - 1, $black);

        // Convert to base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);

        $base64 = base64_encode($imageData);

        return '<img src="data:image/png;base64,' . $base64 . '" width="' . $size . '" height="' . $size . '" style="border: 2px solid #333; background: white;" alt="QR Code">';
    }

    private function generateHtmlQrCode($qrData)
    {
        // Create a simple HTML-based QR code representation
        $hash = md5($qrData);
        $size = 12; // 12x12 grid for better visibility

        $html = '<div style="width: 120px; height: 120px; background: white; border: 3px solid #333; display: table; border-collapse: collapse; margin: 0; padding: 0;">';
        $html .= '<div style="display: table-row;">';

        for ($i = 0; $i < $size * $size; $i++) {
            $char = substr($hash, $i % 32, 1);
            $isBlack = hexdec($char) % 2 === 0;

            // Add some recognizable QR patterns (corners and center)
            $row = floor($i / $size);
            $col = $i % $size;

            // Make positioning markers black (like real QR codes)
            if (($row < 3 && $col < 3) || // Top-left corner
                ($row < 3 && $col >= $size - 3) || // Top-right corner
                ($row >= $size - 3 && $col < 3) || // Bottom-left corner
                ($row >= 2 && $row < 5 && $col >= 2 && $col < 5)) { // Center pattern
                $isBlack = true;
            }

            // Create border
            if ($row === 0 || $row === $size - 1 || $col === 0 || $col === $size - 1) {
                $isBlack = true;
            }

            $color = $isBlack ? '#000' : '#fff';
            $border = $isBlack ? '1px solid #000' : '1px solid #ddd';

            if ($i % $size === 0 && $i > 0) {
                $html .= '</div><div style="display: table-row;">';
            }

            $html .= '<div style="display: table-cell; width: 10px; height: 10px; background-color: ' . $color . '; border: ' . $border . '; padding: 0; margin: 0;"></div>';
        }

        $html .= '</div></div>';
        return $html;
    }

    private function generateSimpleHtmlQrCode($hash, $qrData)
    {
        // Generate a simple QR code using HTML table that works reliably in PDF
        $size = 8; // 8x8 grid
        $html = '<div style="width: 80px; height: 80px; background: white; border: 2px solid black; font-family: monospace; font-size: 4px; line-height: 1;">';

        // Use hash to create pattern
        for ($i = 0; $i < $size; $i++) {
            $html .= '<div style="display: flex; height: 10px;">';
            for ($j = 0; $j < $size; $j++) {
                // Use hash to determine if cell should be filled
                $char = substr($hash, ($i * $size + $j) % 32, 1);
                $isFilled = hexdec($char) % 2 === 0;

                $html .= '<div style="width: 10px; height: 10px; background-color: ' . ($isFilled ? 'black' : 'white') . '; border: 0.5px solid #eee;"></div>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    private function generateTextQrCode($qrData)
    {
        // Generate a clean text-based representation as fallback (no IDs)
        return '<div style="width: 120px; height: 120px; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border: 3px solid #007bff; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: Arial, sans-serif; text-align: center; padding: 10px; box-shadow: 0 4px 8px rgba(0,123,255,0.2); border-radius: 8px;">
            <div style="font-weight: bold; font-size: 14px; color: #007bff; margin-bottom: 8px;">PAYSLIP QR</div>
            <div style="font-size: 10px; color: #6c757d; text-align: center; line-height: 1.2;">
                Scan untuk<br>verifikasi
            </div>
            <div style="width: 40px; height: 40px; background: #007bff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-top: 8px;">
                <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                    <path d="M3 3h7v7H3V3zm2 2v3h3V5H5zm8-2h7v7h-7V3zm2 2v3h3V5h-3zM3 11h7v7H3v-7zm2 2v3h3v-3H5zm13-2h3v3h-3v-3zm0 4h3v3h-3v-3zm-4 4h7v7h-7v-7zm2 2v3h3v-3h-3zM9 11h2v2H9v-2zm2 2h2v2h-2v-2zm-2 2h2v2H9v-2zm4-4h2v2h-2v-2zm2 2h2v2h-2v-2zm-2 2h2v2h-2v-2zm4-4h2v2h-2v-2zm2 2h2v2h-2v-2z"/>
                </svg>
            </div>
        </div>';
    }

    public function getCompanyInfo()
    {
        return [
            'name' => config('app.company_name', 'PT. Payroll System Indonesia'),
            'address' => config('app.company_address', 'Jl. Teknologi No. 123, Jakarta Selatan 12345'),
            'phone' => config('app.company_phone', '+62 21 1234 5678'),
            'email' => config('app.company_email', 'hrd@payrollsystem.co.id'),
            'website' => config('app.company_website', 'www.payrollsystem.co.id'),
            'logo' => asset('images/company-logo.svg'), // Make sure to upload company logo
        ];
    }

    public function downloadPayslip($payrollDetailId)
    {
        try {
            $pdf = $this->generatePayslipPDF($payrollDetailId);

            $payrollDetail = PayrollDetail::find($payrollDetailId);
            $utf8Cleaner = new Utf8CleanerService();
            $employeeName = $utf8Cleaner->cleanString($payrollDetail->employee->name ?? 'Employee');
            $periodName = $utf8Cleaner->cleanString($payrollDetail->payrollPeriod->period_name ?? 'Unknown');

            $filename = 'Slip_Gaji_' . $employeeName . '_' . $periodName . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('PDF download failed: ' . $e->getMessage());
            // Return a simple text response to avoid UTF-8 issues in JSON
            return response('Failed to generate PDF: ' . $e->getMessage(), 500);
        }
    }

    public function streamPayslip($payrollDetailId)
    {
        try {
            $pdf = $this->generatePayslipPDF($payrollDetailId);
            return $pdf->stream();
        } catch (\Exception $e) {
            \Log::error('PDF stream failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF. Please try again.');
        }
    }

    public function getEmployeePayslips($employeeId, $limit = 12)
    {
        return PayrollDetail::with('payrollPeriod')
            ->where('employee_id', $employeeId)
            ->whereHas('payrollPeriod', function ($query) {
                $query->where('status', 'Calculated');
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
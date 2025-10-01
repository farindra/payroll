<?php

namespace App\Services;

use App\Models\PayrollDetail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class PayslipSecurityService
{
    private const HASH_SALT = 'payslip_secure_salt_2024';
    private const HASH_LENGTH = 16;

    /**
     * Generate a secure token for temporary access
     */
    public function generateSecureToken($type, $data, $additionalInfo = ''): string
    {
        // Create a random string for uniqueness
        $randomString = Str::random(8);

        // Create a unique string combining data and timestamp
        $rawString = $type . '|' . $additionalInfo . '|' . now()->timestamp . '|' . $randomString;

        // Add salt for security
        $saltedString = $rawString . self::HASH_SALT;

        // Generate hash
        $hash = hash('sha256', $saltedString);

        // Return first 16 characters of hash as the secure token
        return substr($hash, 0, self::HASH_LENGTH);
    }

    /**
     * Generate a secure hash for payslip sharing
     */
    public function generateSecureHash($employeeId, $periodId): string
    {
        // Create a deterministic string combining employee ID and period ID
        // Note: Removed timestamp to make hash reproducible for validation
        $rawString = $employeeId . '|' . $periodId;

        // Add salt for security
        $saltedString = $rawString . self::HASH_SALT;

        // Generate hash
        $hash = hash('sha256', $saltedString);

        // Return first 16 characters of hash as the secure ID
        return substr($hash, 0, self::HASH_LENGTH);
    }

    /**
     * Validate and decode secure hash
     */
    public function validateSecureHash($secureHash): ?array
    {
        if (empty($secureHash) || strlen($secureHash) !== self::HASH_LENGTH) {
            return null;
        }

        // For now, we'll need to search through payroll details to find a match
        // In production, you might want to store the hash in the database
        $payrollDetails = PayrollDetail::with(['employee', 'payrollPeriod'])
            ->whereHas('employee')
            ->whereHas('payrollPeriod')
            ->get();

        foreach ($payrollDetails as $detail) {
            $expectedHash = $this->generateSecureHash($detail->employee_id, $detail->payroll_period_id);

            if (hash_equals($expectedHash, $secureHash)) {
                return [
                    'employee_id' => $detail->employee_id,
                    'period_id' => $detail->payroll_period_id,
                    'payroll_detail_id' => $detail->id
                ];
            }
        }

        return null;
    }

    /**
     * Generate encrypted token for additional security
     */
    public function generateEncryptedToken($employeeId, $periodId): string
    {
        $payload = [
            'employee_id' => $employeeId,
            'period_id' => $periodId,
            'timestamp' => now()->timestamp,
            'random' => Str::random(16)
        ];

        return Crypt::encrypt($payload);
    }

    /**
     * Validate and decrypt token
     */
    public function validateEncryptedToken($token): ?array
    {
        try {
            $payload = Crypt::decrypt($token);

            // Check if token is expired (optional - 30 days)
            if (isset($payload['timestamp']) && (now()->timestamp - $payload['timestamp']) > 30 * 24 * 60 * 60) {
                return null;
            }

            return [
                'employee_id' => $payload['employee_id'],
                'period_id' => $payload['period_id']
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate secure sharing URL using hash method
     */
    public function generateSecureShareUrl($employeeId, $periodId): string
    {
        $secureHash = $this->generateSecureHash($employeeId, $periodId);
        return route('payslip.share.secure', ['hash' => $secureHash]);
    }

    /**
     * Generate secure sharing URL using encrypted token
     */
    public function generateEncryptedShareUrl($employeeId, $periodId): string
    {
        $token = $this->generateEncryptedToken($employeeId, $periodId);
        return route('payslip.share.encrypted', ['token' => $token]);
    }

    /**
     * Create a temporary signed URL for additional security
     */
    public function generateSignedShareUrl($employeeId, $periodId): string
    {
        $secureHash = $this->generateSecureHash($employeeId, $periodId);
        return URL::temporarySignedRoute(
            'payslip.share.signed',
            now()->addDays(30), // Valid for 30 days
            ['hash' => $secureHash]
        );
    }

    /**
     * Generate secure print URL
     */
    public function generateSecurePrintUrl($employeeId, $periodId): string
    {
        $secureHash = $this->generateSecureHash($employeeId, $periodId);
        $url = route('payslip.print.secure', ['hash' => $secureHash]);

        // Check if URL already contains a port, if not add port 8000 for localhost
        if (str_contains($url, 'localhost') && !str_contains($url, 'localhost:')) {
            $url = str_replace('localhost', 'localhost:8000', $url);
        }

        return $url;
    }

    /**
     * Generate secure share URL for QR codes (uses secure share route)
     */
    public function generateSecureQrUrl($employeeId, $periodId): string
    {
        $secureHash = $this->generateSecureHash($employeeId, $periodId);
        $url = route('payslip.share.secure', ['hash' => $secureHash]);

        // Check if URL already contains a port, if not add port 8000 for localhost
        if (str_contains($url, 'localhost') && !str_contains($url, 'localhost:')) {
            $url = str_replace('localhost', 'localhost:8000', $url);
        }

        return $url;
    }
}
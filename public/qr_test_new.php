<!DOCTYPE html>
<html>
<head>
    <title>QR Code Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .qr-test { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .qr-container { display: inline-block; margin: 10px; }
    </style>
</head>
<body>
    <h1>QR Code Generation Test</h1>

    <div class="qr-test">
        <h2>Testing QR Code Generation</h2>
        <?php
        // Test QR code generation
        $qrData = 'https://www.example.com/test';

        try {
            // Include the PayslipService
            require_once __DIR__ . '/../vendor/autoload.php';

            // Create a simple test instance
            $payslipService = new App\Services\PayslipService();

            // Test different QR code generation methods
            echo '<h3>Endroid QR Code:</h3>';
            echo '<div class="qr-container">';
            echo $payslipService->generateRealQrCode($qrData);
            echo '</div>';

            echo '<h3>Bacon QR Code:</h3>';
            echo '<div class="qr-container">';
            echo $payslipService->generateQrCodeWithBacon($qrData);
            echo '</div>';

            echo '<h3>HTML QR Code (Fallback):</h3>';
            echo '<div class="qr-container">';
            echo $payslipService->generateHtmlQrCode($qrData);
            echo '</div>';

        } catch (Exception $e) {
            echo '<p>Error: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>
</body>
</html>
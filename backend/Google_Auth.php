<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPGangsta_GoogleAuthenticator;


$authenticator = new PHPGangsta_GoogleAuthenticator();

$secret = $authenticator->createSecret();
echo "Secret: $secret\n";

$accountName = "urbansiana.id@gmail.com";
$issuer = "field_reporter";
$qrCodeUrl = $authenticator->getQRCodeGoogleUrl($issuer . ':' . $accountName, $secret, $issuer);
echo "QR Code URL: $qrCodeUrl\n";

echo "Silakan pindai QR Code ini dengan Google Authenticator: $qrCodeUrl\n";

echo "Masukkan kode OTP: ";
$handle = fopen("php://stdin", "r");
$code = trim(fgets($handle));
$isValid = $authenticator->verifyCode($secret, $code, 2);
if ($isValid) {
    echo "Kode OTP valid!\n";
} else {
    echo "Kode OTP tidak valid.\n";
}

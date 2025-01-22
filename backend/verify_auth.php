<?php

require_once __DIR__ . '/config/config.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use PHPGangsta_GoogleAuthenticator;

// Mengambil konfigurasi database dari Config
$config = Config::getInstance();
$dbConfig = $config->get('db');

// Koneksi ke database
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']}",
        $dbConfig['user'],
        $dbConfig['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage(),
    ]);
    exit;
}

// Mendapatkan input dari request JSON
$data = json_decode(file_get_contents("php://input"), true);
$code = $data['code'] ?? '';
$token = $data['token'] ?? '';

// Fungsi untuk mengambil secret berdasarkan token dari database
function getSecretFromToken($pdo, $token)
{
    $stmt = $pdo->prepare("SELECT secret FROM users WHERE token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Fungsi untuk validasi kode Google Authenticator
function validateCode($secret, $code)
{
    $sonataAuth = new GoogleAuthenticator();
    $phpGangstaAuth = new PHPGangsta_GoogleAuthenticator();

    $sonataResult = $sonataAuth->checkCode($secret, $code);
    $phpGangstaResult = $phpGangstaAuth->verifyCode($secret, $code, 2);

    return $sonataResult && $phpGangstaResult;
}

// Logika utama
if (!empty($code) && !empty($token)) {
    $secret = getSecretFromToken($pdo, $token);

    if ($secret) {
        $isValid = validateCode($secret, $code);

        if ($isValid) {
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Code is valid',
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid code',
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid token',
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing code or token',
    ]);
}

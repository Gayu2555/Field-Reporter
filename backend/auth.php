<?php
// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/error.log');

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Helper function untuk JSON response
function sendJsonResponse($success, $message, $data = null, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    // Periksa method request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan', 405);
    }

    // Baca raw input
    $rawInput = file_get_contents('php://input');
    if (!$rawInput) {
        throw new Exception('Data tidak diterima', 400);
    }

    // Parse JSON
    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Format JSON tidak valid: ' . json_last_error_msg(), 400);
    }

    // Validasi input
    if (empty($data['email']) || empty($data['password'])) {
        throw new Exception('Email dan password harus diisi', 400);
    }

    require_once __DIR__ . '/config.php';

    // Ambil instance Config
    $config = Config::getInstance();
    $dbConfig = $config->get('db');

    // Buat koneksi PDO
    try {
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8mb4",
            $dbConfig['user'],
            $dbConfig['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw new Exception('Gagal terhubung ke database', 500);
    }

    // Sanitasi input
    $identifier = trim($data['email']);
    $password = $data['password'];

    // Cek apakah input adalah email atau username
    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
    $field = $isEmail ? 'email' : 'username';

    // Query user
    $stmt = $pdo->prepare("SELECT id, email, username, password FROM users WHERE {$field} = :identifier LIMIT 1");
    $stmt->execute(['identifier' => $identifier]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('Email/username atau password salah', 401);
    }

    // Verifikasi password
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Email/username atau password salah', 401);
    }

    // Generate token
    $token = bin2hex(random_bytes(32));

    // Simpan token ke database
    $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, token, created_at) VALUES (:user_id, :token, NOW())");
    $stmt->execute([
        'user_id' => $user['id'],
        'token' => $token
    ]);

    // Kirim response sukses
    sendJsonResponse(true, 'Login berhasil', [
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'username' => $user['username']
        ]
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    sendJsonResponse(false, 'Terjadi kesalahan database', null, 500);
} catch (Exception $e) {
    error_log("Application error: " . $e->getMessage());
    sendJsonResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
}

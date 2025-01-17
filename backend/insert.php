<?php
// Load configuration
require_once __DIR__ . '/config.php';

try {
    // Get config instance
    $config = Config::getInstance();
    $dbConfig = $config->get('db');

    // Create PDO connection
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

    // Test user data
    $email = 'gayuyunma1123@gmail.com';
    $password = 'Gayu@251005777';
    $username = 'gayuyunma';

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
    $stmt->execute([
        'email' => $email,
        'username' => $username
    ]);

    if ($stmt->fetch()) {
        die("Email atau username sudah terdaftar. Tidak bisa membuat user baru.");
    }

    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, created_at) 
        VALUES (:username, :email, :password, NOW())
    ");

    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword
    ]);

    echo "User berhasil dibuat!\n";
    echo "Email: " . $email . "\n";
    echo "Password: " . $password . "\n";
    echo "Username: " . $username . "\n";
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

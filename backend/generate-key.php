<?php
// generate-key.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../backend/keyGenerator.php';

try {
    echo "Generating new application key...\n";
    $key = KeyGenerator::updateEnvFile();
    echo "Application key generated successfully!\n";
    echo "Generated key: " . $key . "\n";
    echo "The key has been automatically added to your .env file.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

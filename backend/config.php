<?php
// config.php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Security configuration class
class Config
{
    private static $instance = null;
    private $config = [];

    private function __construct()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        // Required environment variables
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'APP_KEY'])->notEmpty();

        // Set security configurations
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', $_ENV['SESSION_SECURE'] ?? 1);
        ini_set('session.use_only_cookies', 1);

        $this->config = [
            'db' => [
                'host' => $_ENV['DB_HOST'],
                'name' => $_ENV['DB_NAME'],
                'user' => $_ENV['DB_USER'],
                'pass' => $_ENV['DB_PASSWORD']
            ],
            'security' => [
                'app_key' => $_ENV['APP_KEY'],
                'max_login_attempts' => $_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5,
                'lockout_time' => $_ENV['LOCKOUT_TIME'] ?? 900
            ]
        ];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}

// Database connection class
class Database
{
    private $conn;

    public function __construct()
    {
        $config = Config::getInstance();
        $db_config = $config->get('db');

        try {
            $this->conn = new PDO(
                "mysql:host={$db_config['host']};dbname={$db_config['name']}",
                $db_config['user'],
                $db_config['pass']
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}

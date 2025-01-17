<?php
// keyGenerator.php

class KeyGenerator
{
    /**
     * Generate a secure application key
     * 
     * @param int $length Length of the key in bytes (32 bytes = 256 bits recommended)
     * @return string The generated key
     */
    public static function generateKey($length = 32)
    {
        try {
            // Generate random bytes and convert to hex
            $randomBytes = random_bytes($length);
            return bin2hex($randomBytes);
        } catch (Exception $e) {
            error_log("Error generating app key: " . $e->getMessage());
            throw new Exception("Could not generate secure key");
        }
    }

    /**
     * Create or update .env file with new APP_KEY
     */
    public static function updateEnvFile()
    {
        try {
            $envFile = dirname(__DIR__) . '/.env';
            $newKey = self::generateKey();

            if (file_exists($envFile)) {
                // Read current .env content
                $currentEnv = file_get_contents($envFile);

                if (strpos($currentEnv, 'APP_KEY=') !== false) {
                    // Replace existing APP_KEY
                    $newEnv = preg_replace(
                        '/APP_KEY=.*/',
                        'APP_KEY=' . $newKey,
                        $currentEnv
                    );
                } else {
                    // Add APP_KEY if it doesn't exist
                    $newEnv = $currentEnv . "\nAPP_KEY=" . $newKey;
                }
            } else {
                // Create new .env file with APP_KEY
                $newEnv = "APP_KEY=" . $newKey;
            }

            // Save the .env file
            if (file_put_contents($envFile, $newEnv) === false) {
                throw new Exception("Could not write to .env file");
            }

            return $newKey;
        } catch (Exception $e) {
            error_log("Error updating .env file: " . $e->getMessage());
            throw new Exception("Could not update environment file");
        }
    }
}

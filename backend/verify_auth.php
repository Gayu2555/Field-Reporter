<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;


$data = json_decode(file_get_contents("php://input"), true);
$code = $data['code'] ?? '';
$token = $data['token'] ?? '';

function getSecretFromToken($token) {}

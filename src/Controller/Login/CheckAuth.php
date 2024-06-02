<?php
session_start();

$origin = 'http://localhost:5173';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

$response = [];

try {
    if (isset($_SESSION['user'])) {
        $response = [
            'status' => 'success',
            'user' => [
                'mail' => htmlspecialchars($_SESSION['user']['mail'], ENT_QUOTES, 'UTF-8'),
                'firstname' => htmlspecialchars($_SESSION['user']['firstname'], ENT_QUOTES, 'UTF-8'),
                'lastname' => htmlspecialchars($_SESSION['user']['lastname'], ENT_QUOTES, 'UTF-8'),
            ]
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Not authenticated'
        ];
    }
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'An error occurred while checking authentication'
    ];
}

echo json_encode($response);
?>

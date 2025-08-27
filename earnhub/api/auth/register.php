<?php
require_once '../../config/env.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }
    
    $requiredFields = ['name', 'email', 'phone', 'password'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
            exit;
        }
    }
    
    $registrationData = [
        'name' => trim($input['name']),
        'email' => trim(strtolower($input['email'])),
        'phone' => trim($input['phone']),
        'password' => $input['password'],
        'referral_code' => trim($input['referral_code'] ?? '')
    ];
    
    $result = auth()->register($registrationData);
    
    http_response_code($result['success'] ? 201 : 400);
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Registration API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
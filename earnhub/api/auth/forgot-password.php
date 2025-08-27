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
    
    if (!$input || !isset($input['email']) || empty(trim($input['email']))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email address is required']);
        exit;
    }
    
    $email = trim(strtolower($input['email']));
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid email address is required']);
        exit;
    }
    
    $result = auth()->requestPasswordReset($email);
    
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Forgot password API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
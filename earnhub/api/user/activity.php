<?php
require_once '../../config/env.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

auth()->requireAuth();

try {
    $userId = $_SESSION['user_id'];
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 10;
    
    // Get recent point history
    $activities = fetchAll("
        SELECT 
            points,
            type,
            description,
            created_at
        FROM point_history 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ", [$userId, $limit]);
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
    
} catch (Exception $e) {
    error_log("Activity API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load activity']);
}
?>
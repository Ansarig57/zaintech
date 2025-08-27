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
    $today = date('Y-m-d');
    
    // Check daily watch limit
    $todayWatched = getRecordCount('watch_logs', 'user_id = ? AND watch_date = ? AND completed = 1', [$userId, $today]);
    $watchLimit = getAdminSetting('watch_daily_limit', 20);
    
    if ($todayWatched >= $watchLimit) {
        echo json_encode([
            'success' => false, 
            'message' => 'Daily ad watching limit reached. Come back tomorrow!'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Ready to watch ad',
        'todayWatched' => $todayWatched,
        'watchLimit' => $watchLimit,
        'remaining' => $watchLimit - $todayWatched
    ]);
    
} catch (Exception $e) {
    error_log("Watch check API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Check failed. Please try again.']);
}
?>
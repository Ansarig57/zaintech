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

auth()->requireAuth();

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'];
    $today = date('Y-m-d');
    
    $adType = $input['ad_type'] ?? 'general';
    $duration = (int)($input['duration'] ?? 30);
    
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
    
    // Get reward amount
    $reward = getAdminSetting('watch_ad_reward', 5);
    
    beginTransaction();
    
    try {
        // Record the watch log
        $watchId = insertRecord('watch_logs', [
            'user_id' => $userId,
            'ad_type' => $adType,
            'reward_amount' => $reward,
            'duration' => $duration,
            'completed' => 1,
            'watch_date' => $today
        ]);
        
        // Add points to user's history (trigger will update user points)
        insertRecord('point_history', [
            'user_id' => $userId,
            'points' => $reward,
            'type' => 'watch_ad',
            'description' => "Watched {$adType} ad: {$reward} points",
            'reference_id' => $watchId
        ]);
        
        commit();
        
        // Get updated user points
        $userPoints = fetchOne("SELECT points FROM users WHERE id = ?", [$userId]);
        $newTodayWatched = $todayWatched + 1;
        
        echo json_encode([
            'success' => true,
            'message' => "Great! You earned {$reward} points!",
            'reward' => $reward,
            'newPoints' => (int)$userPoints['points'],
            'todayWatched' => $newTodayWatched,
            'watchLimit' => $watchLimit
        ]);
        
    } catch (Exception $e) {
        rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Watch claim API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Claim failed. Please try again.']);
}
?>
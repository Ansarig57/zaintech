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
    $userId = $_SESSION['user_id'];
    $today = date('Y-m-d');
    
    // Check daily spin limit
    $todaySpins = getRecordCount('spins', 'user_id = ? AND spin_date = ?', [$userId, $today]);
    $spinLimit = getAdminSetting('spin_daily_limit', 5);
    
    if ($todaySpins >= $spinLimit) {
        echo json_encode([
            'success' => false, 
            'message' => 'Daily spin limit reached. Come back tomorrow!'
        ]);
        exit;
    }
    
    // Generate random reward
    $minReward = getAdminSetting('spin_min_reward', 10);
    $maxReward = getAdminSetting('spin_max_reward', 500);
    $reward = rand($minReward, $maxReward);
    
    beginTransaction();
    
    try {
        // Record the spin
        $spinId = insertRecord('spins', [
            'user_id' => $userId,
            'reward_amount' => $reward,
            'reward_type' => 'points',
            'spin_date' => $today
        ]);
        
        // Add points to user's history (trigger will update user points)
        insertRecord('point_history', [
            'user_id' => $userId,
            'points' => $reward,
            'type' => 'spin',
            'description' => "Spin wheel reward: {$reward} points",
            'reference_id' => $spinId
        ]);
        
        commit();
        
        // Get updated user points
        $userPoints = fetchOne("SELECT points FROM users WHERE id = ?", [$userId]);
        $newTodaySpins = $todaySpins + 1;
        
        echo json_encode([
            'success' => true,
            'message' => "Congratulations! You won {$reward} points!",
            'reward' => $reward,
            'newPoints' => (int)$userPoints['points'],
            'todaySpins' => $newTodaySpins,
            'spinLimit' => $spinLimit
        ]);
        
    } catch (Exception $e) {
        rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Spin API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Spin failed. Please try again.']);
}
?>
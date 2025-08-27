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
    
    // Get user data
    $user = fetchOne("SELECT last_login_bonus, login_streak FROM users WHERE id = ?", [$userId]);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Check if already claimed today
    if ($user['last_login_bonus'] === $today) {
        echo json_encode(['success' => false, 'message' => 'Daily bonus already claimed today']);
        exit;
    }
    
    $dailyBonus = getAdminSetting('daily_login_bonus', 50);
    $streakBonusDays = getAdminSetting('streak_bonus_days', 7);
    $streakBonusPoints = getAdminSetting('streak_bonus_points', 5000);
    
    // Calculate streak
    $newStreak = 1;
    $bonusPoints = $dailyBonus;
    $streakBonus = false;
    
    if ($user['last_login_bonus']) {
        $lastBonus = new DateTime($user['last_login_bonus']);
        $todayDate = new DateTime($today);
        $daysDiff = $todayDate->diff($lastBonus)->days;
        
        if ($daysDiff === 1) {
            // Consecutive day
            $newStreak = $user['login_streak'] + 1;
        } else if ($daysDiff > 1) {
            // Streak broken
            $newStreak = 1;
        }
    }
    
    // Check for streak bonus
    if ($newStreak > 0 && $newStreak % $streakBonusDays === 0) {
        $bonusPoints += $streakBonusPoints;
        $streakBonus = true;
    }
    
    beginTransaction();
    
    try {
        // Update user streak and last bonus date
        updateRecord('users', [
            'last_login_bonus' => $today,
            'login_streak' => $newStreak
        ], 'id = ?', [$userId]);
        
        // Add points to history
        $description = "Daily login bonus: {$dailyBonus} points";
        if ($streakBonus) {
            $description .= " + {$streakBonusPoints} streak bonus ({$newStreak} days)";
        }
        
        insertRecord('point_history', [
            'user_id' => $userId,
            'points' => $bonusPoints,
            'type' => 'daily_bonus',
            'description' => $description
        ]);
        
        commit();
        
        // Get updated user points
        $userPoints = fetchOne("SELECT points FROM users WHERE id = ?", [$userId]);
        
        $message = "Daily bonus claimed: +{$dailyBonus} points!";
        if ($streakBonus) {
            $message .= " Streak bonus: +{$streakBonusPoints} points! ({$newStreak} day streak)";
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'dailyBonus' => $dailyBonus,
            'streakBonus' => $streakBonus ? $streakBonusPoints : 0,
            'newStreak' => $newStreak,
            'totalBonus' => $bonusPoints,
            'newPoints' => (int)$userPoints['points']
        ]);
        
    } catch (Exception $e) {
        rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Daily bonus API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to claim bonus. Please try again.']);
}
?>
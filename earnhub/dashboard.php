<?php
require_once 'config/env.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!auth()->isLoggedIn()) {
    header('Location: index.html');
    exit;
}

$user = auth()->getCurrentUser();
if (!$user) {
    header('Location: index.html');
    exit;
}

// Get user statistics
$stats = fetchOne("
    SELECT 
        u.points,
        u.total_earned,
        u.referral_earnings,
        u.login_streak,
        u.last_login_bonus,
        COUNT(DISTINCT s.id) as total_spins,
        COUNT(DISTINCT w.id) as total_ads_watched,
        COUNT(DISTINCT r.id) as total_referrals
    FROM users u
    LEFT JOIN spins s ON u.id = s.user_id
    LEFT JOIN watch_logs w ON u.id = w.user_id AND w.completed = 1
    LEFT JOIN users r ON u.id = r.referred_by
    WHERE u.id = ?
    GROUP BY u.id
", [$user['id']]);

// Get today's activity
$today = date('Y-m-d');
$todaySpins = getRecordCount('spins', 'user_id = ? AND spin_date = ?', [$user['id'], $today]);
$todayWatched = getRecordCount('watch_logs', 'user_id = ? AND watch_date = ? AND completed = 1', [$user['id'], $today]);

// Get recent notifications
$notifications = fetchAll("
    SELECT * FROM notifications 
    WHERE (user_id = ? OR user_id IS NULL) 
    AND (expires_at IS NULL OR expires_at > NOW())
    ORDER BY created_at DESC 
    LIMIT 5
", [$user['id']]);

// Check daily login bonus
$canClaimBonus = !$user['last_login_bonus'] || $user['last_login_bonus'] !== $today;

// Get conversion rate
$pointsToRate = getAdminSetting('points_to_pkr_rate', 1000);
$pkrAmount = round($user['points'] / $pointsToRate, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Earnhub</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="<?php echo $user['theme']; ?>" id="app-body">
    <!-- Navigation -->
    <nav class="dashboard-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <h1>Earnhub</h1>
                <span class="brand-tagline">Dashboard</span>
            </div>
            <div class="nav-menu">
                <a href="#dashboard" class="nav-link active" data-section="dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="#spin" class="nav-link" data-section="spin">
                    <i class="fas fa-dharmachakra"></i> Spin & Earn
                </a>
                <a href="#watch" class="nav-link" data-section="watch">
                    <i class="fas fa-video"></i> Watch Ads
                </a>
                <a href="#wallet" class="nav-link" data-section="wallet">
                    <i class="fas fa-wallet"></i> Wallet
                </a>
                <a href="#referral" class="nav-link" data-section="referral">
                    <i class="fas fa-users"></i> Referrals
                </a>
                <a href="#leaderboard" class="nav-link" data-section="leaderboard">
                    <i class="fas fa-trophy"></i> Leaderboard
                </a>
                <a href="#profile" class="nav-link" data-section="profile">
                    <i class="fas fa-user"></i> Profile
                </a>
            </div>
            <div class="nav-actions">
                <div class="points-display">
                    <i class="fas fa-coins"></i>
                    <span id="user-points"><?php echo number_format($user['points']); ?></span>
                    <small>Points</small>
                </div>
                <div class="user-menu">
                    <img src="<?php echo $user['avatar'] ?: 'assets/images/default-avatar.png'; ?>" alt="Avatar" class="user-avatar">
                    <div class="dropdown-menu">
                        <a href="#profile" class="dropdown-item" data-section="profile">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="#settings" class="dropdown-item" data-section="settings">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <a href="#" class="dropdown-item" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
                <div class="mobile-toggle" id="mobile-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="dashboard-main">
        <!-- Dashboard Section -->
        <section id="dashboard-section" class="dashboard-section active">
            <div class="container">
                <div class="welcome-banner">
                    <div class="welcome-content">
                        <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 👋</h1>
                        <p>Ready to earn some points today?</p>
                        <?php if ($canClaimBonus): ?>
                        <button class="btn btn-primary" onclick="claimDailyBonus()">
                            <i class="fas fa-gift"></i> Claim Daily Bonus (+<?php echo DAILY_LOGIN_BONUS; ?> Points)
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="welcome-stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-fire"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo $user['login_streak']; ?></div>
                                <div class="stat-label">Day Streak</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo number_format($stats['points']); ?></div>
                            <div class="stat-label">Total Points</div>
                            <div class="stat-subtext">≈ ₹<?php echo number_format($pkrAmount, 2); ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value">₹<?php echo number_format($stats['total_earned'], 2); ?></div>
                            <div class="stat-label">Total Earned</div>
                            <div class="stat-subtext">Lifetime earnings</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dharmachakra"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo $todaySpins; ?>/<?php echo getAdminSetting('spin_daily_limit', 5); ?></div>
                            <div class="stat-label">Spins Today</div>
                            <div class="stat-subtext">Daily limit</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo $todayWatched; ?>/<?php echo getAdminSetting('watch_daily_limit', 20); ?></div>
                            <div class="stat-label">Ads Watched</div>
                            <div class="stat-subtext">Today</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="actions-grid">
                        <div class="action-card" onclick="switchSection('spin')">
                            <div class="action-icon">
                                <i class="fas fa-dharmachakra"></i>
                            </div>
                            <h3>Spin Wheel</h3>
                            <p>Win up to <?php echo getAdminSetting('spin_max_reward', 500); ?> points per spin</p>
                            <div class="action-status">
                                <?php if ($todaySpins < getAdminSetting('spin_daily_limit', 5)): ?>
                                <span class="status-available">Available</span>
                                <?php else: ?>
                                <span class="status-completed">Daily limit reached</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="action-card" onclick="switchSection('watch')">
                            <div class="action-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <h3>Watch Ads</h3>
                            <p>Earn <?php echo getAdminSetting('watch_ad_reward', 5); ?> points per ad</p>
                            <div class="action-status">
                                <?php if ($todayWatched < getAdminSetting('watch_daily_limit', 20)): ?>
                                <span class="status-available">Available</span>
                                <?php else: ?>
                                <span class="status-completed">Daily limit reached</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="action-card" onclick="switchSection('referral')">
                            <div class="action-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>Invite Friends</h3>
                            <p>Earn <?php echo getAdminSetting('referral_signup_bonus', 100); ?> points per referral</p>
                            <div class="action-status">
                                <span class="status-available">Always available</span>
                            </div>
                        </div>
                        <div class="action-card" onclick="switchSection('wallet')">
                            <div class="action-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <h3>Withdraw</h3>
                            <p>Convert points to real money</p>
                            <div class="action-status">
                                <?php if ($pkrAmount >= getAdminSetting('min_withdrawal_pkr', 200)): ?>
                                <span class="status-available">Available</span>
                                <?php else: ?>
                                <span class="status-unavailable">Min ₹<?php echo getAdminSetting('min_withdrawal_pkr', 200); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="recent-activity">
                    <h2>Recent Activity</h2>
                    <div class="activity-list" id="activity-list">
                        <!-- Activity items will be loaded here -->
                    </div>
                </div>

                <!-- Notifications -->
                <?php if (!empty($notifications)): ?>
                <div class="notifications-section">
                    <h2>Notifications</h2>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?php echo $notification['type']; ?>">
                            <div class="notification-icon">
                                <i class="fas fa-<?php echo $notification['type'] === 'success' ? 'check-circle' : ($notification['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle'); ?>"></i>
                            </div>
                            <div class="notification-content">
                                <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <small><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Spin Section -->
        <section id="spin-section" class="dashboard-section">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-dharmachakra"></i> Spin & Earn</h1>
                    <p>Spin the wheel and win amazing rewards!</p>
                </div>

                <div class="spin-container">
                    <div class="spin-wheel-wrapper">
                        <div class="spin-wheel" id="spin-wheel">
                            <div class="wheel-pointer"></div>
                            <canvas id="wheel-canvas" width="400" height="400"></canvas>
                        </div>
                        <button class="spin-button" id="spin-button" onclick="spinWheel()">
                            <i class="fas fa-play"></i>
                            <span>SPIN</span>
                        </button>
                    </div>
                    
                    <div class="spin-info">
                        <div class="spin-stats">
                            <div class="spin-stat">
                                <div class="stat-value"><?php echo $todaySpins; ?>/<?php echo getAdminSetting('spin_daily_limit', 5); ?></div>
                                <div class="stat-label">Spins Today</div>
                            </div>
                            <div class="spin-stat">
                                <div class="stat-value"><?php echo getAdminSetting('spin_min_reward', 10); ?>-<?php echo getAdminSetting('spin_max_reward', 500); ?></div>
                                <div class="stat-label">Points Range</div>
                            </div>
                            <div class="spin-stat">
                                <div class="stat-value"><?php echo $stats['total_spins']; ?></div>
                                <div class="stat-label">Total Spins</div>
                            </div>
                        </div>

                        <div class="spin-rules">
                            <h3>How it works</h3>
                            <ul>
                                <li>Each spin costs nothing - it's completely free!</li>
                                <li>Win between <?php echo getAdminSetting('spin_min_reward', 10); ?> to <?php echo getAdminSetting('spin_max_reward', 500); ?> points per spin</li>
                                <li>You can spin up to <?php echo getAdminSetting('spin_daily_limit', 5); ?> times per day</li>
                                <li>Points are added to your wallet immediately</li>
                                <li>Come back tomorrow for more spins!</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Watch Ads Section -->
        <section id="watch-section" class="dashboard-section">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-video"></i> Watch Ads & Earn</h1>
                    <p>Watch engaging video ads and earn points</p>
                </div>

                <div class="watch-container">
                    <div class="watch-player">
                        <div class="ad-placeholder" id="ad-placeholder">
                            <div class="ad-content">
                                <i class="fas fa-video fa-4x"></i>
                                <h3>Ready to watch an ad?</h3>
                                <p>Click the button below to start watching and earn points</p>
                                <button class="btn btn-primary btn-lg" onclick="startWatchingAd()">
                                    <i class="fas fa-play"></i> Start Watching
                                </button>
                            </div>
                        </div>
                        <div class="ad-timer" id="ad-timer" style="display: none;">
                            <div class="timer-circle">
                                <div class="timer-text">
                                    <span id="timer-seconds"><?php echo getAdminSetting('watch_ad_duration', 30); ?></span>
                                    <small>seconds</small>
                                </div>
                            </div>
                            <button class="btn btn-success btn-lg" id="claim-button" style="display: none;" onclick="claimAdReward()">
                                <i class="fas fa-gift"></i> Claim +<?php echo getAdminSetting('watch_ad_reward', 5); ?> Points
                            </button>
                        </div>
                    </div>

                    <div class="watch-stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo $todayWatched; ?>/<?php echo getAdminSetting('watch_daily_limit', 20); ?></div>
                                <div class="stat-label">Ads Today</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo getAdminSetting('watch_ad_reward', 5); ?></div>
                                <div class="stat-label">Points per Ad</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo getAdminSetting('watch_ad_duration', 30); ?>s</div>
                                <div class="stat-label">Ad Duration</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo $stats['total_ads_watched']; ?></div>
                                <div class="stat-label">Total Watched</div>
                            </div>
                        </div>
                    </div>

                    <div class="ad-types">
                        <h3>Available Ad Types</h3>
                        <div class="ad-types-grid">
                            <div class="ad-type-card">
                                <div class="ad-type-icon">
                                    <i class="fab fa-facebook"></i>
                                </div>
                                <h4>Facebook Ads</h4>
                                <p>Watch promoted content from Facebook</p>
                                <div class="ad-reward">+<?php echo getAdminSetting('watch_ad_reward', 5); ?> Points</div>
                            </div>
                            <div class="ad-type-card">
                                <div class="ad-type-icon">
                                    <i class="fab fa-tiktok"></i>
                                </div>
                                <h4>TikTok Promos</h4>
                                <p>Discover trending TikTok content</p>
                                <div class="ad-reward">+<?php echo getAdminSetting('watch_ad_reward', 5); ?> Points</div>
                            </div>
                            <div class="ad-type-card">
                                <div class="ad-type-icon">
                                    <i class="fab fa-youtube"></i>
                                </div>
                                <h4>YouTube Videos</h4>
                                <p>Watch engaging YouTube content</p>
                                <div class="ad-reward">+<?php echo getAdminSetting('watch_ad_reward', 5); ?> Points</div>
                            </div>
                            <div class="ad-type-card">
                                <div class="ad-type-icon">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <h4>General Ads</h4>
                                <p>Various promotional content</p>
                                <div class="ad-reward">+<?php echo getAdminSetting('watch_ad_reward', 5); ?> Points</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Other sections will be added in the next parts... -->
    </main>

    <!-- Scripts -->
    <script>
        // User data for JavaScript
        window.userData = {
            id: <?php echo $user['id']; ?>,
            name: '<?php echo addslashes($user['name']); ?>',
            points: <?php echo $user['points']; ?>,
            todaySpins: <?php echo $todaySpins; ?>,
            todayWatched: <?php echo $todayWatched; ?>,
            spinLimit: <?php echo getAdminSetting('spin_daily_limit', 5); ?>,
            watchLimit: <?php echo getAdminSetting('watch_daily_limit', 20); ?>,
            adDuration: <?php echo getAdminSetting('watch_ad_duration', 30); ?>,
            adReward: <?php echo getAdminSetting('watch_ad_reward', 5); ?>
        };
    </script>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/spin-wheel.js"></script>
    <script src="assets/js/watch-ads.js"></script>
</body>
</html>
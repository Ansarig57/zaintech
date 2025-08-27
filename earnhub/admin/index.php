<?php
require_once '../config/env.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Show login form
    include 'login.php';
    exit;
}

// Get admin info
$adminId = $_SESSION['admin_id'];
$adminRole = $_SESSION['admin_role'];

// Get dashboard stats
$totalUsers = getRecordCount('users', 'status = ?', ['active']);
$totalEarnings = fetchOne("SELECT SUM(total_earned) as total FROM users")['total'] ?? 0;
$pendingWithdrawals = getRecordCount('withdrawals', 'status = ?', ['pending']);
$todaySpins = getRecordCount('spins', 'spin_date = ?', [date('Y-m-d')]);
$todayWatched = getRecordCount('watch_logs', 'watch_date = ? AND completed = 1', [date('Y-m-d')]);

// Get recent activities
$recentUsers = fetchAll("SELECT name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recentWithdrawals = fetchAll("
    SELECT w.*, u.name, u.email 
    FROM withdrawals w 
    JOIN users u ON w.user_id = u.id 
    ORDER BY w.created_at DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Earnhub</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dark-theme admin-panel" id="app-body">
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <h1>Earnhub</h1>
                <span class="brand-tagline">Admin Panel</span>
            </div>
            <div class="nav-menu">
                <a href="#dashboard" class="nav-link active" data-section="dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="#users" class="nav-link" data-section="users">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="#withdrawals" class="nav-link" data-section="withdrawals">
                    <i class="fas fa-money-bill-wave"></i> Withdrawals
                </a>
                <a href="#settings" class="nav-link" data-section="settings">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="#notifications" class="nav-link" data-section="notifications">
                    <i class="fas fa-bell"></i> Notifications
                </a>
                <a href="#analytics" class="nav-link" data-section="analytics">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
            </div>
            <div class="nav-actions">
                <div class="admin-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <small><?php echo ucfirst($adminRole); ?></small>
                </div>
                <button class="btn btn-outline" onclick="adminLogout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
                <div class="mobile-toggle" id="mobile-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Dashboard Section -->
        <section id="dashboard-section" class="admin-section active">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-home"></i> Admin Dashboard</h1>
                    <p>Overview of your Earnhub platform</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
                            <div class="stat-label">Total Users</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i> +12% this month
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value">₹<?php echo number_format($totalEarnings, 2); ?></div>
                            <div class="stat-label">Total Earnings</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i> +8% this month
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo $pendingWithdrawals; ?></div>
                            <div class="stat-label">Pending Withdrawals</div>
                            <div class="stat-trend">
                                <i class="fas fa-exclamation-triangle"></i> Needs attention
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dharmachakra"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo number_format($todaySpins); ?></div>
                            <div class="stat-label">Today's Spins</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i> Active users
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="actions-grid">
                        <div class="action-card" onclick="switchAdminSection('users')">
                            <div class="action-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h3>Manage Users</h3>
                            <p>View, edit, or manage user accounts</p>
                        </div>
                        <div class="action-card" onclick="switchAdminSection('withdrawals')">
                            <div class="action-icon">
                                <i class="fas fa-money-check-alt"></i>
                            </div>
                            <h3>Process Withdrawals</h3>
                            <p>Review and approve withdrawal requests</p>
                            <?php if ($pendingWithdrawals > 0): ?>
                            <div class="action-badge"><?php echo $pendingWithdrawals; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="action-card" onclick="switchAdminSection('settings')">
                            <div class="action-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <h3>Site Settings</h3>
                            <p>Configure platform settings and limits</p>
                        </div>
                        <div class="action-card" onclick="switchAdminSection('notifications')">
                            <div class="action-icon">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <h3>Send Notification</h3>
                            <p>Broadcast messages to all users</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="admin-content-grid">
                    <div class="content-card">
                        <h3>Recent Users</h3>
                        <div class="recent-list">
                            <?php foreach ($recentUsers as $user): ?>
                            <div class="recent-item">
                                <div class="recent-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="recent-info">
                                    <div class="recent-title"><?php echo htmlspecialchars($user['name']); ?></div>
                                    <div class="recent-description"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                                <div class="recent-time">
                                    <?php echo date('M j', strtotime($user['created_at'])); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="content-card">
                        <h3>Recent Withdrawals</h3>
                        <div class="recent-list">
                            <?php foreach ($recentWithdrawals as $withdrawal): ?>
                            <div class="recent-item">
                                <div class="recent-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="recent-info">
                                    <div class="recent-title">₹<?php echo number_format($withdrawal['amount_pkr'], 2); ?></div>
                                    <div class="recent-description"><?php echo htmlspecialchars($withdrawal['name']); ?></div>
                                </div>
                                <div class="recent-status">
                                    <span class="status-badge <?php echo $withdrawal['status']; ?>">
                                        <?php echo ucfirst($withdrawal['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <h3>User Growth</h3>
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <h3>Daily Activity</h3>
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <!-- Users Section -->
        <section id="users-section" class="admin-section">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-users"></i> User Management</h1>
                    <p>Manage all user accounts and their data</p>
                </div>

                <div class="users-controls">
                    <div class="search-filters">
                        <div class="search-box">
                            <input type="text" placeholder="Search users..." id="user-search">
                            <i class="fas fa-search"></i>
                        </div>
                        <select id="user-filter">
                            <option value="all">All Users</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="banned">Banned</option>
                        </select>
                        <select id="user-sort">
                            <option value="created_at">Newest First</option>
                            <option value="points">Highest Points</option>
                            <option value="total_earned">Highest Earnings</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" onclick="exportUsers()">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>

                <div class="users-table-container">
                    <table class="admin-table" id="users-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Points</th>
                                <th>Total Earned</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <!-- Users will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <div class="pagination" id="users-pagination">
                    <!-- Pagination will be loaded here -->
                </div>
            </div>
        </section>

        <!-- Withdrawals Section -->
        <section id="withdrawals-section" class="admin-section">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-money-bill-wave"></i> Withdrawal Management</h1>
                    <p>Review and process withdrawal requests</p>
                </div>

                <div class="withdrawals-controls">
                    <div class="status-tabs">
                        <button class="tab-button active" onclick="filterWithdrawals('pending')">
                            Pending <span class="badge"><?php echo $pendingWithdrawals; ?></span>
                        </button>
                        <button class="tab-button" onclick="filterWithdrawals('approved')">
                            Approved
                        </button>
                        <button class="tab-button" onclick="filterWithdrawals('rejected')">
                            Rejected
                        </button>
                        <button class="tab-button" onclick="filterWithdrawals('paid')">
                            Paid
                        </button>
                    </div>
                </div>

                <div class="withdrawals-table-container">
                    <table class="admin-table" id="withdrawals-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="withdrawals-table-body">
                            <!-- Withdrawals will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Settings Section -->
        <section id="settings-section" class="admin-section">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-cog"></i> Site Settings</h1>
                    <p>Configure platform settings and parameters</p>
                </div>

                <div class="settings-tabs">
                    <button class="tab-button active" onclick="switchSettingsTab('general')">
                        <i class="fas fa-cog"></i> General
                    </button>
                    <button class="tab-button" onclick="switchSettingsTab('rewards')">
                        <i class="fas fa-gift"></i> Rewards
                    </button>
                    <button class="tab-button" onclick="switchSettingsTab('limits')">
                        <i class="fas fa-shield-alt"></i> Limits
                    </button>
                    <button class="tab-button" onclick="switchSettingsTab('themes')">
                        <i class="fas fa-palette"></i> Themes
                    </button>
                </div>

                <div class="settings-content">
                    <!-- General Settings -->
                    <div id="general-tab" class="settings-tab active">
                        <form id="general-settings-form" class="settings-form">
                            <div class="form-group">
                                <label for="site-title">Site Title</label>
                                <input type="text" id="site-title" name="site_title" value="Earnhub - Premium Earning Platform">
                            </div>
                            <div class="form-group">
                                <label for="points-rate">Points to PKR Rate</label>
                                <input type="number" id="points-rate" name="points_to_pkr_rate" value="1000">
                                <small>How many points equal 1 PKR</small>
                            </div>
                            <div class="form-group">
                                <label for="min-withdrawal">Minimum Withdrawal (PKR)</label>
                                <input type="number" id="min-withdrawal" name="min_withdrawal_pkr" value="200">
                            </div>
                            <div class="form-group">
                                <label for="contact-email">Contact Email</label>
                                <input type="email" id="contact-email" name="contact_email" value="support@earnhub.com">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save General Settings
                            </button>
                        </form>
                    </div>

                    <!-- Rewards Settings -->
                    <div id="rewards-tab" class="settings-tab">
                        <form id="rewards-settings-form" class="settings-form">
                            <h3>Spin Wheel Settings</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="spin-min">Min Spin Reward</label>
                                    <input type="number" id="spin-min" name="spin_min_reward" value="10">
                                </div>
                                <div class="form-group">
                                    <label for="spin-max">Max Spin Reward</label>
                                    <input type="number" id="spin-max" name="spin_max_reward" value="500">
                                </div>
                                <div class="form-group">
                                    <label for="spin-limit">Daily Spin Limit</label>
                                    <input type="number" id="spin-limit" name="spin_daily_limit" value="5">
                                </div>
                            </div>

                            <h3>Watch Ads Settings</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="watch-reward">Watch Ad Reward</label>
                                    <input type="number" id="watch-reward" name="watch_ad_reward" value="5">
                                </div>
                                <div class="form-group">
                                    <label for="watch-duration">Ad Duration (seconds)</label>
                                    <input type="number" id="watch-duration" name="watch_ad_duration" value="30">
                                </div>
                                <div class="form-group">
                                    <label for="watch-limit">Daily Watch Limit</label>
                                    <input type="number" id="watch-limit" name="watch_daily_limit" value="20">
                                </div>
                            </div>

                            <h3>Daily Bonus Settings</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="daily-bonus">Daily Login Bonus</label>
                                    <input type="number" id="daily-bonus" name="daily_login_bonus" value="50">
                                </div>
                                <div class="form-group">
                                    <label for="streak-days">Streak Bonus Days</label>
                                    <input type="number" id="streak-days" name="streak_bonus_days" value="7">
                                </div>
                                <div class="form-group">
                                    <label for="streak-bonus">Streak Bonus Points</label>
                                    <input type="number" id="streak-bonus" name="streak_bonus_points" value="5000">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Reward Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Other admin sections will be loaded dynamically -->
    </main>

    <!-- Scripts -->
    <script>
        // Admin data for JavaScript
        window.adminData = {
            role: '<?php echo $adminRole; ?>',
            totalUsers: <?php echo $totalUsers; ?>,
            totalEarnings: <?php echo $totalEarnings; ?>,
            pendingWithdrawals: <?php echo $pendingWithdrawals; ?>
        };
    </script>
    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
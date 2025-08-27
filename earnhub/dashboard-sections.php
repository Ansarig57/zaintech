        <!-- Wallet Section -->
        <section id="wallet-section" class="dashboard-section">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-wallet"></i> My Wallet</h1>
                    <p>Manage your earnings and withdrawals</p>
                </div>

                <div class="wallet-overview">
                    <div class="wallet-card main-balance">
                        <div class="wallet-header">
                            <h3>Current Balance</h3>
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="wallet-amount">
                            <span class="points"><?php echo number_format($user['points']); ?></span>
                            <small>Points</small>
                        </div>
                        <div class="wallet-conversion">
                            <i class="fas fa-exchange-alt"></i>
                            <span>≈ ₹<?php echo number_format($pkrAmount, 2); ?> PKR</span>
                        </div>
                        <div class="conversion-rate">
                            <small>1000 Points = ₹1 PKR</small>
                        </div>
                    </div>

                    <div class="wallet-stats">
                        <div class="wallet-stat">
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value">₹<?php echo number_format($stats['total_earned'], 2); ?></div>
                                <div class="stat-label">Total Earned</div>
                            </div>
                        </div>
                        <div class="wallet-stat">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value">₹<?php echo number_format($stats['referral_earnings'], 2); ?></div>
                                <div class="stat-label">Referral Earnings</div>
                            </div>
                        </div>
                        <div class="wallet-stat">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value">₹0.00</div>
                                <div class="stat-label">Withdrawn</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wallet-actions">
                    <div class="action-section">
                        <h3>Quick Actions</h3>
                        <div class="action-buttons">
                            <?php if ($pkrAmount >= getAdminSetting('min_withdrawal_pkr', 200)): ?>
                            <button class="btn btn-primary btn-lg" onclick="openWithdrawModal()">
                                <i class="fas fa-money-bill-wave"></i>
                                Withdraw Money
                            </button>
                            <?php else: ?>
                            <button class="btn btn-outline btn-lg" disabled>
                                <i class="fas fa-lock"></i>
                                Minimum ₹<?php echo getAdminSetting('min_withdrawal_pkr', 200); ?> Required
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-outline btn-lg" onclick="switchSection('referral')">
                                <i class="fas fa-users"></i>
                                Invite Friends
                            </button>
                        </div>
                    </div>
                </div>

                <div class="withdrawal-history">
                    <h3>Withdrawal History</h3>
                    <div class="history-table" id="withdrawal-history">
                        <!-- Withdrawal history will be loaded here -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Referral Section -->
        <section id="referral-section" class="dashboard-section">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-users"></i> Referral Program</h1>
                    <p>Invite friends and earn lifetime commissions</p>
                </div>

                <div class="referral-overview">
                    <div class="referral-card">
                        <div class="referral-header">
                            <h3>Your Referral Code</h3>
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="referral-code">
                            <span id="referral-code"><?php echo $user['referral_code']; ?></span>
                            <button class="btn btn-outline" onclick="copyReferralCode()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <div class="referral-link">
                            <input type="text" id="referral-link" value="<?php echo SITE_URL; ?>?ref=<?php echo $user['referral_code']; ?>" readonly>
                            <button class="btn btn-primary" onclick="copyReferralLink()">
                                <i class="fas fa-link"></i> Copy Link
                            </button>
                        </div>
                    </div>

                    <div class="referral-stats">
                        <div class="referral-stat">
                            <div class="stat-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo $stats['total_referrals']; ?></div>
                                <div class="stat-label">Total Referrals</div>
                            </div>
                        </div>
                        <div class="referral-stat">
                            <div class="stat-icon">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value">₹<?php echo number_format($stats['referral_earnings'], 2); ?></div>
                                <div class="stat-label">Referral Earnings</div>
                            </div>
                        </div>
                        <div class="referral-stat">
                            <div class="stat-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo getAdminSetting('referral_commission_rate', 10); ?>%</div>
                                <div class="stat-label">Commission Rate</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="referral-benefits">
                    <h3>Referral Benefits</h3>
                    <div class="benefits-grid">
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h4>Signup Bonus</h4>
                            <p>Earn <strong><?php echo getAdminSetting('referral_signup_bonus', 100); ?> points</strong> for each friend who joins</p>
                        </div>
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <h4>Lifetime Commission</h4>
                            <p>Get <strong><?php echo getAdminSetting('referral_commission_rate', 10); ?>%</strong> of their earnings forever</p>
                        </div>
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <h4>Bonus Rewards</h4>
                            <p>Special bonuses for top referrers each month</p>
                        </div>
                    </div>
                </div>

                <div class="share-options">
                    <h3>Share Your Code</h3>
                    <div class="share-buttons">
                        <button class="btn btn-social whatsapp" onclick="shareOnWhatsApp()">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </button>
                        <button class="btn btn-social facebook" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook"></i> Facebook
                        </button>
                        <button class="btn btn-social twitter" onclick="shareOnTwitter()">
                            <i class="fab fa-twitter"></i> Twitter
                        </button>
                        <button class="btn btn-social telegram" onclick="shareOnTelegram()">
                            <i class="fab fa-telegram"></i> Telegram
                        </button>
                    </div>
                </div>

                <div class="referral-list">
                    <h3>Your Referrals</h3>
                    <div class="referrals-table" id="referrals-list">
                        <!-- Referrals will be loaded here -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Leaderboard Section -->
        <section id="leaderboard-section" class="dashboard-section">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-trophy"></i> Leaderboard</h1>
                    <p>Compete with other users and win amazing prizes</p>
                </div>

                <div class="leaderboard-tabs">
                    <button class="tab-button active" onclick="switchLeaderboard('weekly')">
                        <i class="fas fa-calendar-week"></i> Weekly
                    </button>
                    <button class="tab-button" onclick="switchLeaderboard('monthly')">
                        <i class="fas fa-calendar-alt"></i> Monthly
                    </button>
                </div>

                <div class="leaderboard-content">
                    <div class="leaderboard-prizes">
                        <h3>This Week's Prizes</h3>
                        <div class="prizes-grid">
                            <div class="prize-card first">
                                <div class="prize-rank">🥇</div>
                                <div class="prize-amount">₹<?php echo getAdminSetting('jackpot_prize_amount', 10000); ?></div>
                                <div class="prize-label">1st Place</div>
                            </div>
                            <div class="prize-card second">
                                <div class="prize-rank">🥈</div>
                                <div class="prize-amount">₹5,000</div>
                                <div class="prize-label">2nd Place</div>
                            </div>
                            <div class="prize-card third">
                                <div class="prize-rank">🥉</div>
                                <div class="prize-amount">₹2,500</div>
                                <div class="prize-label">3rd Place</div>
                            </div>
                        </div>
                    </div>

                    <div class="leaderboard-list" id="leaderboard-list">
                        <!-- Leaderboard will be loaded here -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Profile Section -->
        <section id="profile-section" class="dashboard-section">
            <div class="container">
                <div class="section-header">
                    <h1><i class="fas fa-user"></i> My Profile</h1>
                    <p>Manage your account settings and preferences</p>
                </div>

                <div class="profile-content">
                    <div class="profile-sidebar">
                        <div class="profile-avatar">
                            <img src="<?php echo $user['avatar'] ?: 'assets/images/default-avatar.png'; ?>" alt="Avatar" id="current-avatar">
                            <div class="avatar-overlay">
                                <i class="fas fa-camera"></i>
                                <span>Change Photo</span>
                            </div>
                            <input type="file" id="avatar-upload" accept="image/*" style="display: none;">
                        </div>
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                            <div class="profile-stats">
                                <div class="profile-stat">
                                    <span class="stat-value"><?php echo $user['login_streak']; ?></span>
                                    <span class="stat-label">Day Streak</span>
                                </div>
                                <div class="profile-stat">
                                    <span class="stat-value"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                                    <span class="stat-label">Joined</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-main">
                        <div class="profile-tabs">
                            <button class="tab-button active" onclick="switchProfileTab('details')">
                                <i class="fas fa-user"></i> Personal Details
                            </button>
                            <button class="tab-button" onclick="switchProfileTab('security')">
                                <i class="fas fa-shield-alt"></i> Security
                            </button>
                            <button class="tab-button" onclick="switchProfileTab('preferences')">
                                <i class="fas fa-cog"></i> Preferences
                            </button>
                        </div>

                        <div class="profile-tab-content">
                            <!-- Personal Details Tab -->
                            <div id="details-tab" class="tab-content active">
                                <form id="profile-form" class="profile-form">
                                    <div class="form-group">
                                        <label for="profile-name">Full Name</label>
                                        <input type="text" id="profile-name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="profile-email">Email Address</label>
                                        <input type="email" id="profile-email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        <?php if (!$user['email_verified']): ?>
                                        <small class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Email not verified
                                            <a href="#" onclick="resendVerification()">Resend verification</a>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group">
                                        <label for="profile-phone">Phone Number</label>
                                        <input type="tel" id="profile-phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </form>
                            </div>

                            <!-- Security Tab -->
                            <div id="security-tab" class="tab-content">
                                <form id="password-form" class="profile-form">
                                    <div class="form-group">
                                        <label for="current-password">Current Password</label>
                                        <input type="password" id="current-password" name="current_password" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="new-password">New Password</label>
                                        <input type="password" id="new-password" name="new_password" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm-password">Confirm New Password</label>
                                        <input type="password" id="confirm-password" name="confirm_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key"></i> Update Password
                                    </button>
                                </form>

                                <div class="security-info">
                                    <h4>Account Security</h4>
                                    <div class="security-items">
                                        <div class="security-item">
                                            <i class="fas fa-shield-check text-success"></i>
                                            <span>Two-factor authentication</span>
                                            <small>Coming soon</small>
                                        </div>
                                        <div class="security-item">
                                            <i class="fas fa-mobile-alt text-success"></i>
                                            <span>SMS verification</span>
                                            <small>Active</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preferences Tab -->
                            <div id="preferences-tab" class="tab-content">
                                <form id="preferences-form" class="profile-form">
                                    <div class="form-group">
                                        <label for="theme-select">Theme</label>
                                        <select id="theme-select" name="theme">
                                            <option value="dark-theme" <?php echo $user['theme'] === 'dark-theme' ? 'selected' : ''; ?>>Dark</option>
                                            <option value="neon-theme" <?php echo $user['theme'] === 'neon-theme' ? 'selected' : ''; ?>>Neon</option>
                                            <option value="golden-theme" <?php echo $user['theme'] === 'golden-theme' ? 'selected' : ''; ?>>Golden</option>
                                            <option value="blue-theme" <?php echo $user['theme'] === 'blue-theme' ? 'selected' : ''; ?>>Blue</option>
                                            <option value="red-theme" <?php echo $user['theme'] === 'red-theme' ? 'selected' : ''; ?>>Red</option>
                                            <option value="green-theme" <?php echo $user['theme'] === 'green-theme' ? 'selected' : ''; ?>>Green</option>
                                            <option value="purple-theme" <?php echo $user['theme'] === 'purple-theme' ? 'selected' : ''; ?>>Purple</option>
                                            <option value="gradient-theme" <?php echo $user['theme'] === 'gradient-theme' ? 'selected' : ''; ?>>Gradient</option>
                                            <option value="pastel-theme" <?php echo $user['theme'] === 'pastel-theme' ? 'selected' : ''; ?>>Pastel</option>
                                            <option value="classic-theme" <?php echo $user['theme'] === 'classic-theme' ? 'selected' : ''; ?>>Classic</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="checkbox">
                                            <input type="checkbox" name="email_notifications" checked>
                                            <span class="checkmark"></span>
                                            Email notifications
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label class="checkbox">
                                            <input type="checkbox" name="push_notifications" checked>
                                            <span class="checkmark"></span>
                                            Push notifications
                                        </label>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Preferences
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
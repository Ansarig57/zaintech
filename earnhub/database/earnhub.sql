-- ===== EARNHUB DATABASE SCHEMA =====
-- MySQL Database Schema for Earnhub Premium Earning Platform

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `earnhub` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `earnhub`;

-- ===== USERS TABLE =====
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) UNIQUE NOT NULL,
  `phone` varchar(20) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `total_earned` decimal(10,2) DEFAULT 0.00,
  `referral_code` varchar(20) UNIQUE NOT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `referral_earnings` decimal(10,2) DEFAULT 0.00,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verification_token` varchar(100) DEFAULT NULL,
  `password_reset_token` varchar(100) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `login_streak` int(11) DEFAULT 0,
  `last_login_bonus` date DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `theme` varchar(50) DEFAULT 'dark-theme',
  `status` enum('active','suspended','banned') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_referral_code` (`referral_code`),
  KEY `idx_referred_by` (`referred_by`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`referred_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== SPINS TABLE =====
CREATE TABLE `spins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reward_amount` int(11) NOT NULL,
  `reward_type` enum('points','bonus') DEFAULT 'points',
  `spin_date` date NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_spin_date` (`spin_date`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== WATCH LOGS TABLE =====
CREATE TABLE `watch_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ad_type` enum('facebook','tiktok','youtube','general') DEFAULT 'general',
  `reward_amount` int(11) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in seconds',
  `completed` tinyint(1) DEFAULT 0,
  `watch_date` date NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_watch_date` (`watch_date`),
  KEY `idx_completed` (`completed`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== WITHDRAWALS TABLE =====
CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount_points` int(11) NOT NULL,
  `amount_pkr` decimal(10,2) NOT NULL,
  `payment_method` enum('bank','easypaisa','jazzcash','paypal') NOT NULL,
  `payment_details` text NOT NULL COMMENT 'JSON encoded payment details',
  `status` enum('pending','approved','rejected','paid') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_processed_by` (`processed_by`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== NOTIFICATIONS TABLE =====
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL for broadcast to all users',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','promotion') DEFAULT 'info',
  `read_status` tinyint(1) DEFAULT 0,
  `action_url` varchar(255) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_read_status` (`read_status`),
  KEY `idx_expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== SUPPORT TICKETS TABLE =====
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `category` enum('general','payment','technical','account','complaint') DEFAULT 'general',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `message` text NOT NULL,
  `admin_reply` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_assigned_to` (`assigned_to`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== JACKPOT ENTRIES TABLE =====
CREATE TABLE `jackpot_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `week_start` date NOT NULL,
  `entries` int(11) DEFAULT 1,
  `points_earned` int(11) DEFAULT 0,
  `rank_position` int(11) DEFAULT NULL,
  `is_winner` tinyint(1) DEFAULT 0,
  `prize_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_week` (`user_id`, `week_start`),
  KEY `idx_week_start` (`week_start`),
  KEY `idx_is_winner` (`is_winner`),
  KEY `idx_rank_position` (`rank_position`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== POINT HISTORY TABLE =====
CREATE TABLE `point_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `points` int(11) NOT NULL COMMENT 'Positive for earn, negative for spend',
  `type` enum('spin','watch_ad','daily_bonus','referral','withdrawal','jackpot','admin_adjust') NOT NULL,
  `description` varchar(255) NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'Reference to related table record',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== ADMIN SETTINGS TABLE =====
CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) UNIQUE NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','integer','decimal','boolean','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_setting_key` (`setting_key`),
  FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== ADMIN USERS TABLE =====
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) UNIQUE NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `permissions` text DEFAULT NULL COMMENT 'JSON encoded permissions',
  `last_login` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== DEVICE TRACKING TABLE =====
CREATE TABLE `device_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `device_id` varchar(100) NOT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `is_blocked` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_device_id` (`device_id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_is_blocked` (`is_blocked`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== INSERT DEFAULT DATA =====

-- Insert default admin user
INSERT INTO `admin_users` (`username`, `password`, `full_name`, `email`, `role`, `permissions`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@earnhub.com', 'super_admin', '["all"]');

-- Insert default admin settings
INSERT INTO `admin_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('points_to_pkr_rate', '1000', 'integer', 'Points required for 1 PKR'),
('min_withdrawal_pkr', '200', 'integer', 'Minimum withdrawal amount in PKR'),
('spin_min_reward', '10', 'integer', 'Minimum spin reward points'),
('spin_max_reward', '500', 'integer', 'Maximum spin reward points'),
('spin_daily_limit', '5', 'integer', 'Daily spin limit per user'),
('watch_ad_reward', '5', 'integer', 'Points per ad watched'),
('watch_ad_duration', '30', 'integer', 'Ad duration in seconds'),
('watch_daily_limit', '20', 'integer', 'Daily ad watch limit'),
('daily_login_bonus', '50', 'integer', 'Daily login bonus points'),
('streak_bonus_days', '7', 'integer', 'Days required for streak bonus'),
('streak_bonus_points', '5000', 'integer', 'Bonus points for completing streak'),
('referral_signup_bonus', '100', 'integer', 'Bonus for successful referral signup'),
('referral_commission_rate', '10', 'integer', 'Commission percentage for referrer'),
('jackpot_entry_threshold', '1000', 'integer', 'Weekly points required for jackpot entry'),
('jackpot_prize_amount', '10000', 'decimal', 'Weekly jackpot prize in PKR'),
('site_maintenance', 'false', 'boolean', 'Site maintenance mode'),
('site_title', 'Earnhub - Premium Earning Platform', 'string', 'Site title'),
('site_description', 'Earn real money through spins, ads, and daily bonuses', 'string', 'Site description'),
('contact_email', 'support@earnhub.com', 'string', 'Contact email address'),
('facebook_page', 'https://facebook.com/earnhub', 'string', 'Facebook page URL'),
('telegram_group', 'https://t.me/earnhub', 'string', 'Telegram group URL');

-- Insert sample notification
INSERT INTO `notifications` (`title`, `message`, `type`, `created_by`) VALUES
('Welcome to Earnhub!', 'Start earning real money today with our premium features. Spin the wheel, watch ads, and claim daily bonuses!', 'info', 1);

-- ===== INDEXES FOR PERFORMANCE =====
ALTER TABLE `users` ADD INDEX `idx_points` (`points`);
ALTER TABLE `users` ADD INDEX `idx_total_earned` (`total_earned`);
ALTER TABLE `users` ADD INDEX `idx_created_at` (`created_at`);

ALTER TABLE `point_history` ADD INDEX `idx_points` (`points`);
ALTER TABLE `spins` ADD INDEX `idx_reward_amount` (`reward_amount`);
ALTER TABLE `watch_logs` ADD INDEX `idx_reward_amount` (`reward_amount`);

-- ===== TRIGGERS =====

-- Trigger to update user points when point_history is inserted
DELIMITER //
CREATE TRIGGER `update_user_points` AFTER INSERT ON `point_history`
FOR EACH ROW BEGIN
    UPDATE `users` 
    SET `points` = `points` + NEW.points,
        `total_earned` = CASE 
            WHEN NEW.points > 0 THEN `total_earned` + (NEW.points / 1000)
            ELSE `total_earned`
        END
    WHERE `id` = NEW.user_id;
END//

-- Trigger to generate referral code for new users
CREATE TRIGGER `generate_referral_code` BEFORE INSERT ON `users`
FOR EACH ROW BEGIN
    IF NEW.referral_code IS NULL OR NEW.referral_code = '' THEN
        SET NEW.referral_code = CONCAT('EH', UPPER(SUBSTRING(MD5(CONCAT(NEW.email, NOW())), 1, 6)));
    END IF;
END//

DELIMITER ;

-- ===== VIEWS FOR ANALYTICS =====

-- View for user statistics
CREATE VIEW `user_stats` AS
SELECT 
    u.id,
    u.name,
    u.email,
    u.points,
    u.total_earned,
    u.referral_earnings,
    u.login_streak,
    u.created_at,
    COUNT(DISTINCT s.id) as total_spins,
    COUNT(DISTINCT w.id) as total_ads_watched,
    COUNT(DISTINCT r.id) as total_referrals,
    COALESCE(SUM(CASE WHEN ph.points > 0 THEN ph.points ELSE 0 END), 0) as total_points_earned,
    COALESCE(SUM(CASE WHEN ph.points < 0 THEN ABS(ph.points) ELSE 0 END), 0) as total_points_spent
FROM users u
LEFT JOIN spins s ON u.id = s.user_id
LEFT JOIN watch_logs w ON u.id = w.user_id AND w.completed = 1
LEFT JOIN users r ON u.id = r.referred_by
LEFT JOIN point_history ph ON u.id = ph.user_id
WHERE u.status = 'active'
GROUP BY u.id;

-- View for leaderboard
CREATE VIEW `leaderboard_weekly` AS
SELECT 
    u.id,
    u.name,
    u.avatar,
    COALESCE(SUM(ph.points), 0) as week_points,
    ROW_NUMBER() OVER (ORDER BY COALESCE(SUM(ph.points), 0) DESC) as rank_position
FROM users u
LEFT JOIN point_history ph ON u.id = ph.user_id 
    AND ph.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
    AND ph.points > 0
WHERE u.status = 'active'
GROUP BY u.id, u.name, u.avatar
ORDER BY week_points DESC
LIMIT 100;

CREATE VIEW `leaderboard_monthly` AS
SELECT 
    u.id,
    u.name,
    u.avatar,
    COALESCE(SUM(ph.points), 0) as month_points,
    ROW_NUMBER() OVER (ORDER BY COALESCE(SUM(ph.points), 0) DESC) as rank_position
FROM users u
LEFT JOIN point_history ph ON u.id = ph.user_id 
    AND ph.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
    AND ph.points > 0
WHERE u.status = 'active'
GROUP BY u.id, u.name, u.avatar
ORDER BY month_points DESC
LIMIT 100;

COMMIT;
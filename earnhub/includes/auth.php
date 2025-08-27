<?php
// ===== AUTHENTICATION CLASS =====

class Auth {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // ===== USER REGISTRATION =====
    public function register($data) {
        try {
            // Validate input
            $errors = $this->validateRegistrationData($data);
            if (!empty($errors)) {
                return ['success' => false, 'message' => implode(', ', $errors)];
            }
            
            // Check if user already exists
            if ($this->userExists($data['email'], $data['phone'])) {
                return ['success' => false, 'message' => 'User with this email or phone already exists'];
            }
            
            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Generate referral code
            $referralCode = $this->generateReferralCode($data['email']);
            
            // Check referral code if provided
            $referredBy = null;
            if (!empty($data['referral_code'])) {
                $referrer = fetchOne(
                    "SELECT id FROM users WHERE referral_code = ? AND status = 'active'",
                    [$data['referral_code']]
                );
                if ($referrer) {
                    $referredBy = $referrer['id'];
                }
            }
            
            beginTransaction();
            
            // Insert user
            $userId = insertRecord('users', [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $hashedPassword,
                'referral_code' => $referralCode,
                'referred_by' => $referredBy,
                'email_verification_token' => $verificationToken,
                'device_id' => $this->getDeviceId(),
                'ip_address' => $this->getClientIP()
            ]);
            
            // Add welcome bonus
            $this->addPointHistory($userId, REFERRAL_SIGNUP_BONUS, 'referral', 'Welcome bonus for joining Earnhub');
            
            // Add referral bonus to referrer if applicable
            if ($referredBy) {
                $this->addPointHistory($referredBy, REFERRAL_SIGNUP_BONUS, 'referral', 'Referral bonus for inviting new user');
            }
            
            commit();
            
            // Send verification email
            $this->sendVerificationEmail($data['email'], $data['name'], $verificationToken);
            
            return ['success' => true, 'message' => 'Registration successful! Please check your email for verification.'];
            
        } catch (Exception $e) {
            rollback();
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    // ===== USER LOGIN =====
    public function login($email, $password, $remember = false) {
        try {
            // Check for too many failed attempts
            if ($this->isAccountLocked($email)) {
                return ['success' => false, 'message' => 'Account temporarily locked due to too many failed attempts. Try again later.'];
            }
            
            // Find user
            $user = fetchOne(
                "SELECT * FROM users WHERE (email = ? OR phone = ?) AND status = 'active'",
                [$email, $email]
            );
            
            if (!$user || !password_verify($password, $user['password'])) {
                $this->recordFailedLogin($email);
                return ['success' => false, 'message' => 'Invalid email/phone or password'];
            }
            
            // Check if email is verified
            if (!$user['email_verified']) {
                return ['success' => false, 'message' => 'Please verify your email address before logging in'];
            }
            
            // Clear failed login attempts
            $this->clearFailedLogins($email);
            
            // Update last login
            updateRecord('users', [
                'last_login' => date('Y-m-d H:i:s'),
                'last_activity' => date('Y-m-d H:i:s'),
                'device_id' => $this->getDeviceId(),
                'ip_address' => $this->getClientIP()
            ], 'id = ?', [$user['id']]);
            
            // Set session
            $this->setUserSession($user, $remember);
            
            // Track device
            $this->trackDevice($user['id']);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $this->sanitizeUserData($user)
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    // ===== LOGOUT =====
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Update last activity
            updateRecord('users', [
                'last_activity' => date('Y-m-d H:i:s')
            ], 'id = ?', [$_SESSION['user_id']]);
        }
        
        session_destroy();
        
        // Clear remember me cookie if exists
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
        
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    // ===== EMAIL VERIFICATION =====
    public function verifyEmail($token) {
        try {
            $user = fetchOne(
                "SELECT id FROM users WHERE email_verification_token = ? AND email_verified = 0",
                [$token]
            );
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid or expired verification token'];
            }
            
            updateRecord('users', [
                'email_verified' => 1,
                'email_verification_token' => null
            ], 'id = ?', [$user['id']]);
            
            return ['success' => true, 'message' => 'Email verified successfully! You can now login.'];
            
        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Verification failed. Please try again.'];
        }
    }
    
    // ===== PASSWORD RESET =====
    public function requestPasswordReset($email) {
        try {
            $user = fetchOne("SELECT id, name FROM users WHERE email = ?", [$email]);
            
            if (!$user) {
                return ['success' => false, 'message' => 'No account found with this email address'];
            }
            
            $resetToken = bin2hex(random_bytes(32));
            $resetExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            updateRecord('users', [
                'password_reset_token' => $resetToken,
                'password_reset_expires' => $resetExpires
            ], 'id = ?', [$user['id']]);
            
            $this->sendPasswordResetEmail($email, $user['name'], $resetToken);
            
            return ['success' => true, 'message' => 'Password reset link sent to your email'];
            
        } catch (Exception $e) {
            error_log("Password reset request error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send reset link. Please try again.'];
        }
    }
    
    public function resetPassword($token, $newPassword) {
        try {
            $user = fetchOne(
                "SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()",
                [$token]
            );
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid or expired reset token'];
            }
            
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'];
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            updateRecord('users', [
                'password' => $hashedPassword,
                'password_reset_token' => null,
                'password_reset_expires' => null
            ], 'id = ?', [$user['id']]);
            
            return ['success' => true, 'message' => 'Password reset successfully! You can now login.'];
            
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Password reset failed. Please try again.'];
        }
    }
    
    // ===== SESSION MANAGEMENT =====
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $user = fetchOne(
            "SELECT * FROM users WHERE id = ? AND status = 'active'",
            [$_SESSION['user_id']]
        );
        
        return $user ? $this->sanitizeUserData($user) : null;
    }
    
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit;
        }
    }
    
    public function requireAdmin() {
        if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role'])) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin authentication required']);
            exit;
        }
    }
    
    // ===== ADMIN LOGIN =====
    public function adminLogin($username, $password) {
        try {
            if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
                $_SESSION['admin_id'] = 1;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_role'] = 'super_admin';
                
                return ['success' => true, 'message' => 'Admin login successful'];
            }
            
            $admin = fetchOne(
                "SELECT * FROM admin_users WHERE username = ? AND status = 'active'",
                [$username]
            );
            
            if (!$admin || !password_verify($password, $admin['password'])) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            
            updateRecord('admin_users', [
                'last_login' => date('Y-m-d H:i:s')
            ], 'id = ?', [$admin['id']]);
            
            return ['success' => true, 'message' => 'Admin login successful'];
            
        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    // ===== HELPER METHODS =====
    private function validateRegistrationData($data) {
        $errors = [];
        
        if (empty($data['name']) || strlen($data['name']) < 2) {
            $errors[] = 'Name must be at least 2 characters long';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required';
        }
        
        if (empty($data['phone']) || !preg_match('/^[\+]?[1-9][\d]{0,15}$/', str_replace(' ', '', $data['phone']))) {
            $errors[] = 'Valid phone number is required';
        }
        
        if (empty($data['password']) || strlen($data['password']) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long';
        }
        
        return $errors;
    }
    
    private function userExists($email, $phone) {
        return recordExists('users', 'email = ? OR phone = ?', [$email, $phone]);
    }
    
    private function generateReferralCode($email) {
        do {
            $code = 'EH' . strtoupper(substr(md5($email . time() . rand()), 0, 6));
        } while (recordExists('users', 'referral_code = ?', [$code]));
        
        return $code;
    }
    
    private function setUserSession($user, $remember) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = 'user';
        
        if ($remember) {
            $rememberToken = bin2hex(random_bytes(32));
            setcookie('remember_token', $rememberToken, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            
            updateRecord('users', [
                'remember_token' => password_hash($rememberToken, PASSWORD_DEFAULT)
            ], 'id = ?', [$user['id']]);
        }
    }
    
    private function sanitizeUserData($user) {
        unset($user['password'], $user['email_verification_token'], $user['password_reset_token'], $user['remember_token']);
        return $user;
    }
    
    private function getDeviceId() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $this->getClientIP();
        return md5($userAgent . $ip);
    }
    
    private function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    private function trackDevice($userId) {
        $deviceId = $this->getDeviceId();
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $existing = fetchOne(
            "SELECT id FROM device_tracking WHERE user_id = ? AND device_id = ?",
            [$userId, $deviceId]
        );
        
        if ($existing) {
            updateRecord('device_tracking', [
                'last_activity' => date('Y-m-d H:i:s'),
                'ip_address' => $ip
            ], 'id = ?', [$existing['id']]);
        } else {
            insertRecord('device_tracking', [
                'user_id' => $userId,
                'device_id' => $deviceId,
                'browser' => $this->parseBrowser($userAgent),
                'ip_address' => $ip,
                'last_activity' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    private function parseBrowser($userAgent) {
        $browsers = [
            'Chrome' => '/Chrome\/[\d\.]+/i',
            'Firefox' => '/Firefox\/[\d\.]+/i',
            'Safari' => '/Safari\/[\d\.]+/i',
            'Edge' => '/Edge\/[\d\.]+/i',
            'Opera' => '/Opera\/[\d\.]+/i'
        ];
        
        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $browser;
            }
        }
        
        return 'Unknown';
    }
    
    private function isAccountLocked($email) {
        $cacheKey = 'failed_logins_' . md5($email);
        $attempts = $_SESSION[$cacheKey] ?? 0;
        return $attempts >= MAX_LOGIN_ATTEMPTS;
    }
    
    private function recordFailedLogin($email) {
        $cacheKey = 'failed_logins_' . md5($email);
        $_SESSION[$cacheKey] = ($_SESSION[$cacheKey] ?? 0) + 1;
        $_SESSION[$cacheKey . '_time'] = time();
    }
    
    private function clearFailedLogins($email) {
        $cacheKey = 'failed_logins_' . md5($email);
        unset($_SESSION[$cacheKey], $_SESSION[$cacheKey . '_time']);
    }
    
    private function addPointHistory($userId, $points, $type, $description, $referenceId = null) {
        return insertRecord('point_history', [
            'user_id' => $userId,
            'points' => $points,
            'type' => $type,
            'description' => $description,
            'reference_id' => $referenceId
        ]);
    }
    
    private function sendVerificationEmail($email, $name, $token) {
        $subject = "Verify your Earnhub account";
        $verifyUrl = SITE_URL . "/verify-email.php?token=" . $token;
        
        $message = "
        <h2>Welcome to Earnhub, {$name}!</h2>
        <p>Thank you for joining our premium earning platform. Please verify your email address by clicking the link below:</p>
        <p><a href='{$verifyUrl}' style='background: #00d4ff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email Address</a></p>
        <p>If the button doesn't work, copy and paste this link into your browser:</p>
        <p>{$verifyUrl}</p>
        <p>This link will expire in 24 hours.</p>
        <p>Best regards,<br>Earnhub Team</p>
        ";
        
        $this->sendEmail($email, $subject, $message);
    }
    
    private function sendPasswordResetEmail($email, $name, $token) {
        $subject = "Reset your Earnhub password";
        $resetUrl = SITE_URL . "/reset-password.php?token=" . $token;
        
        $message = "
        <h2>Password Reset Request</h2>
        <p>Hi {$name},</p>
        <p>You requested to reset your password for your Earnhub account. Click the link below to set a new password:</p>
        <p><a href='{$resetUrl}' style='background: #00d4ff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
        <p>If the button doesn't work, copy and paste this link into your browser:</p>
        <p>{$resetUrl}</p>
        <p>This link will expire in 1 hour.</p>
        <p>If you didn't request this password reset, please ignore this email.</p>
        <p>Best regards,<br>Earnhub Team</p>
        ";
        
        $this->sendEmail($email, $subject, $message);
    }
    
    private function sendEmail($to, $subject, $message) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . SITE_NAME . ' <' . SITE_EMAIL . '>',
            'Reply-To: ' . SITE_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}

// Global auth instance
function auth() {
    return Auth::getInstance();
}

?>
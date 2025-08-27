<?php
require_once 'config/env.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

$token = $_GET['token'] ?? '';
$message = '';
$success = false;
$showForm = !empty($token);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($token)) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($newPassword) || strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        $message = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Passwords do not match.';
    } else {
        $result = auth()->resetPassword($token, $newPassword);
        $message = $result['message'];
        $success = $result['success'];
        if ($success) {
            $showForm = false;
        }
    }
} elseif (empty($token)) {
    $message = 'Invalid reset link.';
    $showForm = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Earnhub</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .reset-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            padding: 2rem;
        }

        .reset-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: var(--shadow-lg);
        }

        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .reset-header h1 {
            color: var(--text-primary);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .reset-header p {
            color: var(--text-secondary);
        }

        .reset-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin: 0 auto 1.5rem;
        }

        .message {
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .message.success {
            background: rgba(46, 213, 115, 0.1);
            border: 1px solid var(--success-color);
            color: var(--success-color);
        }

        .message.error {
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
        }

        .reset-footer {
            text-align: center;
            margin-top: 2rem;
        }

        .reset-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .reset-footer a:hover {
            color: var(--accent-color);
        }
    </style>
</head>
<body class="dark-theme" id="app-body">
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <div class="reset-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h1>Reset Password</h1>
                <p>Enter your new password below</p>
            </div>

            <?php if (!empty($message)): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <?php if ($showForm): ?>
            <form method="POST" class="auth-form">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input type="password" id="new-password" name="new_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                    <i class="fas fa-lock form-icon"></i>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('new-password')"></i>
                </div>
                
                <div class="form-group">
                    <label for="confirm-password">Confirm New Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                    <i class="fas fa-lock form-icon"></i>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm-password')"></i>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </form>
            <?php endif; ?>

            <?php if ($success): ?>
            <div style="text-align: center;">
                <a href="index.html" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login Now
                </a>
            </div>
            <?php endif; ?>

            <div class="reset-footer">
                <p>Remember your password? <a href="index.html">Login here</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                toggle.classList.remove('fa-eye');
                toggle.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                toggle.classList.remove('fa-eye-slash');
                toggle.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
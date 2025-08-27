<?php
require_once 'config/env.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

$token = $_GET['token'] ?? '';
$message = '';
$success = false;

if (!empty($token)) {
    $result = auth()->verifyEmail($token);
    $message = $result['message'];
    $success = $result['success'];
} else {
    $message = 'Invalid verification link.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Earnhub</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .verification-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            padding: 2rem;
        }

        .verification-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            text-align: center;
            box-shadow: var(--shadow-lg);
        }

        .verification-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 2rem;
        }

        .verification-icon.success {
            background: linear-gradient(45deg, var(--success-color), var(--accent-color));
        }

        .verification-icon.error {
            background: linear-gradient(45deg, var(--danger-color), var(--secondary-color));
        }

        .verification-title {
            color: var(--text-primary);
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .verification-message {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .verification-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
    </style>
</head>
<body class="dark-theme" id="app-body">
    <div class="verification-container">
        <div class="verification-card">
            <div class="verification-icon <?php echo $success ? 'success' : 'error'; ?>">
                <i class="fas fa-<?php echo $success ? 'check' : 'times'; ?>"></i>
            </div>
            
            <h1 class="verification-title">
                <?php echo $success ? 'Email Verified!' : 'Verification Failed'; ?>
            </h1>
            
            <p class="verification-message">
                <?php echo htmlspecialchars($message); ?>
            </p>
            
            <div class="verification-actions">
                <?php if ($success): ?>
                    <a href="index.html" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login Now
                    </a>
                <?php else: ?>
                    <a href="index.html" class="btn btn-outline">
                        <i class="fas fa-home"></i> Go Home
                    </a>
                    <a href="#" onclick="resendVerification()" class="btn btn-primary">
                        <i class="fas fa-envelope"></i> Resend Email
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        function resendVerification() {
            app.showSuccess('Resend verification functionality coming soon!');
        }
    </script>
</body>
</html>
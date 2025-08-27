<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Earnhub</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            padding: 2rem;
        }

        .login-container {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: var(--shadow-lg);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: var(--text-primary);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-secondary);
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .login-form {
            margin-bottom: 2rem;
        }

        .login-footer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .demo-credentials {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-top: 1.5rem;
        }

        .demo-credentials h4 {
            color: var(--text-primary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .demo-credentials p {
            color: var(--text-secondary);
            font-size: 0.8rem;
            margin: 0.25rem 0;
        }
    </style>
</head>
<body class="dark-theme" id="app-body">
    <div class="admin-login">
        <div class="login-container">
            <div class="login-header">
                <h1>Earnhub Admin</h1>
                <p>Secure administrative access</p>
                <div class="admin-badge">
                    <i class="fas fa-shield-alt"></i>
                    Admin Panel
                </div>
            </div>

            <form id="admin-login-form" class="login-form">
                <div class="form-group">
                    <label for="admin-username">Username</label>
                    <input type="text" id="admin-username" name="username" required>
                    <i class="fas fa-user form-icon"></i>
                </div>
                <div class="form-group">
                    <label for="admin-password">Password</label>
                    <input type="password" id="admin-password" name="password" required>
                    <i class="fas fa-lock form-icon"></i>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('admin-password')"></i>
                </div>
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
                </button>
            </form>

            <div class="demo-credentials">
                <h4><i class="fas fa-info-circle"></i> Demo Credentials</h4>
                <p><strong>Username:</strong> admin</p>
                <p><strong>Password:</strong> 123ZAIN</p>
            </div>

            <div class="login-footer">
                <p>&copy; 2024 Earnhub. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
    <script>
        document.getElementById('admin-login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const loginData = {
                username: formData.get('username'),
                password: formData.get('password')
            };

            try {
                app.showLoading(e.target);
                
                const response = await fetch('../api/admin/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(loginData)
                });

                const result = await response.json();
                
                if (result.success) {
                    app.showSuccess('Login successful! Redirecting...');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    app.showError(result.message || 'Login failed. Please try again.');
                }
            } catch (error) {
                console.error('Admin login error:', error);
                app.showError('Connection error. Please try again.');
            } finally {
                app.hideLoading(e.target);
            }
        });

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
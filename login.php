<?php
require_once 'config.php';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController = new AuthController();
    $result = $authController->login($_POST['username'], $_POST['password']);
    
    if ($result['success']) {
        // Redirect based on role
        $role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';
        if ($role === 'admin') {
            redirect('dashboard.php');
        } else {
            redirect('dashboard.php'); // Both Admin and Employee go to same dashboard
        }
    } else {
        $error_message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Cairo', 'Tajawal', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            direction: rtl;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 450px;
            margin: 0 auto;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .login-header i {
            position: relative;
            z-index: 1;
            font-size: 3.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .login-header h3 {
            position: relative;
            z-index: 1;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            position: relative;
            z-index: 1;
            margin: 0;
            opacity: 0.9;
        }
        
        .login-body {
            padding: 2.5rem;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 0.875rem 1.25rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
            transform: translateY(-2px);
        }
        
        .input-group-text {
            border-radius: 12px 0 0 12px;
            border: 2px solid #e9ecef;
            border-left: none;
            background: #f8f9fa;
            color: #667eea;
        }
        
        .form-control:focus + .input-group-text,
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem;
            font-weight: 600;
            width: 100%;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .demo-credentials {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 1.25rem;
            margin-top: 1.5rem;
            border: 2px dashed #dee2e6;
        }
        
        .demo-credentials strong {
            color: #667eea;
            display: block;
            margin-bottom: 0.75rem;
        }
        
        .demo-credentials span {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-user-shield"></i>
                        <h3>مرحباً بعودتك</h3>
                        <p>Welcome Back</p>
                        <p class="mb-0" style="font-size: 0.9rem;">سجل الدخول إلى حسابك | Sign in to your account</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">اسم المستخدم | Username</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                           required autocomplete="username">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">كلمة المرور | Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           required autocomplete="current-password">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                <span>تسجيل الدخول | Sign In</span>
                            </button>
                        </form>
                        
                        <div class="demo-credentials">
                            <strong>بيانات تجريبية | Demo Credentials:</strong>
                            <span>Admin: <b>admin</b> / <b>admin123</b></span>
                            <span class="d-block mt-2">Employee: <b>employee</b> / <b>emp123</b></span>
                            <small class="text-muted d-block mt-2">كلمات المرور غير مشفرة للاختبار | Passwords are plain text for testing</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Form submission with AJAX (optional enhancement)
            $('#loginForm').on('submit', function(e) {
                // Let form submit normally for now
                // Can be enhanced with AJAX later if needed
            });
        });
    </script>
</body>
</html>

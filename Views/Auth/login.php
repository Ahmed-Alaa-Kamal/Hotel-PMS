<?php
require_once __DIR__ . '/../../Controllers/AuthController.php';
require_once __DIR__ . '/../../Models/User.php';

$auth = new AuthController();
$errMsg = '';

if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        $user = new User();
        $user->username = $_POST['username'];
        $user->password = $_POST['password'];

        if ($auth->login($user)) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $role = $_SESSION['userRole'] ?? 'guest';
            switch ($role) {
                case 'manager':
                case 'admin':
                    header('Location: ../Manager/index.php');
                    break;
                case 'frontdesk':
                    header('Location: ../Frontdesk/index.php');
                    break;
                case 'hksup':
                    header('Location: ../HkSup/index.php');
                    break;
                case 'hk':
                    header('Location: ../Hk/index.php');
                    break;
                case 'guest':
                default:
                    header('Location: ../Guest/index.php');
                    break;
            }
            exit;
        } else {
            $errMsg = $auth->error ?? 'Invalid username or password.';
        }
    } else {
        $errMsg = 'Please fill all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Hotel PMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #2c3e50, #3498db); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { max-width: 400px; width: 100%; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .card-body { padding: 2rem; }
        .btn-primary { background: #3498db; border: none; padding: 12px; font-weight: bold; }
        .btn-primary:hover { background: #2980b9; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="card">
        <div class="card-body">
            <h3 class="text-center mb-3"><i class="bi bi-building"></i> Hotel PMS</h3>
            <a href="../public/index.php" class="btn btn-outline-secondary w-100 mb-3"><i class="bi bi-arrow-left"></i> Back to Homepage</a>

                        <?php if ($errMsg != ''): ?>
                            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errMsg) ?></div>
                        <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right me-2"></i>Login</button>
            </form>
            <p class="mt-3 text-center mb-0">Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </div>
</div>
</body>
</html>
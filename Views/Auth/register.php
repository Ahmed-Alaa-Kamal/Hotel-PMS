<?php
require_once __DIR__ . '/../../Controllers/AuthController.php';
require_once __DIR__ . '/../../Models/User.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['userId'])) {
    header('Location: /hotel_pms/Views/Guest/index.php');
    exit;
}

$auth = new AuthController();
$errMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    $user->username  = $_POST['username'] ?? '';
    $user->full_name = $_POST['full_name'] ?? '';
    $user->email     = $_POST['email'] ?? '';
    $user->phone     = $_POST['phone'] ?? '';
    $user->password  = $_POST['password'] ?? '';
    $user->role      = 'guest';

    if (empty($user->username) || empty($user->full_name) || empty($user->email) || empty($user->password)) {
        $errMsg = 'All fields are required.';
    }else if(!preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $user->username)){
        $errMsg="Username must start with a letter and contain only letters and numbers";
    }else if(!preg_match('/^[a-zA-Z\s]+$/', $user->full_name)){
        $errMsg="Full Name must contain only letters";
    }else if(!preg_match('/^[a-zA-Z]/', $user->email)){
        $errMsg="Email must start with a letter";
    }else if(!preg_match('/^[0-9]+$/', $user->phone)){
        $errMsg="Phone must contain only numbers";
    } else {
        $result = $auth->register($user);
        if ($result) {
            header('Location: /hotel_pms/Views/Guest/index.php');
            exit;
        } else {
            $errMsg = $auth->error ?? 'Registration failed.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Boutique Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            max-width: 480px;
            width: 100%;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .card-header {
            background: #fff;
            border-bottom: none;
            text-align: center;
            padding: 2rem 1rem 0;
            border-radius: 15px 15px 0 0 !important;
        }
        .card-body {
            padding: 2rem;
        }
        .btn-primary {
            background: #3498db;
            border: none;
            padding: 12px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="card">
            <div class="card-header">
                <h3>Create Account</h3>
                <p class="text-muted">Join our hotel guest community</p>
            </div>
            <div class="card-body">
                <?php if ($errMsg): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errMsg) ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" required pattern="^[a-zA-Z][a-zA-Z0-9]*$" title="Must start with a letter and contain only letters and numbers">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required pattern="^[a-zA-Z\s]*$" title="Must contain only letters">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" pattern="^[A-Za-z][a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Must start with a letter and contain only letters and numbers" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="phone" class="form-control" required pattern="^[0-9]+$">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <small class="text-muted">At least 6 characters</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
                <p class="mt-3 text-center">
                    Already have an account? <a href="login.php">Login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
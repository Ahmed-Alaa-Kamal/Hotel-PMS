<?php
require_once __DIR__ . '/../../Controllers/ManagerController.php';
require_once __DIR__ . '/../../Models/User.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'manager') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$mgr = new ManagerController();
$err = '';
$success = '';
$user = null;
$id   = $_GET['id'] ?? null;

if ($id) {
    $user = $mgr->getUserById($id);
    if (!$user) die('User not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userObj = new User();
    $userObj->username  = $_POST['username'] ?? '';
    $userObj->full_name = $_POST['full_name'] ?? '';
    $userObj->email     = $_POST['email'] ?? '';
    $userObj->phone     = $_POST['phone'] ?? '';
    $userObj->role      = $_POST['role'] ?? '';

    if ($id) {
        // تعديل مستخدم: كلمة المرور اختيارية
        $userObj->password = $_POST['password'] ?? '';
        $mgr->updateUser($id, $userObj);
        $success = 'User updated successfully.';
        $user = $mgr->getUserById($id);
    } else {
        // إضافة مستخدم جديد
        $userObj->password = $_POST['password'] ?? '';
        if (empty($userObj->password)) {
            $err = 'Password is required for new user.';
        } else {
            $result = $mgr->createUser($userObj);
            if ($result) {
                $success = 'User created successfully.';
            } else {
                $err = 'Username already exists or database error.';
            }
        }
    }
}

$page_title = $id ? 'Edit User' : 'Add User';
$active = 'users';
?>
<!-- HTML يبدأ هنا -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { background: #1a252f; min-height: 100vh; }
        .sidebar .nav-link { color: #b0bec5; border-radius: .35rem; margin-bottom: .2rem; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
        .sidebar .nav-link.active { background: #3498db; color: #fff !important; }
        .topbar { background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    </style>
</head>
<body>
<div class="container-fluid g-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar p-3">
            <h5 class="text-white mb-4"><i class="bi bi-building"></i> Hotel PMS</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="users.php" class="nav-link active"><i class="bi bi-people me-2"></i>Users</a>
                <a href="guests.php" class="nav-link"><i class="bi bi-person-badge me-2"></i>Guests</a>
                <a href="rooms.php" class="nav-link"><i class="bi bi-door-open me-2"></i>Rooms</a>
                <a href="room_types.php" class="nav-link"><i class="bi bi-tags me-2"></i>Room Types</a>
                <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart me-2"></i>Reports</a>
                <a href="feedback.php" class="nav-link"><i class="bi bi-star me-2"></i>Feedback</a>
                <a href="messages.php" class="nav-link"><i class="bi bi-envelope me-2"></i>Messages</a>
                <a href="blacklist.php" class="nav-link "><i class="bi bi-shield-exclamation me-2"></i>Blacklist</a>
                                <a href="blacklist_approval.php" class="nav-link "><i class="bi bi-check-circle me-2"></i>Approval</a>

            </nav>
        </div>
        <!-- Main -->
        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (Manager)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>
            <div class="p-4">
                <div class="card shadow-sm w-50 mx-auto">
                    <div class="card-body">
                        <a href="users.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back</a>
                        <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
                        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" required
                                       value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password <?= !$id ? '*' : '' ?></label>
                                <input type="password" name="password" class="form-control" <?= !$id ? 'required' : '' ?>>
                                <?php if ($id): ?><small class="text-muted">Leave blank to keep current password.</small><?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" required
                                       value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control"
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <?php foreach (['manager','frontdesk','hk','hksup','guest'] as $r): ?>
                                        <option value="<?= $r ?>" <?= (isset($user) && $user['role'] === $r) ? 'selected' : '' ?>>
                                            <?= ucfirst($r) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-<?= $id ? 'pencil' : 'plus-circle' ?>"></i>
                                <?= $id ? 'Update' : 'Create' ?> User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
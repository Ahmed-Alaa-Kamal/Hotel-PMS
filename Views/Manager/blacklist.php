<?php
require_once __DIR__ . '/../../Controllers/ManagerController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'manager') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$mgr = new ManagerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $guestId = (int)$_POST['guest_id'];
        $reason  = $_POST['ban_reason'] ?? '';
        $mgr->blacklistGuest($guestId, 1, $reason);
    } elseif (isset($_POST['remove'])) {
        $guestId = (int)$_POST['guest_id'];
        $mgr->blacklistGuest($guestId, 0);
    }
}

$guests = $mgr->listGuests(); 
$blacklisted = array_filter($guests, fn($g) => !empty($g['blacklisted']));

$page_title = 'Blacklist Management';
$active = 'blacklist';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
        <div class="col-md-2 sidebar p-3">
            <h5 class="text-white mb-4"><i class="bi bi-building"></i> Hotel PMS</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i>Users</a>
                <a href="guests.php" class="nav-link"><i class="bi bi-person-badge me-2"></i>Guests</a>
                <a href="rooms.php" class="nav-link"><i class="bi bi-door-open me-2"></i>Rooms</a>
                <a href="room_types.php" class="nav-link"><i class="bi bi-tags me-2"></i>Room Types</a>
                <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart me-2"></i>Reports</a>
                <a href="feedback.php" class="nav-link"><i class="bi bi-star me-2"></i>Feedback</a>
                <a href="messages.php" class="nav-link"><i class="bi bi-envelope me-2"></i>Messages</a>
                <a href="blacklist.php" class="nav-link active"><i class="bi bi-shield-exclamation me-2"></i>Blacklist</a>
                                <a href="blacklist_approval.php" class="nav-link "><i class="bi bi-check-circle me-2"></i>Approval</a>

            </nav>
        </div>
        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (Manager)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>
            <div class="p-4">
                <h4>Blacklisted Guests</h4>
                <div class="card shadow-sm mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark"><tr><th>ID</th><th>Name</th><th>Email</th><th>Action</th></tr></thead>
                            <tbody>
                            <?php foreach ($blacklisted as $g): ?>
                                <tr>
                                    <td><?= (int)$g['id'] ?></td>
                                    <td><?= htmlspecialchars($g['full_name']) ?></td>
                                    <td><?= htmlspecialchars($g['email'] ?? '') ?></td>
                                    <td>
                                        <form method="post">
                                            <input type="hidden" name="guest_id" value="<?= (int)$g['id'] ?>">
                                            <button type="submit" name="remove" class="btn btn-sm btn-success">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <h4>Add to Blacklist</h4>
                <form method="post" class="card shadow-sm p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <select name="guest_id" class="form-select">
                                <?php foreach ($guests as $g): if (empty($g['blacklisted'])): ?>
                                    <option value="<?= (int)$g['id'] ?>"><?= htmlspecialchars($g['full_name']) ?> (<?= htmlspecialchars($g['email'] ?? '') ?>)</option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="ban_reason" class="form-control" placeholder="Reason for ban" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="add" class="btn btn-danger w-100">Add to Blacklist</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
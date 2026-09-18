<?php
require_once __DIR__ . '/../../Controllers/ManagerController.php';
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'manager') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$mgr = new ManagerController();
$fd  = new FrontdeskController();

$message = '';

if (isset($_POST['action'])) {
    $resId = (int)$_POST['reservation_id'];
    if ($_POST['action'] === 'approve') {
        $fd->updateReservationStatus($resId, 'pending_payment');
        $message = 'Reservation approved. Guest can now complete payment.';
    } elseif ($_POST['action'] === 'reject') {
        $fd->updateReservationStatus($resId, 'cancelled');
        $message = 'Reservation rejected.';
    }
}

$pending = $fd->getReservationsByStatus('pending_blacklist');

$page_title = 'Blacklist Approval';
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
                <a href="blacklist.php" class="nav-link"><i class="bi bi-shield-exclamation me-2"></i>Blacklist</a>
                <a href="blacklist_approval.php" class="nav-link active"><i class="bi bi-check-circle me-2"></i>Approval</a>
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
                <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                <h4>Pending Blacklist Reservations</h4>
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr><th>ID</th><th>Guest</th><th>Room Type</th><th>Dates</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                            <?php if (empty($pending)): ?>
                                <tr><td colspan="5" class="text-center py-4">No pending blacklist reservations.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pending as $r): ?>
                                    <tr>
                                        <td><?= (int)$r['id'] ?></td>
                                        <td><?= htmlspecialchars($r['guest_name']) ?></td>
                                        <td><?= htmlspecialchars($r['room_type_name']) ?></td>
                                        <td><?= $r['check_in'] ?> → <?= $r['check_out'] ?></td>
                                        <td>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="reservation_id" value="<?= (int)$r['id'] ?>">
                                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
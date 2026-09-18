<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'manager') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$fd     = new FrontdeskController();
$guests = $fd->listGuests(); 

$page_title = 'All Guests';
$active = 'guests';
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
                <a href="guests.php" class="nav-link active"><i class="bi bi-person-badge me-2"></i>Guests</a>
                <a href="rooms.php" class="nav-link"><i class="bi bi-door-open me-2"></i>Rooms</a>
                <a href="room_types.php" class="nav-link"><i class="bi bi-tags me-2"></i>Room Types</a>
                <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart me-2"></i>Reports</a>
                <a href="feedback.php" class="nav-link"><i class="bi bi-star me-2"></i>Feedback</a>
                <a href="messages.php" class="nav-link"><i class="bi bi-envelope me-2"></i>Messages</a>
                <a href="blacklist.php" class="nav-link"><i class="bi bi-shield-exclamation me-2"></i>Blacklist</a>
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
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Nationality</th>
                                    <th>VIP</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($guests)): ?>
                                <tr><td colspan="7" class="text-center py-4">No guests found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($guests as $g): ?>
                                    <tr>
                                        <td><?= (int)$g['id'] ?></td>
                                        <td><?= htmlspecialchars($g['full_name']) ?></td>
                                        <td><?= htmlspecialchars($g['email'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($g['phone'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($g['nationality'] ?? '') ?></td>
                                        <td>
                                            <?php if ($g['vip'] == 1 || in_array($g['loyalty_tier'], ['gold','platinum'])): ?>
                                                <span class="badge bg-warning text-dark">⭐ VIP</span>
                                            <?php endif; ?>
                                        </td>                                       
                                         <td><?= (int)($g['loyalty_points'] ?? 0) ?></td>
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
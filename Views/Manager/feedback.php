<?php
require_once __DIR__ . '/../../Controllers/DBController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'manager') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$db = DBController::getInstance();
$feedbacks = $db->select(
    "SELECT f.*, g.full_name AS guest_name 
     FROM feedback f 
     JOIN guests g ON f.guest_id = g.id 
     ORDER BY f.created_at DESC 
     LIMIT 100"
);
$avg = $db->selectOne("SELECT AVG(rating) AS avg_rating FROM feedback");

$page_title = 'Guest Feedback';
$active = 'feedback';
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
        .rating-stars { color: #f39c12; }
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
                <a href="feedback.php" class="nav-link active"><i class="bi bi-star me-2"></i>Feedback</a>
                <a href="messages.php" class="nav-link"><i class="bi bi-envelope me-2"></i>Messages</a>
                <a href="blacklist.php" class="nav-link "><i class="bi bi-shield-exclamation me-2"></i>Blacklist</a>
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
                <div class="card kpi-card p-3 mb-4 text-center" style="max-width: 200px;">
                    <div class="text-muted small">Average Rating</div>
                    <h3><?= number_format($avg['avg_rating'] ?? 0, 1) ?> / 5</h3>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header"><strong>All Reviews</strong></div>
                    <div class="card-body p-0">
                        <?php if (empty($feedbacks)): ?>
                            <p class="text-center py-4">No feedback received yet.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                            <?php foreach ($feedbacks as $fb): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars($fb['guest_name']) ?></strong>
                                        <span class="rating-stars">
                                            <?= str_repeat('★', (int)$fb['rating']) ?><?= str_repeat('☆', 5 - (int)$fb['rating']) ?>
                                        </span>
                                        <small class="text-muted"><?= $fb['created_at'] ?></small>
                                    </div>
                                    <?php if (!empty($fb['comment'])): ?>
                                        <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($fb['comment'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
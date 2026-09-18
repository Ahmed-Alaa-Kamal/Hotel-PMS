<?php
require_once __DIR__ . '/../../Controllers/HkSupController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['hksup','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$hksup = new HkSupController();
$pendingTasks = $hksup->listAllTasks('pending');
$doneTasks    = $hksup->listAllTasks('done');
$maintenance  = $hksup->listMaintenance('open');
$rooms        = $hksup->listRooms();

$db = DBController::getInstance();
$notifData = $db->getNotifications($_SESSION['userId']);
$notifCount = $notifData['count'];
$notifItems = $notifData['items'];

$page_title = 'Housekeeping Supervisor Dashboard';
$active = 'dashboard';
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
        .kpi-card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.05); }
    </style>
</head>
<body>
<div class="container-fluid g-0">
    <div class="row g-0">
        <div class="col-md-2 sidebar p-3">
            <h5 class="text-white mb-4"><i class="bi bi-clipboard-check"></i> HK Sup</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="tasks.php" class="nav-link"><i class="bi bi-list-task me-2"></i>All Tasks</a>
                <a href="inspection.php" class="nav-link"><i class="bi bi-check-circle me-2"></i>Inspection</a>
                <a href="maintenance.php" class="nav-link"><i class="bi bi-tools me-2"></i>Maintenance</a>
            </nav>
        </div>
        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div class="d-flex align-items-center">
                    <ul class="navbar-nav me-2">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell" style="font-size:1.2rem;"></i>
                                <span id="notif-count" class="badge rounded-pill bg-danger" 
                                    style="display: <?= $notifCount > 0 ? 'inline-block' : 'none' ?>;">
                                    <?= $notifCount ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 320px;" aria-labelledby="notifDropdown">
                                <?php if (empty($notifItems)): ?>
                                    <li><span class="dropdown-item text-muted">No notifications</span></li>
                                <?php else: ?>
                                    <?php foreach ($notifItems as $n): ?>
                                        <?php $isUnread = (int)($n['is_read'] ?? 0) === 0; ?>
                                        <li>
                                            <a class="dropdown-item <?= $isUnread ? 'fw-bold' : '' ?>" 
                                            href="/hotel_pms/Views/Auth/mark_notification_read.php?id=<?= $n['id'] ?>&redirect=<?= urlencode($n['link'] ?? '#') ?>">
                                                <div><?= htmlspecialchars($n['message']) ?></div>
                                                <small class="text-muted"><?= $n['created_at'] ?></small>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </li>
                    </ul>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (HK Sup)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>
            <div class="p-4">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card kpi-card p-3 text-center">
                            <div class="text-muted small">Pending Tasks</div>
                            <h3><?= count($pendingTasks) ?></h3>
                            <a href="tasks.php" class="stretched-link"></a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card kpi-card p-3 text-center">
                            <div class="text-muted small">Awaiting Inspection</div>
                            <h3><?= count($doneTasks) ?></h3>
                            <a href="inspection.php" class="stretched-link"></a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card kpi-card p-3 text-center">
                            <div class="text-muted small">Open Maintenance</div>
                            <h3><?= count($maintenance) ?></h3>
                            <a href="maintenance.php" class="stretched-link"></a>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm p-3">
                            <h5>Quick actions</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="tasks.php" class="btn btn-outline-primary btn-sm">Manage Tasks</a>
                                <a href="inspection.php" class="btn btn-outline-success btn-sm">Inspection</a>
                                <a href="maintenance.php" class="btn btn-outline-warning btn-sm">Maintenance</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    function updateNotifList() {
        fetch('/hotel_pms/Views/Auth/notifications_list.php?_=' + Date.now())
            .then(function(response) { return response.text(); })
            .then(function(html) {
                var dropdown = document.querySelector('#notifDropdown + .dropdown-menu');
                if (dropdown) {
                    dropdown.innerHTML = html;
                }
            })
            .catch(function(err) { console.error('List update error:', err); });
    }

    function updateBadge() {
        fetch('/hotel_pms/Views/Auth/notifications_count.php?_=' + Date.now())
            .then(function(response) { return response.text(); })
            .then(function(countText) {
                var count = parseInt(countText, 10);
                var badge = document.getElementById('notif-count');
                if (badge) {
                    badge.textContent = count > 0 ? count : '';
                    badge.style.display = count > 0 ? 'inline-block' : 'none';
                }
            })
            .catch(function(err) { console.error('Badge update error:', err); });
    }

    updateBadge();
    updateNotifList();
    setInterval(function() {
        updateBadge();
        updateNotifList();
    }, 1000);
})();
</script>


</body>
</html>
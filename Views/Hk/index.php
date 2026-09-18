<?php
require_once __DIR__ . '/../../Controllers/HkController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['hk','hksup','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$hk = new HkController();
$myTasks      = $hk->listMyTasks($_SESSION['userId']);
$taskHistory  = $hk->getMyTaskHistory($_SESSION['userId']);

$db = DBController::getInstance();
$notifData = $db->getNotifications($_SESSION['userId']);
$notifCount = $notifData['count'];
$notifItems = $notifData['items'];

$page_title = 'My Tasks – Housekeeper';
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
    </style>
</head>
<body>
<div class="container-fluid g-0">
    <div class="row g-0">
        <div class="col-md-2 sidebar p-3">
            <h5 class="text-white mb-4">🧹 Housekeeping</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link active"><i class="bi bi-list-check me-2"></i>My Tasks</a>
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
                                <span id="notif-count" class="badge rounded-pill bg-danger" style="display: <?= $notifCount > 0 ? 'inline-block' : 'none' ?>;"><?= $notifCount ?></span>
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
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (HK)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>
            <div class="p-4">
                <h4 class="mb-3">Current Tasks (<?= count($myTasks) ?>)</h4>
                <?php if (empty($myTasks)): ?>
                    <div class="alert alert-info">No tasks assigned right now.</div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($myTasks as $t): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card shadow-sm border-start border-4 border-<?= $t['priority'] === 'high' ? 'danger' : ($t['priority'] === 'normal' ? 'warning' : 'secondary') ?>">
                                    <div class="card-body">
                                        <h5>Room <?= htmlspecialchars($t['room_no']) ?></h5>
                                        <small>Floor <?= $t['floor'] ?></small>
                                        <p class="mt-2 mb-1"><strong><?= htmlspecialchars($t['task_type']) ?></strong></p>
                                        <span class="badge bg-<?= $t['status'] === 'rejected' ? 'danger' : ($t['status'] === 'in_progress' ? 'info' : 'secondary') ?>">
                                            <?= htmlspecialchars($t['status']) ?>
                                        </span>
                                        <?php if ($t['status'] === 'rejected' && !empty($t['notes'])): ?>
                                            <div class="mt-2 small text-danger"><?= nl2br(htmlspecialchars($t['notes'])) ?></div>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <a href="task_detail.php?id=<?= (int)$t['id'] ?>" class="btn btn-sm btn-primary">Open</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h4 class="mt-5 mb-3">Task History</h4>
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Room</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Completed</th>
                                    <th>Inspected</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($taskHistory)): ?>
                                <tr><td colspan="6" class="text-center py-4">No task history.</td></tr>
                            <?php else: ?>
                                <?php foreach ($taskHistory as $th): ?>
                                    <tr>
                                        <td><?= (int)$th['id'] ?></td>
                                        <td><?= htmlspecialchars($th['room_no']) ?></td>
                                        <td><?= htmlspecialchars($th['task_type']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $th['status'] === 'inspected' ? 'success' : ($th['status'] === 'rejected' ? 'danger' : 'info') ?>">
                                                <?= htmlspecialchars($th['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($th['completed_at'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($th['inspected_at'] ?? 'N/A') ?></td>
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
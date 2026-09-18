<?php
require_once __DIR__ . '/../../Controllers/HkSupController.php';
require_once __DIR__ . '/../../Controllers/HkController.php';
require_once __DIR__ . '/../../Models/Notification.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['hksup','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$hksup        = new HkSupController();
$hk           = new HkController();
$taskId       = (int)($_GET['id'] ?? 0);
$task         = $hk->getTaskById($taskId);
if (!$task) die('Task not found.');

$housekeepers = $hksup->listHousekeepers();
$message      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $hkUserId = (int)$_POST['hk_user_id'];
    if ($hksup->assignTask($taskId, $hkUserId)) {
        $message = 'Task assigned successfully.';

        $db = DBController::getInstance();
        $notif = new Notification();
        $notif->user_id = $hkUserId;
        $notif->message = "New task assigned: Room {$task['room_no']} - {$task['task_type']}";
        $notif->link    = "/hotel_pms/Views/Hk/task_detail.php?id=$taskId";
        $db->addNotification($notif);

        $task = $hk->getTaskById($taskId);
    } else {
        $message = 'Failed to assign task.';
    }
}

$page_title = 'Assign Task #' . $taskId . ' – ' . htmlspecialchars($task['room_no']);
$active = 'tasks';
?>
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
        <div class="col-md-2 sidebar p-3">
            <h5 class="text-white mb-4">HK Supervisor</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="tasks.php" class="nav-link active"><i class="bi bi-list-check me-2"></i>Tasks</a>
                <a href="inspection.php" class="nav-link"><i class="bi bi-check2-square me-2"></i>Inspection</a>
                <a href="inventory.php" class="nav-link"><i class="bi bi-boxes me-2"></i>Inventory</a>
                <a href="maintenance.php" class="nav-link"><i class="bi bi-tools me-2"></i>Maintenance</a>
                <a href="lostfound.php" class="nav-link"><i class="bi bi-search me-2"></i>Lost & Found</a>
            </nav>
        </div>
        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (HK Sup)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            <div class="p-4">
                <a href="tasks.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back</a>
                <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5>Room <?= htmlspecialchars($task['room_no']) ?> – <?= htmlspecialchars($task['task_type']) ?></h5>
                        <?php if (!empty($task['assignee'])): ?>
                            <p>Currently assigned to: <strong><?= htmlspecialchars($task['assignee']) ?></strong></p>
                        <?php endif; ?>
                        <form method="post" class="mt-3">
                            <div class="mb-3">
                                <label class="form-label">Assign to</label>
                                <select name="hk_user_id" class="form-select" required>
                                    <option value="">Select housekeeper...</option>
                                    <?php foreach ($housekeepers as $hkUser): ?>
                                        <option value="<?= (int)$hkUser['id'] ?>"><?= htmlspecialchars($hkUser['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="assign" class="btn btn-primary"><i class="bi bi-person-check"></i> Assign</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
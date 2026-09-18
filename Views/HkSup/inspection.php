<?php
require_once __DIR__ . '/../../Controllers/HkSupController.php';
require_once __DIR__ . '/../../Controllers/HkController.php';      
require_once __DIR__ . '/../../Models/Notification.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['hksup','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$hksup   = new HkSupController();
$hk      = new HkController();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $taskId = (int)$_POST['task_id'];
    if ($hksup->inspectAndApprove($taskId, $_SESSION['userId'])) {
        $message = 'Task approved successfully. Room is now clean.';

        $db = DBController::getInstance();
        $task = $hk->getTaskById($taskId);
        if ($task && $task['assigned_to']) {
            $notif = new Notification();
            $notif->user_id = (int)$task['assigned_to'];
            $notif->message = "Your task for Room {$task['room_no']} has been approved. The room is ready.";
            $notif->link = "/hotel_pms/Views/Hk/task_detail.php?id=$taskId";
            $db->addNotification($notif);
        }
    } else {
        $message = 'Approval failed. Task may not be in completed state.';
    }
    header('Location: inspection.php?msg=' . urlencode($message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject'])) {
    $taskId = (int)$_POST['task_id'];
    $notes  = $_POST['notes'] ?? 'No reason provided';
    if ($hksup->inspectAndReject($taskId, $_SESSION['userId'], $notes)) {
        $message = 'Task rejected. A new cleaning task has been created.';

        $db = DBController::getInstance();
        $task = $hk->getTaskById($taskId);
        if ($task && $task['assigned_to']) {
            $notif = new Notification();
            $notif->user_id = (int)$task['assigned_to'];
            $notif->message = "Your task for Room {$task['room_no']} was rejected. Reason: $notes";
            $notif->link = "/hotel_pms/Views/Hk/task_detail.php?id=$taskId";
            $db->addNotification($notif);
        }
    } else {
        $message = 'Rejection failed. Make sure the task is completed.';
    }
    header('Location: inspection.php?msg=' . urlencode($message));
    exit;
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

$tasks = $hksup->listAllTasks('done');

$page_title = 'Inspection';
$active = 'inspection';
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
            <h5 class="text-white mb-4"><i class="bi bi-clipboard-check"></i> HK Sup</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="tasks.php" class="nav-link"><i class="bi bi-list-task me-2"></i>All Tasks</a>
                <a href="inspection.php" class="nav-link active"><i class="bi bi-check-circle me-2"></i>Inspection</a>
                <a href="maintenance.php" class="nav-link"><i class="bi bi-tools me-2"></i>Maintenance</a>
            </nav>
        </div>

        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (HK Sup)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>

            <div class="p-4">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header"><strong>Tasks Awaiting Inspection</strong></div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th><th>Room</th><th>Type</th><th>Worker</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($tasks)): ?>
                                <tr><td colspan="5" class="text-center py-4">No tasks awaiting inspection.</td></tr>
                            <?php else: ?>
                                <?php foreach ($tasks as $t): ?>
                                <tr>
                                    <td><?= (int)$t['id'] ?></td>
                                    <td><?= htmlspecialchars($t['room_no']) ?></td>
                                    <td><?= htmlspecialchars($t['task_type']) ?></td>
                                    <td><?= htmlspecialchars($t['assignee'] ?? 'Unassigned') ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="task_id" value="<?= (int)$t['id'] ?>">
                                            <button type="submit" name="approve" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="task_id" value="<?= (int)$t['id'] ?>">
                                            <input type="text" name="notes" class="form-control form-control-sm d-inline w-50" placeholder="Rejection reason" required>
                                            <button type="submit" name="reject" class="btn btn-sm btn-danger">Reject</button>
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
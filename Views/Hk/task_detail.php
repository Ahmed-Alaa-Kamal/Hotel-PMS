<?php
require_once __DIR__ . '/../../Controllers/HkController.php';
require_once __DIR__ . '/../../Controllers/HkSupController.php';
require_once __DIR__ . '/../../Models/Notification.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['hk','hksup','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$hk     = new HkController();
$taskId = (int)($_GET['id'] ?? 0);
if ($taskId <= 0) die('Invalid task ID.');

$task = $hk->getTaskById($taskId);
if (!$task) die('Task not found.');

if (!in_array($_SESSION['userRole'], ['hksup','admin','manager']) &&
    (int)$task['assigned_to'] !== (int)$_SESSION['userId']) {
    die('This task is not assigned to you.');
}

$message = '';

if (isset($_POST['start'])) {
    $hk->startTask($taskId, $_SESSION['userId']);
    $task = $hk->getTaskById($taskId);
    $message = 'Task started.';
}

if (isset($_POST['complete'])) {
    $notes = $_POST['notes'] ?? '';
    if ($hk->completeTask($taskId, $notes)) {
        $message = 'Task completed. Waiting for supervisor inspection.';

        $db = DBController::getInstance();
        $supervisors = $db->select("SELECT id FROM users WHERE role = 'hksup' AND active = 1");
        foreach ($supervisors as $sup) {
            $notif = new Notification();
            $notif->user_id = (int)$sup['id'];
            $notif->message = "Task completed: Room {$task['room_no']} – {$task['task_type']} is waiting for inspection.";
            $notif->link = "/hotel_pms/Views/HkSup/inspection.php";
            $db->addNotification($notif);
        }
        $task = $hk->getTaskById($taskId);
    }
}

if (isset($_POST['restart'])) {
    $hk->restartTask($taskId, $_SESSION['userId']);
    $task = $hk->getTaskById($taskId);
    $message = 'Task restarted.';
}

$page_title = 'Task #' . $taskId . ' – ' . htmlspecialchars($task['room_no']);
$active = 'dashboard';
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
        .timeline { border-left: 3px solid #dee2e6; padding-left: 20px; }
        .timeline-item { margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container-fluid g-0">
    <div class="row g-0">
        <div class="col-md-2 sidebar p-3">
            <h5 class="text-white mb-4">Housekeeping</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link active"><i class="bi bi-list-check me-2"></i>My Tasks</a>
            </nav>
        </div>
        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (HK)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            <div class="p-4">
                <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back</a>
                <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4>Room <?= htmlspecialchars($task['room_no']) ?> – <?= htmlspecialchars($task['task_type']) ?></h4>
                        <p><strong>Priority:</strong> <?= htmlspecialchars($task['priority']) ?></p>
                        <div class="timeline mt-3">
                            <?php if ($task['status'] === 'done'): ?>
                                <div class="timeline-item"><i class="bi bi-check-circle text-success"></i> <strong>Task completed</strong> at <?= htmlspecialchars($task['completed_at'] ?? 'N/A') ?><div class="alert alert-info mt-2">Waiting for supervisor inspection.</div></div>
                            <?php endif; ?>
                            <?php if ($task['status'] === 'inspected'): ?>
                                <div class="timeline-item"><i class="bi bi-check-circle text-success"></i> <strong>Task completed</strong> at <?= htmlspecialchars($task['completed_at'] ?? 'N/A') ?></div>
                                <div class="timeline-item"><i class="bi bi-check-circle-fill text-success"></i> <strong>Task approved</strong> at <?= htmlspecialchars($task['inspected_at'] ?? 'N/A') ?></div>
                                <div class="alert alert-success mt-2">This task has been approved.</div>
                            <?php endif; ?>
                            <?php if ($task['status'] === 'rejected'): ?>
                                <div class="timeline-item"><i class="bi bi-check-circle text-success"></i> <strong>Task completed</strong> at <?= htmlspecialchars($task['completed_at'] ?? 'N/A') ?></div>
                                <div class="timeline-item"><i class="bi bi-x-circle text-danger"></i> <strong>Task rejected</strong> at <?= htmlspecialchars($task['inspected_at'] ?? 'N/A') ?></div>
                                <?php if (!empty($task['notes'])): ?><div class="alert alert-danger mt-2"><strong>Rejection reason:</strong> <?= nl2br(htmlspecialchars($task['notes'])) ?></div><?php endif; ?>
                                <form method="post" class="mt-2"><button type="submit" name="restart" class="btn btn-warning"><i class="bi bi-arrow-clockwise"></i> Restart Task</button></form>
                            <?php endif; ?>
                        </div>
                        <hr>
                        <?php if ($task['status'] === 'assigned'): ?>
                            <form method="post"><button type="submit" name="start" class="btn btn-warning"><i class="bi bi-play"></i> Start Task</button></form>
                        <?php elseif ($task['status'] === 'in_progress'): ?>
                            <form method="post">
                                <div class="mb-3"><textarea name="notes" class="form-control" rows="2" placeholder="Completion notes..."></textarea></div>
                                <button type="submit" name="complete" class="btn btn-success"><i class="bi bi-check-circle"></i> Complete Task</button>
                            </form>
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
<?php
require_once __DIR__ . '/../../Controllers/HkSupController.php';
require_once __DIR__ . '/../../Models/HkTask.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['hksup','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$hksup = new HkSupController();
$status_filter = $_GET['status'] ?? null;
$tasks = $hksup->listAllTasks($status_filter);
$rooms = $hksup->listRooms();
$housekeepers = $hksup->listHousekeepers();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_task'])) {
    $task = new HkTask();
    $task->room_id     = (int)($_POST['room_id'] ?? 0);
    $task->task_type   = $_POST['task_type'] ?? 'clean';
    $task->priority    = $_POST['priority'] ?? 'normal';
    $task->assigned_to = $_POST['assigned_to'] ?? null;
    $task->notes       = $_POST['notes'] ?? '';

    if ($task->room_id > 0) {
        $result = $hksup->createTask($task);
        if ($result) {
            $message = 'Task created successfully.';
            if (!empty($task->assigned_to)) {
                $db = DBController::getInstance();
                $roomNo = '';
                foreach ($rooms as $r) {
                     if ((int)$r['id'] === (int)$task->room_id) {
                         $roomNo = $r['room_no'];
                          break; } }
                $notif = new Notification();
                $notif->user_id = (int)$task->assigned_to;
                $notif->message = "New task assigned: Room $roomNo - {$task->task_type}";
                $notif->link    = "/hotel_pms/Views/Hk/task_detail.php?id=" . $result;
                $db->addNotification($notif);
            }
        
        $tasks = $hksup->listAllTasks($status_filter);
        } else {
            $message = 'Failed to create task.';
        }
    } else {
        $message = 'Please select a room.';
    }
}

$page_title = 'Task Management';
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
                <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header"><strong>Create New Task</strong></div>
                    <div class="card-body">
                        <form method="post" class="row g-2">
                            <div class="col-md-3">
                                <select name="room_id" class="form-select" required>
                                    <option value="">Room...</option>
                                    <?php foreach ($rooms as $r): ?>
                                        
                                            <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['room_no']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="task_type" class="form-select">
                                    <option value="clean">Clean</option>
                                    <option value="inspect">Inspect</option>
                                    <option value="turn_down">Turn Down</option>
                                    <option value="deep_clean">Deep Clean</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="priority" class="form-select">
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="assigned_to" class="form-select">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($housekeepers as $hk): ?>
                                        <option value="<?= (int)$hk['id'] ?>"><?= htmlspecialchars($hk['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="create_task" class="btn btn-primary w-100"><i class="bi bi-plus"></i> Create</button>
                            </div>
                            <div class="col-12">
                                <input type="text" name="notes" class="form-control" placeholder="Notes (optional)">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr><th>ID</th><th>Room</th><th>Type</th><th>Priority</th><th>Status</th><th>Assignee</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($tasks as $t): ?>
                                <tr>
                                    <td><?= (int)$t['id'] ?></td>
                                    <td><?= htmlspecialchars($t['room_no']) ?></td>
                                    <td><?= htmlspecialchars($t['task_type']) ?></td>
                                    <td><?= htmlspecialchars($t['priority']) ?></td>
                                    <td><?= htmlspecialchars($t['status']) ?></td>
                                    <td><?= htmlspecialchars($t['assignee'] ?? 'Unassigned') ?></td>
                                    <td>
                                        <a href="assign_task.php?id=<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-primary">Assign</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
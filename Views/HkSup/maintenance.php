<?php
require_once __DIR__ . '/../../Controllers/HkSupController.php';
require_once __DIR__ . '/../../Models/Maintenance.php';   

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['hksup','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$hksup   = new HkSupController();
$err     = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report'])) {
    $maint = new Maintenance();
    $maint->room_id     = (int)($_POST['room_id'] ?? 0);
    $maint->description = trim($_POST['description'] ?? '');
    $maint->priority    = $_POST['priority'] ?? 'normal';

    if ($maint->room_id > 0 && !empty($maint->description)) {
        $result = $hksup->createMaintenanceRequest($maint,$_SESSION['userId']);   
        if ($result) {
            $success = 'Maintenance request reported. Room is now out of order.';
        } else {
            $err = 'Failed to report maintenance.';
        }
    } else {
        $err = 'Please fill all fields.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve'])) {
    $maintenance_id = (int)($_POST['maintenance_id'] ?? 0);
    $resolution     = trim($_POST['resolution'] ?? '');
    if ($maintenance_id > 0 && !empty($resolution)) {
        $result = $hksup->resolveMaintenance($maintenance_id, $resolution);
        if ($result) {
            $success = 'Maintenance resolved. Room is dirty and cleaning task created.';
        } else {
            $err = 'Failed to resolve maintenance.';
        }
    } else {
        $err = 'Please provide resolution details.';
    }
}

$requests = $hksup->listMaintenance('open');
$rooms    = $hksup->listRooms();

$page_title = 'Maintenance Requests';
$active = 'maintenance';
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
                <a href="tasks.php" class="nav-link"><i class="bi bi-list-check me-2"></i>Tasks</a>
                <a href="inspection.php" class="nav-link"><i class="bi bi-check2-square me-2"></i>Inspection</a>
                <a href="inventory.php" class="nav-link"><i class="bi bi-boxes me-2"></i>Inventory</a>
                <a href="maintenance.php" class="nav-link active"><i class="bi bi-tools me-2"></i>Maintenance</a>
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
                <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header"><strong>Report New Issue</strong></div>
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
                            <div class="col-md-4"><input name="description" class="form-control" placeholder="Issue description" required></div>
                            <div class="col-md-3">
                                <select name="priority" class="form-select">
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="col-md-2"><button type="submit" name="report" class="btn btn-warning w-100">Report</button></div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr><th>ID</th><th>Room</th><th>Issue</th><th>Priority</th><th>Status</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                            <?php if (empty($requests)): ?>
                                <tr><td colspan="6" class="text-center py-4">No maintenance requests.</td></tr>
                            <?php else: ?>
                                <?php foreach ($requests as $req): ?>
                                    <tr>
                                        <td><?= (int)$req['id'] ?></td>
                                        <td><?= htmlspecialchars($req['room_no']) ?></td>
                                        <td><?= htmlspecialchars($req['description']) ?></td>
                                        <td><?= htmlspecialchars($req['priority']) ?></td>
                                        <td><?= htmlspecialchars($req['status']) ?></td>
                                        <td>
                                            <?php if ($req['status'] !== 'resolved'): ?>
                                                <form method="post" class="d-flex">
                                                    <input type="hidden" name="maintenance_id" value="<?= (int)$req['id'] ?>">
                                                    <input type="text" name="resolution" class="form-control form-control-sm me-2" placeholder="Resolution" required>
                                                    <button type="submit" name="resolve" class="btn btn-sm btn-success">Resolve</button>
                                                </form>
                                            <?php endif; ?>
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
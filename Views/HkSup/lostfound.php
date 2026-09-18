<?php
require_once __DIR__ . '/../../Controllers/HkSupController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['hksup','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$hksup = new HkSupController();

$err     = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $room_id     = (int)($_POST['room_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($room_id > 0 && $description) {
        $hksup->addLostFound($room_id, $description, $_SESSION['userId']);
        $success = 'Item recorded.';
    } else {
        $err = 'Room and description are required.';
    }
}

if (isset($_POST['return'])) {
    $itemId      = (int)($_POST['item_id'] ?? 0);
    $returnedTo  = trim($_POST['returned_to'] ?? '');

    if ($itemId > 0 && $returnedTo) {
        $hksup->returnLostFound($itemId, $returnedTo);
        $success = 'Item returned.';
    }
}

$items = $hksup->listLostFound() ?: [];
$rooms = $hksup->listRooms() ?: [];

$page_title = 'Lost & Found';
$active = 'lostfound';
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
                <a href="maintenance.php" class="nav-link"><i class="bi bi-tools me-2"></i>Maintenance</a>
                <a href="lostfound.php" class="nav-link active"><i class="bi bi-search me-2"></i>Lost & Found</a>
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
                    <div class="card-header"><strong>Report Found Item</strong></div>
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
                            <div class="col-md-6"><input name="description" class="form-control" placeholder="Description of item" required></div>
                            <div class="col-md-3"><button type="submit" name="add" class="btn btn-primary w-100">Save</button></div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr><th>ID</th><th>Room</th><th>Description</th><th>Status</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= (int)$item['id'] ?></td>
                                    <td><?= htmlspecialchars($item['room_no'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($item['description']) ?></td>
                                    <td><?= htmlspecialchars($item['status']) ?></td>
                                    <td>
                                        <?php if ($item['status'] === 'held'): ?>
                                            <form method="post" class="d-flex">
                                                <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                                                <input type="text" name="returned_to" class="form-control form-control-sm me-2" placeholder="Returned to" required>
                                                <button type="submit" name="return" class="btn btn-sm btn-success">Return</button>
                                            </form>
                                        <?php else: ?>
                                            <?= htmlspecialchars($item['returned_to'] ?? '') ?>
                                        <?php endif; ?>
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
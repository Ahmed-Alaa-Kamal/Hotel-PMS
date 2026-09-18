<?php
require_once __DIR__ . '/../../Controllers/DBController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['hksup','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

    $db = DBController::getInstance();
$inventory = $db->select("SELECT * FROM minibar_stock ORDER BY item_name");
$alerts    = [];
foreach ($inventory as $item) {
    if ($item['qty'] < 10) {
        $alerts[] = $item['item_name'] . " (qty: " . $item['qty'] . ")";
    }
}

$err     = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_item'])) {
    $item_name  = trim($_POST['item_name'] ?? '');
    $qty        = (int)($_POST['qty'] ?? 0);
    $unit_price = (float)($_POST['unit_price'] ?? 0);
    if ($item_name && $qty >= 0) {
        $existing = $db->selectOne("SELECT * FROM minibar_stock WHERE item_name = '" . $db->escape($item_name) . "'");
        if ($existing) {
            $db->update("UPDATE minibar_stock SET qty = $qty, unit_price = $unit_price WHERE id = " . (int)$existing['id']);
        } else {
            $db->insert("INSERT INTO minibar_stock (item_name, qty, unit_price) VALUES ('" . $db->escape($item_name) . "', $qty, $unit_price)");
        }
        $success = 'Item saved.';
        $inventory = $db->select("SELECT * FROM minibar_stock ORDER BY item_name");
    } else {
        $err = 'Item name and qty are required.';
    }
}

$page_title = 'Minibar Inventory';
$active = 'inventory';
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
            <h5 class="text-white mb-4">HK Supervisor</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="tasks.php" class="nav-link"><i class="bi bi-list-check me-2"></i>Tasks</a>
                <a href="inspection.php" class="nav-link"><i class="bi bi-check2-square me-2"></i>Inspection</a>
                <a href="inventory.php" class="nav-link active"><i class="bi bi-boxes me-2"></i>Inventory</a>
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
                <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
                <?php if (!empty($alerts)): ?>
                    <div class="alert alert-warning"><strong>Low Stock Alerts:</strong> <?= implode(', ', $alerts) ?></div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header"><strong>Add / Update Item</strong></div>
                    <div class="card-body">
                        <form method="post" class="row g-2">
                            <div class="col-md-4"><input name="item_name" class="form-control" placeholder="Item Name" required></div>
                            <div class="col-md-3"><input type="number" name="qty" class="form-control" min="0" placeholder="Qty" required></div>
                            <div class="col-md-3"><input type="number" step="0.01" name="unit_price" class="form-control" min="0" placeholder="Unit Price" required></div>
                            <div class="col-md-2"><button type="submit" name="save_item" class="btn btn-primary w-100">Save</button></div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr><th>Item</th><th>Qty</th><th>Unit Price</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($inventory as $inv): ?>
                                <tr class="<?= $inv['qty'] < 10 ? 'table-danger' : '' ?>">
                                    <td><?= htmlspecialchars($inv['item_name']) ?></td>
                                    <td><?= (int)$inv['qty'] ?></td>
                                    <td><?= number_format($inv['unit_price'], 2) ?> EGP</td>
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
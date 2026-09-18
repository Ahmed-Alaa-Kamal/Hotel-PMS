<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'guest') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$fd = new FrontdeskController();
$folio_id = (int)($_GET['id'] ?? 0);
if ($folio_id <= 0) { die('Invalid folio ID.'); }

$folio    = $fd->getFolio($folio_id);
if (!$folio) { die('Folio not found.'); }

// تحقق من أن هذا الفوليو يخص هذا الضيف
$myGuest = $gc->getGuestByUserId($_SESSION['userId']);
if (!$myGuest || (int)$folio['guest_id'] !== (int)$myGuest['id']) {
    die('Unauthorized access.');
}

$charges  = $fd->getFolioCharges($folio_id);
$payments = $fd->getFolioPayments($folio_id);
$res      = $fd->getReservation($folio['reservation_id']);

$page_title = 'My Folio #' . $folio_id;
$active = 'folio';
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
            <h5 class="text-white mb-4">Hotel PMS</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link"><i class="bi bi-house me-2"></i>Dashboard</a>
                <a href="reservations.php" class="nav-link"><i class="bi bi-calendar me-2"></i>Reservations</a>
                <a href="folio.php?id=<?= $folio_id ?>" class="nav-link active"><i class="bi bi-receipt me-2"></i>Folio</a>
            </nav>
        </div>
        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?></span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            <div class="p-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5>Reservation #<?= (int)$res['id'] ?></h5>
                        <p>Room: <?= htmlspecialchars($res['room_no']) ?> | Dates: <?= $res['check_in'] ?> → <?= $res['check_out'] ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header"><strong>Charges</strong></div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr><th>Description</th><th>Amount</th></tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($charges as $c): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($c['description']) ?></td>
                                            <td><?= number_format($c['amount'],2) ?> EGP</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light"><tr><th>Total</th><th><?= number_format($folio['total_charges'] ?? 0, 2) ?> EGP</th></tr></tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header"><strong>Payments</strong></div>
                            <table class="table table-sm mb-0">
                                <thead class="table-light"><tr><th>Method</th><th>Amount</th></tr></thead>
                                <tbody>
                                <?php foreach ($payments as $p): ?>
                                    <tr><td><?= htmlspecialchars($p['method']) ?></td><td><?= number_format($p['amount'],2) ?> EGP</td></tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <h5>Balance</h5>
                                <h3><?= number_format($folio['balance'] ?? 0, 2) ?> EGP</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
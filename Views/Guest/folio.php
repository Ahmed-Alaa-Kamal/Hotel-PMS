<?php
require_once __DIR__ . '/../../Controllers/GuestController.php';
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'guest') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$gc = new GuestController();
$fd = new FrontdeskController();
$guest = $gc->getGuestByUserId($_SESSION['userId']);
if (!$guest) die('Guest profile not found.');

$folio_id = (int)($_GET['id'] ?? 0);
$reservation_id = (int)($_GET['reservation_id'] ?? 0);


if ($folio_id <= 0 && $reservation_id > 0) {
    $folio = $gc->getMyFolioForReservation($reservation_id);  
    if ($folio) {
        $folio_id = (int)$folio['id'];
    } else {
        die('No folio found for this reservation.');
    }
} elseif ($folio_id > 0) {
    
} else {
    die('Invalid request.');
}

$folio = $gc->getFolio($folio_id);  
if (!$folio) die('Folio not found.');
if ((int)$folio['guest_id'] !== (int)$guest['id']) die('Unauthorized access.');

$charges  = $gc->getFolioCharges($folio_id);
$payments = $gc->getFolioPayments($folio_id);
$reservation = $fd->getReservation($folio['reservation_id'] ?? $reservation_id);

$page_title = 'My Folio #' . $folio_id;
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
            <h5 class="text-white mb-4"><i class="bi bi-person"></i> <?= htmlspecialchars($guest['full_name']) ?></h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="reserv.php" class="nav-link"><i class="bi bi-plus-circle me-2"></i>New Reservation</a>
                <a href="reservations.php" class="nav-link"><i class="bi bi-calendar me-2"></i>My Reservations</a>
                <a href="folio.php?id=<?= $folio_id ?>" class="nav-link active"><i class="bi bi-receipt me-2"></i>Current Folio</a>
                <a href="profile.php" class="nav-link"><i class="bi bi-person-circle me-2"></i>Profile</a>
                <a href="feedback.php" class="nav-link"><i class="bi bi-star me-2"></i>Feedback</a>
            </nav>
        </div>

        
        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (Guest)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>

            <div class="p-4">
                <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back</a>

                <?php if ($reservation): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5>Reservation #<?= (int)$reservation['id'] ?></h5>
                        <p>Room: <?= htmlspecialchars($reservation['room_no'] ?? 'N/A') ?> | 
                           Dates: <?= $reservation['check_in'] ?> → <?= $reservation['check_out'] ?></p>
                    </div>
                </div>
                <?php endif; ?>

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
                                    <?php if (empty($charges)): ?>
                                        <tr><td colspan="2" class="text-center py-3">No charges.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($charges as $c): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($c['description']) ?></td>
                                                <td><?= number_format($c['amount'], 2) ?> EGP</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr><th>Total Charges</th><th><?= number_format($folio['total_charges'] ?? 0, 2) ?> EGP</th></tr>
                                    </tfoot>
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
                                <?php if (empty($payments)): ?>
                                    <tr><td colspan="2" class="text-center py-3">No payments.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($payments as $p): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($p['method']) ?></td>
                                            <td><?= number_format($p['amount'], 2) ?> EGP</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr><th>Total Paid</th><th><?= number_format($folio['total_payments'] ?? 0, 2) ?> EGP</th></tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <h5>Balance</h5>
                                <h3 class="text-<?= ($folio['balance'] ?? 0) > 0 ? 'danger' : 'success' ?>">
                                    <?= number_format($folio['balance'] ?? 0, 2) ?> EGP
                                </h3>
                            </div>
                        </div>

                        
                        <div class="card shadow-sm mt-3">
                            <div class="card-body">
                                <h6 class="text-muted">Summary</h6>
                                <?php
                                $roomTotal = 0;
                                foreach ($charges as $c) {
                                    if ($c['charge_type'] === 'room') {
                                        $roomTotal += $c['amount'];
                                    }
                                }
                                $taxAmount = $folio['tax_amount'] ?? 0;
                                ?>
                                <div class="d-flex justify-content-between small">
                                    <span>Room Charges:</span>
                                    <span><?= number_format($roomTotal, 2) ?> EGP</span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span>Other Services:</span>
                                    <span><?= number_format(($folio['total_charges'] ?? 0) - $roomTotal - $taxAmount, 2) ?> EGP</span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span>VAT (14% on room):</span>
                                    <span><?= number_format($taxAmount, 2) ?> EGP</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
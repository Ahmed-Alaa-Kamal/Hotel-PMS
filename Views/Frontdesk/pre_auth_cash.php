<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';
require_once __DIR__ . '/../../Models/Payment.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['frontdesk','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$fd             = new FrontdeskController();
$reservation_id = (int)($_GET['reservation_id'] ?? 0);
if ($reservation_id <= 0) die('Invalid reservation ID.');

$res = $fd->getReservation($reservation_id);
if (!in_array($res['status'], ['pending', 'booked', 'pending_payment'])) {
    die('Reservation is not in a valid status for pre‑authorization.');
}


$nights     = (strtotime($res['check_out']) - strtotime($res['check_in'])) / 86400;
if ($nights < 1) $nights = 1;
$roomTotal  = $res['rate'] * $nights;
$vat        = round($roomTotal * 0.14, 2);
$grandTotal = $roomTotal + $vat;
$preAuth    = round($grandTotal * 0.20, 2);

$err     = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paid = (float)($_POST['amount'] ?? 0);

    if ($paid <= 0) {
        $err = 'Amount must be greater than zero.';
    } elseif ($paid < $preAuth) {
        $err = 'Minimum pre-authorization amount is ' . number_format($preAuth, 2) . ' EGP.';
    } else {
    
        $folio = $fd->getFolioByReservation($reservation_id);
        if (!$folio) {
            $folioId = $fd->confirmReservation($reservation_id);
            if (!$folioId) die('Failed to create folio.');
            $folio = $fd->getFolio($folioId);
        }

      
        $payment = new Payment();
        $payment->folio_id   = (int)$folio['id'];
        $payment->amount     = $paid;
        $payment->method     = 'cash';
        $payment->reference  = 'Pre-Authorization Hold';
        $payment->received_by = $_SESSION['userId'];

$result = $fd->recordPayment($payment);
if ($result) {
   
    if (in_array($res['status'], ['pending', 'pending_payment'])) {
        $fd->updateReservationStatus($reservation_id, 'booked');
    }
    $success = true;
}else {
            $err = 'Payment failed.';
        }
    }
}

$page_title = 'Pre-Authorization — Reservation #' . $reservation_id;
$active = 'reservations';
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
        .success-icon { font-size: 4rem; color: #28a745; }
        .summary-row { font-size: .9rem; padding: .3rem 0; border-bottom: 1px solid #f0f0f0; }
        .summary-row:last-child { border-bottom: none; }
    </style>
</head>
<body>
<div class="container-fluid g-0">
    <div class="row g-0">

        
        <div class="col-md-2 sidebar p-3">
            <h5 class="text-white mb-4"><i class="bi bi-building"></i> Hotel PMS</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="reservations.php" class="nav-link active"><i class="bi bi-calendar-check me-2"></i>Reservations</a>
                <a href="new_reservation.php" class="nav-link"><i class="bi bi-plus-circle me-2"></i>New Reservation</a>
                <a href="guests.php" class="nav-link"><i class="bi bi-people me-2"></i>Guests</a>
            </nav>
        </div>

        
        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (Front Desk)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

            <div class="p-4">

                <?php if ($success): ?>
                
                <div class="text-center py-5">
                    <div class="success-icon mb-3"><i class="bi bi-check-circle-fill"></i></div>
                    <h3 class="fw-bold text-success">Pre-Authorization Recorded!</h3>
                    <p class="text-muted">Reservation <strong>#<?= $reservation_id ?></strong> is now confirmed.</p>
                    <div class="mt-4">
                        <a href="reservation_detail.php?id=<?= $reservation_id ?>" class="btn btn-primary me-2">
                            <i class="bi bi-eye"></i> View Reservation
                        </a>
                        <a href="reservations.php" class="btn btn-outline-secondary">
                            <i class="bi bi-list"></i> All Reservations
                        </a>
                    </div>
                </div>

                <?php else: ?>

                <a href="reservations.php" class="btn btn-outline-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Back
                </a>

                <?php if ($err): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
                <?php endif; ?>

                <div class="row justify-content-center">
                    <div class="col-md-6">

                        
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <h5 class="mb-1"><?= htmlspecialchars($res['guest_name']) ?></h5>
                                <p class="text-muted mb-0">
                                    <?= htmlspecialchars($res['room_type_name']) ?> &nbsp;|&nbsp;
                                    <?= $res['check_in'] ?> → <?= $res['check_out'] ?>
                                    &nbsp;|&nbsp; <?= (int)$nights ?> night(s)
                                </p>
                            </div>
                        </div>

                       
                        <div class="card shadow-sm mb-3">
                            <div class="card-header"><strong>Bill Breakdown</strong></div>
                            <div class="card-body pb-2">
                                <div class="d-flex justify-content-between summary-row">
                                    <span>Room (<?= (int)$nights ?> night × <?= number_format($res['rate'], 2) ?> EGP)</span>
                                    <strong><?= number_format($roomTotal, 2) ?> EGP</strong>
                                </div>
                                <div class="d-flex justify-content-between summary-row text-muted">
                                    <span>VAT 14% <small>(on room only)</small></span>
                                    <span><?= number_format($vat, 2) ?> EGP</span>
                                </div>
                                <div class="d-flex justify-content-between summary-row fw-bold">
                                    <span>Grand Total</span>
                                    <span><?= number_format($grandTotal, 2) ?> EGP</span>
                                </div>
                                <div class="d-flex justify-content-between summary-row text-primary fw-bold">
                                    <span>Pre-Auth Required (20%)</span>
                                    <span><?= number_format($preAuth, 2) ?> EGP</span>
                                </div>
                            </div>
                        </div>

                        
                        <div class="card shadow-sm">
                            <div class="card-header"><strong><i class="bi bi-cash-coin me-2"></i>Record Cash Pre-Authorization</strong></div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="mb-3">
                                        <label class="form-label">Amount Received (EGP) *</label>
                                        <input type="number" name="amount" class="form-control form-control-lg"
                                               step="0.01" min="<?= $preAuth ?>"
                                               value="<?= number_format($preAuth, 2, '.', '') ?>" required>
                                        <small class="text-muted">Minimum: <?= number_format($preAuth, 2) ?> EGP</small>
                                    </div>
                                    <div class="alert alert-info small mb-3">
                                        <i class="bi bi-info-circle me-1"></i>
                                        This amount will be recorded as a <strong>pre-authorization hold</strong>.
                                        It will be deducted from the final bill at check-out.
                                    </div>
                                    <button type="submit" class="btn btn-success w-100 py-2">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Confirm Pre-Authorization
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
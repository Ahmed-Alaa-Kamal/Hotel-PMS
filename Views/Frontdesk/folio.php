<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';
require_once __DIR__ . '/../../Models/FolioCharge.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['frontdesk','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$fd = new FrontdeskController();
$folio_id = (int)($_GET['id'] ?? 0);
if ($folio_id <= 0) die('Invalid folio ID.');

$folio    = $fd->getFolio($folio_id);
if (!$folio) die('Folio not found.');

$charges  = $fd->getFolioCharges($folio_id);
$payments = $fd->getFolioPayments($folio_id);
$res      = $fd->getReservation($folio['reservation_id']);

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_charge'])) {
    $charge = new FolioCharge();
    $charge->folio_id    = $folio_id;
    $charge->description = trim($_POST['description'] ?? '');
    $charge->charge_type = $_POST['charge_type'] ?? 'other';
    $charge->amount      = (float)($_POST['amount'] ?? 0);
    $charge->qty         = (int)($_POST['qty'] ?? 1);
    $charge->posted_by   = $_SESSION['userId'] ?? null;

    if (!empty($charge->description) && $charge->amount > 0) {
        $fd->postCharge($charge);
        header("Location: folio.php?id=$folio_id");
        exit;
    } else {
        $err = 'Description and amount are required.';
    }
}

$page_title = 'Folio #' . $folio_id;
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
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>
            <div class="p-4">
                <a href="reservation_detail.php?id=<?= (int)$folio['reservation_id'] ?>" class="btn btn-outline-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Back to Reservation
                </a>

      
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5><?= htmlspecialchars($res['guest_name'] ?? 'Unknown Guest') ?></h5>
                        <p class="mb-0">Room: <?= htmlspecialchars($res['room_no'] ?? 'N/A') ?> | 
                           Dates: <?= $res['check_in'] ?? '' ?> → <?= $res['check_out'] ?? '' ?></p>
                    </div>
                </div>

                <div class="row">
    
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <strong>Charges</strong>
                                <?php if ($folio['status'] !== 'closed'): ?>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addChargeModal">
                                        <i class="bi bi-plus"></i> Add Charge
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr><th>Description</th><th>Type</th><th>Qty</th><th>Amount</th><th>Date</th></tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($charges)): ?>
                                        <tr><td colspan="5" class="text-center py-3">No charges yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($charges as $c): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($c['description']) ?></td>
                                                <td><?= htmlspecialchars($c['charge_type']) ?></td>
                                                <td><?= (int)$c['qty'] ?></td>
                                                <td><?= number_format($c['amount'], 2) ?> EGP</td>
                                                <td><?= $c['posted_at'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr><th colspan="3" class="text-end">Total Charges:</th><th><?= number_format($folio['total_charges'] ?? 0, 2) ?> EGP</th><th></th></tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header"><strong>Payments</strong></div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr><th>Method</th><th>Amount</th></tr>
                                    </thead>
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
                                        <tr><th>Total Paid:</th><th><?= number_format($folio['total_payments'] ?? 0, 2) ?> EGP</th></tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>


                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5>Balance</h5>
                                <h3 class="text-<?= ($folio['balance'] ?? 0) > 0 ? 'danger' : 'success' ?>">
                                    <?= number_format($folio['balance'] ?? 0, 2) ?> EGP
                                </h3>
                                <?php if ($folio['status'] !== 'closed'): ?>
                                    <a href="payment.php?folio_id=<?= $folio_id ?>" class="btn btn-success w-100 mt-2">
                                        <i class="bi bi-cash"></i> Record Payment
                                    </a>
                                <?php endif; ?>
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


<div class="modal fade" id="addChargeModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="folio.php?id=<?= $folio_id ?>" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Charge</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="add_charge" value="1">
        <div class="mb-3">
            <label class="form-label">Description *</label>
            <input type="text" name="description" class="form-control" required placeholder="e.g., Room service">
        </div>
        <div class="mb-3">
            <label class="form-label">Charge Type</label>
            <select name="charge_type" class="form-select">
                <option value="service">Service</option>
                <option value="minibar">Mini Bar</option>
                <option value="laundry">Laundry</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Amount *</label>
            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Qty</label>
            <input type="number" name="qty" class="form-control" value="1" min="1">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Charge</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
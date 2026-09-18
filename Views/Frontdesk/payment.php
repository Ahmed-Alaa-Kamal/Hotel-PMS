<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';
require_once __DIR__ . '/../../Models/Payment.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['frontdesk','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$fd = new FrontdeskController();
$folio_id = (int)($_GET['folio_id'] ?? 0);
if ($folio_id <= 0) die('Invalid folio ID.');

$folio    = $fd->getFolio($folio_id);
if (!$folio) die('Folio not found.');

$payments = $fd->getFolioPayments($folio_id);
$err      = '';
$success  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment = new Payment();
    $payment->folio_id   = $folio_id;
    $payment->amount     = (float)($_POST['amount'] ?? 0);
    $payment->method     = $_POST['method'] ?? 'cash';
    $payment->reference  = $_POST['reference'] ?? '';
    $payment->received_by = $_SESSION['userId'] ?? null;

    if ($payment->amount <= 0) {
        $err = 'Amount must be greater than zero.';
    } elseif ($payment->amount > $folio['balance']) {
        $err = 'Amount exceeds outstanding balance (' . number_format($folio['balance'], 2) . ' EGP).';
    } else {
        $result = $fd->recordPayment($payment);
        if ($result) {
            $success = 'Payment recorded successfully.';
            $folio    = $fd->getFolio($folio_id);
            $payments = $fd->getFolioPayments($folio_id);
        } else {
            $err = 'Payment failed.';
        }
    }
}

$page_title = 'Record Payment - Folio #' . $folio_id;
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
                <a href="folio.php?id=<?= $folio_id ?>" class="btn btn-outline-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Back to Folio
                </a>

                <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

                <div class="row">
                    
                    <div class="col-md-5">
                        <div class="card shadow-sm">
                            <div class="card-header"><strong>New Payment</strong></div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="mb-3">
                                        <label class="form-label">Amount (EGP) *</label>
                                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required
                                               placeholder="Max <?= number_format($folio['balance'] ?? 0, 2) ?>">
                                        <small class="text-muted">Outstanding: <?= number_format($folio['balance'] ?? 0, 2) ?> EGP</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Method</label>
                                        <select name="method" class="form-select">
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                            <option value="transfer">Transfer</option>
                                            <option value="points">Loyalty Points</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Reference (optional)</label>
                                        <input type="text" name="reference" class="form-control" placeholder="e.g., Invoice #">
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-cash-coin"></i> Record Payment
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-7">
                        <div class="card shadow-sm">
                            <div class="card-header"><strong>Payment History</strong></div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr><th>Method</th><th>Amount</th><th>Reference</th><th>Received</th></tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($payments)): ?>
                                        <tr><td colspan="4" class="text-center py-3">No payments recorded.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($payments as $p): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($p['method']) ?></td>
                                                <td><?= number_format($p['amount'], 2) ?> EGP</td>
                                                <td><?= htmlspecialchars($p['reference'] ?? '') ?></td>
                                                <td><?= $p['received_at'] ?></td>
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
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
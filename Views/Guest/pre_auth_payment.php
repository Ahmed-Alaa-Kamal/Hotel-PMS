<?php
require_once __DIR__ . '/../../Controllers/GuestController.php';
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';
require_once __DIR__ . '/../../Models/Payment.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'guest') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$gc = new GuestController();
$fd = new FrontdeskController();

$guest          = $gc->getGuestByUserId($_SESSION['userId']);
if (!$guest) die('Guest profile not found.');

$reservation_id = (int)($_GET['reservation_id'] ?? 0);
if ($reservation_id <= 0) die('Invalid request.');

$reservation = $fd->getReservation($reservation_id);
if (!$reservation) die('Reservation not found.');


if ((int)$reservation['guest_id'] !== (int)$guest['id']) die('Unauthorized.');


$nights     = max(1, (strtotime($reservation['check_out']) - strtotime($reservation['check_in'])) / 86400);
$roomTotal  = (float)$reservation['rate'] * $nights;
$vat        = round($roomTotal * 0.14, 2);
$grandTotal = $roomTotal + $vat;
$amount     = round($grandTotal * 0.20, 2);

$booked = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $fd->confirmReservation($reservation_id);
    
    $fd->updateReservationStatus($reservation_id, 'booked');

   
    $folio = $fd->getFolioByReservation($reservation_id);
    if ($folio) {
        $payment = new \Payment();
        $payment->folio_id   = (int)$folio['id'];
        $payment->amount     = $amount;
        $payment->method     = 'card';
        $payment->reference  = 'Pre‑Authorization Hold (20%)';
        $payment->received_by = $guest['id'] ?? null;
        $fd->recordPayment($payment);
    }

    $booked = true;
    $reservation = $fd->getReservation($reservation_id);
}

$page_title = 'Secure Payment';
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
        .card-icon { font-size: 2rem; }
        .success-icon { font-size: 5rem; color: #28a745; }
        .lock-badge { font-size: .75rem; color: #6c757d; }
    </style>
</head>
<body>
<div class="container-fluid g-0">
    <div class="row g-0">

        <div class="col-md-2 sidebar p-3">
            <h5 class="text-white mb-4"><i class="bi bi-person"></i> <?= htmlspecialchars($guest['full_name']) ?></h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="reserv.php" class="nav-link active"><i class="bi bi-plus-circle me-2"></i>New Reservation</a>
                <a href="reservations.php" class="nav-link"><i class="bi bi-calendar me-2"></i>My Reservations</a>
                <a href="profile.php" class="nav-link"><i class="bi bi-person-circle me-2"></i>Profile</a>
                <a href="feedback.php" class="nav-link"><i class="bi bi-star me-2"></i>Feedback</a>
            </nav>
        </div>

  
        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-lock-fill text-success me-1"></i> Secure Payment</span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (Guest)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

            <div class="p-4">
                <?php if ($booked): ?>
          
                <div class="text-center py-5">
                    <div class="success-icon mb-3"><i class="bi bi-check-circle-fill"></i></div>
                    <h3 class="fw-bold text-success">Reservation Confirmed!</h3>
                    <p class="text-muted mt-2">
                        Your reservation <strong>#<?= $reservation_id ?></strong> has been successfully booked.
                    </p>
                    <div class="card shadow-sm w-50 mx-auto mt-4 text-start">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Booking Summary</h6>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Room Type</span>
                                <strong><?= htmlspecialchars($reservation['room_type_name']) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Check-in</span>
                                <strong><?= $reservation['check_in'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Check-out</span>
                                <strong><?= $reservation['check_out'] ?></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Pre-Authorization Hold</span>
                                <strong class="text-primary"><?= number_format($amount, 2) ?> EGP</strong>
                            </div>
                            <p class="text-muted small mt-2 mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                This amount is a temporary hold (20% of total) and will be released upon check-out.
                                It is <strong>not</strong> a charge.
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="reservations.php" class="btn btn-primary me-2">
                            <i class="bi bi-calendar"></i> My Reservations
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </div>
                </div>

                <?php else: ?>
                
                <div class="w-50 mx-auto">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Booking Summary</h6>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Reservation</span>
                                <strong>#<?= $reservation_id ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Room Type</span>
                                <strong><?= htmlspecialchars($reservation['room_type_name']) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Dates</span>
                                <strong><?= $reservation['check_in'] ?> → <?= $reservation['check_out'] ?></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Pre-Authorization Amount</span>
                                <span class="fw-bold text-primary fs-5"><?= number_format($amount, 2) ?> EGP</span>
                            </div>
                            <p class="text-muted small mt-2 mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                This is a temporary hold of 20% of your total bill. It will be released upon check-out.
                            </p>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong><i class="bi bi-credit-card me-2"></i>Card Details</strong>
                            <span class="lock-badge"><i class="bi bi-lock-fill me-1"></i>SSL Secured</span>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Cardholder Name</label>
                                    <input type="text" class="form-control" placeholder="Name on card"
                                           value="<?= htmlspecialchars($guest['full_name']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" class="form-control" placeholder="1234 5678 9012 3456"
                                           maxlength="19" id="cardNumber">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">CVV</label>
                                        <input type="password" class="form-control" placeholder="***" maxlength="3">
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-2 mb-3 text-muted" style="font-size:1.5rem;">
                                    <i class="bi bi-credit-card-2-front"></i>
                                    <i class="bi bi-credit-card"></i>
                                    <i class="bi bi-wallet2"></i>
                                </div>

                                <button type="submit" class="btn btn-success w-100 py-2">
                                    <i class="bi bi-lock-fill me-2"></i>
                                    Confirm & Book — <?= number_format($amount, 2) ?> EGP Hold
                                </button>
                                <p class="text-center text-muted small mt-2">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Your card details are encrypted and never stored.
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// تنسيق رقم الكارت تلقائي
document.getElementById('cardNumber')?.addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 16);
    this.value = v.replace(/(.{4})/g, '$1 ').trim();
});
</script>
</body>
</html>
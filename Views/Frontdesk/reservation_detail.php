<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['frontdesk','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$fd  = new FrontdeskController();
$id  = (int)($_GET['id'] ?? 0);
if ($id <= 0) { die('Invalid reservation ID.'); }

$res = $fd->getReservation($id);
if (!$res) { die('Reservation not found.'); }

$folio = $fd->getFolioByReservation($id);


$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['checkin'])) {
        $result = $fd->checkIn($id);
        if ($result === true) {
            $message = 'Guest checked in successfully.';
        } else {
            $error = 'Check‑in failed. Room may not be ready.';
        }
    } elseif (isset($_POST['checkout'])) {
        $result = $fd->checkOut($id);
        if ($result === true) {
            $message = 'Guest checked out successfully.';
        } elseif ($result === 'unpaid') {
            $error = 'Folio has outstanding balance. Please settle before check‑out.';
        } else {
            $error = 'Check‑out failed.';
        }
    } elseif (isset($_POST['cancel'])) {
        $reason = $_POST['cancel_reason'] ?? 'Cancelled by front desk';
        if ($fd->cancelReservation($id, $reason)) {
            $message = 'Reservation cancelled.';
        } else {
            $error = 'Cancellation failed. Reservation may already be checked in.';
        }
    } elseif (isset($_POST['no_show'])) {
        $result = $fd->markNoShow($id);
        if ($result === true) {
            $message = 'Reservation marked as No‑Show. Room released and penalty charged.';
        } else {
            $error = 'Marking No‑Show failed. Reservation may not be in booked status.';
        }
    }

    
    $res   = $fd->getReservation($id);
    $folio = $fd->getFolioByReservation($id);
}

$page_title = 'Reservation #' . $id;
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
                <a href="reservations.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Reservations</a>

                <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4><?= htmlspecialchars($res['guest_name']) ?></h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Room:</strong> <?= htmlspecialchars($res['room_no'] ?? 'Not assigned') ?> (<?= htmlspecialchars($res['room_type_name']) ?>)
                            </div>
                            <div class="col-md-4">
                                <strong>Check‑in:</strong> <?= $res['check_in'] ?><br>
                                <strong>Check‑out:</strong> <?= $res['check_out'] ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Status:</strong>
                                <span class="badge bg-<?= $res['status'] === 'checked_in' ? 'success' : ($res['status'] === 'cancelled' ? 'danger' : 'primary') ?>">
                                    <?= htmlspecialchars($res['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <?php if ($res['status'] === 'booked'): ?>
                                <form method="post" class="d-inline">
                                    <button type="submit" name="checkin" class="btn btn-success" onclick="return confirm('Check‑in this guest?')"><i class="bi bi-box-arrow-in-right"></i> Check‑In</button>
                                </form>
                                <form method="post" class="d-inline" onsubmit="return confirm('Mark this reservation as No‑Show? This may incur a penalty fee.');">
                                    <input type="hidden" name="no_show" value="1">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-person-x"></i> No‑Show</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($res['status'] === 'checked_in'): ?>
                                <form method="post" class="d-inline">
                                    <button type="submit" name="checkout" class="btn btn-warning" onclick="return confirm('Check‑out this guest?')"><i class="bi bi-box-arrow-right"></i> Check‑Out</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($folio): ?>
                                <a href="folio.php?id=<?= (int)$folio['id'] ?>" class="btn btn-info"><i class="bi bi-receipt"></i> View Folio</a>
                            <?php endif; ?>
                            <?php if (in_array($res['status'], ['booked','checked_in'])): ?>
                                <a href="cancel.php?id=<?= (int)$res['id'] ?>" class="btn btn-outline-danger">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <?php if ($folio): ?>
                <div class="card shadow-sm">
                    <div class="card-header"><strong>Folio #<?= (int)$folio['id'] ?></strong></div>
                    <div class="card-body">
                        <p>Total Charges: <strong><?= number_format($folio['total_charges'] ?? 0, 2) ?> EGP</strong></p>
                        <p>Total Payments: <strong><?= number_format($folio['total_payments'] ?? 0, 2) ?> EGP</strong></p>
                        <p>Balance: <strong class="text-danger"><?= number_format($folio['balance'] ?? 0, 2) ?> EGP</strong></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="cancelModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cancel Reservation #<?= $id ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Reason (optional)</label>
        <input type="text" name="cancel_reason" class="form-control" placeholder="Cancellation reason">
      </div>
      <div class="modal-footer">
        <button type="submit" name="cancel" class="btn btn-danger">Confirm Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
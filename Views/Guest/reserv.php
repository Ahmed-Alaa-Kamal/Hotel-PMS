<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';
require_once __DIR__ . '/../../Controllers/GuestController.php';
require_once __DIR__ . '/../../Models/Reservation.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'guest') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$gc = new GuestController();
$guest = $gc->getGuestByUserId($_SESSION['userId']);
if (!$guest) die('Guest profile not found. Please contact front desk.');


$isBlacklisted = !empty($guest['blacklisted']);
$banReason     = $guest['ban_reason'] ?? '';

$fd      = new FrontdeskController();
$types   = $fd->listRoomTypes();
$err     = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation = new Reservation();
    $reservation->guest_id = (int)$guest['id'];
    $reservation->room_type_id = (int)($_POST['room_type_id'] ?? 0);
    $reservation->check_in     = $_POST['check_in'] ?? '';
    $reservation->check_out    = $_POST['check_out'] ?? '';
    $reservation->adults       = (int)($_POST['adults'] ?? 1);
    $reservation->children     = (int)($_POST['children'] ?? 0);
    $reservation->source       = 'website';

    if ($reservation->room_type_id <= 0 || empty($reservation->check_in) || empty($reservation->check_out)) {
        $err = 'Please fill all required fields.';
    } elseif ($reservation->check_in < date('Y-m-d')) {
        $err = 'Check-in date cannot be in the past.';
    } elseif ($reservation->check_out <= $reservation->check_in) {
        $err = 'Check-out must be after check-in.';
    } else {
        
            if ($isBlacklisted) {
                $reservation->status = 'pending_blacklist';
            } else {
                $reservation->status = 'pending_payment';  
            }

            $reservationId = $fd->createReservation($reservation);
            if ($reservationId) {
                if ($isBlacklisted) {
                    $success = 'Your reservation request has been submitted for management approval.';
                } else {
                    
                    header('Location: pre_auth_payment.php?reservation_id=' . $reservationId);
                    exit;
                }

        } else {
            $err = 'Failed to create reservation. No rooms available for this period.';
        }
    }
}

$page_title = 'New Reservation';
$active = 'reserve';
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
                <a href="reserv.php" class="nav-link active"><i class="bi bi-plus-circle me-2"></i>New Reservation</a>
                <a href="reservations.php" class="nav-link"><i class="bi bi-calendar me-2"></i>My Reservations</a>
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
                <?php if ($err): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($isBlacklisted && !$success): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Account Restricted:</strong> <?= htmlspecialchars($banReason ?: 'No reason provided') ?>. Your reservation will require manager approval.
                </div>
                <?php endif; ?>

                <div class="card shadow-sm w-50 mx-auto">
                    <div class="card-header"><strong>Book a Room</strong></div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Room Type *</label>
                                <select name="room_type_id" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($types as $t): ?>
                                        <option value="<?= (int)$t['id'] ?>">
                                            <?= htmlspecialchars($t['name']) ?> (<?= number_format($t['base_price'], 2) ?> EGP/night)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Check-in *</label>
                                    <input type="date" name="check_in" class="form-control" required min="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Check-out *</label>
                                    <input type="date" name="check_out" class="form-control" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Adults</label>
                                    <input type="number" name="adults" class="form-control" value="1" min="1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Children</label>
                                    <input type="number" name="children" class="form-control" value="0" min="0">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <?= $isBlacklisted ? 'Submit for Approval' : 'Create Reservation' ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
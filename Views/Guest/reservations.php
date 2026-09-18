<?php
require_once __DIR__ . '/../../Controllers/GuestController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'guest') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$gc = new GuestController();
$guest = $gc->getGuestByUserId($_SESSION['userId']);
$reservations = [];
if ($guest) {
    $reservations = $gc->getMyReservations($guest['id']);
}

$page_title = 'My Reservations';
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
            <h5 class="text-white mb-4"><i class="bi bi-person"></i> <?= htmlspecialchars($guest['full_name']) ?></h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="reserv.php" class="nav-link"><i class="bi bi-plus-circle me-2"></i>New Reservation</a>
                <a href="reservations.php" class="nav-link active"><i class="bi bi-calendar me-2"></i>My Reservations</a>
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
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Room</th>
                                    <th>Type</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($reservations)): ?>
                                <tr><td colspan="7" class="text-center py-4">No reservations found. <a href="reserve.php">Book your first stay</a>.</td></tr>
                            <?php else: ?>
                                <?php foreach ($reservations as $r): ?>
                                <?php
                                    $folioForRes = $gc->getMyFolioForReservation($r['id']);
                                ?>
                                <tr>
                                    <td>#<?= (int)$r['id'] ?></td>
                                    <td><?= htmlspecialchars($r['room_no'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($r['room_type_name']) ?></td>
                                    <td><?= $r['check_in'] ?></td>
                                    <td><?= $r['check_out'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $r['status'] === 'booked' ? 'primary' : 
                                            ($r['status'] === 'pending_blacklist' ? 'warning' : 
                                            ($r['status'] === 'cancelled' ? 'danger' : 
                                            ($r['status'] === 'checked_in' ? 'success' : 'info'))) 
                                        ?>">
                                            <?= htmlspecialchars($r['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($r['status'] === 'pending_payment'): ?>
                                            <a href="pre_auth_payment.php?reservation_id=<?= (int)$r['id'] ?>" 
                                            class="btn btn-sm btn-success">
                                                <i class="bi bi-credit-card"></i> Complete Booking
                                            </a>
                                        <?php elseif ($folioForRes): ?>
                                            <a href="folio.php?reservation_id=<?= (int)$r['id'] ?>" 
                                            class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-receipt"></i> View Folio
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
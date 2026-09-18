<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['frontdesk','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$fd     = new FrontdeskController();
$status = $_GET['status'] ?? null;  
$rows   = $fd->listReservations($status);

$page_title = $status ? 'Reservations : ' . htmlspecialchars($status) : 'All Reservations';
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <a href="reservations.php" class="btn btn-outline-secondary btn-sm me-2 <?= !$status ? 'active' : '' ?>">All</a>
                        <a href="reservations.php?status=booked" class="btn btn-outline-primary btn-sm me-2 <?= $status === 'booked' ? 'active' : '' ?>">Booked</a>
                        <a href="reservations.php?status=checked_in" class="btn btn-outline-success btn-sm me-2 <?= $status === 'checked_in' ? 'active' : '' ?>">Checked‑In</a>
                        <a href="reservations.php?status=checked_out" class="btn btn-outline-info btn-sm <?= $status === 'checked_out' ? 'active' : '' ?>">Checked‑Out</a>
                        <a href="reservations.php?status=pending_payment" class="btn btn-outline-warning btn-sm me-2 <?= $status === 'pending_payment' ? 'active' : '' ?>">Pending Payment</a>
                    </div>
                    <a href="new_reservation.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Reservation</a>
                </div>

                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Type</th>
                                    <th>Check‑in</th>
                                    <th>Check‑out</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($rows)): ?>
                                <tr><td colspan="8" class="text-center py-4">No reservations found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td>#<?= (int)$r['id'] ?></td>
                                        <td>
                                            <?= htmlspecialchars($r['guest_name']) ?>
                                            <?php if ($r['vip'] == 1 || in_array($r['loyalty_tier'], ['gold','platinum'])): ?>
                                                <span class="badge bg-warning text-dark ms-1">⭐ VIP</span>
                                            <?php endif; ?>
                                        </td>                                        <td><?= htmlspecialchars($r['room_no'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($r['room_type_name']) ?></td>
                                        <td><?= $r['check_in'] ?></td>
                                        <td><?= $r['check_out'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= $r['status'] === 'checked_in' ? 'success' : ($r['status'] === 'cancelled' ? 'danger' : ($r['status'] === 'checked_out' ? 'info' : 'primary')) ?>">
                                                <?= htmlspecialchars($r['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="reservation_detail.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
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
<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['frontdesk','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$fd = new FrontdeskController();
$today = $fd->listTodayArrivalsAndDepartures();
$reservations = $fd->listReservations('booked'); 
$checkedIn = $fd->listReservations('checked_in'); 

$db = DBController::getInstance();
$notifData = $db->getNotifications($_SESSION['userId']);
$notifCount = $notifData['count'];
$notifItems = $notifData['items'];

$page_title = 'Front Desk Dashboard';
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
            <h5 class="text-white mb-4"><i class="bi bi-building"></i> Hotel PMS</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="reservations.php" class="nav-link"><i class="bi bi-calendar-check me-2"></i>Reservations</a>
                <a href="new_reservation.php" class="nav-link"><i class="bi bi-plus-circle me-2"></i>New Reservation</a>
                <a href="guests.php" class="nav-link"><i class="bi bi-people me-2"></i>Guests</a>
            </nav>
        </div>

        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (Front Desk)</span>
                
<ul class="navbar-nav me-2">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell" style="font-size:1.2rem;"></i>
            <span id="notif-count" class="badge rounded-pill bg-danger" 
                  style="display: <?= $notifCount > 0 ? 'inline-block' : 'none' ?>;">
                <?= $notifCount ?>
            </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 320px;" aria-labelledby="notifDropdown">
            <?php if (empty($notifItems)): ?>
                <li><span class="dropdown-item text-muted">No notifications</span></li>
            <?php else: ?>
                <?php foreach ($notifItems as $n): ?>
                    <?php $isUnread = (int)($n['is_read'] ?? 0) === 0; ?>
                    <li>
                        <a class="dropdown-item <?= $isUnread ? 'fw-bold' : '' ?>" 
                        href="/hotel_pms/Views/Auth/mark_notification_read.php?id=<?= $n['id'] ?>&redirect=<?= urlencode($n['link'] ?? '#') ?>">
                            <div><?= htmlspecialchars($n['message']) ?></div>
                            <small class="text-muted"><?= $n['created_at'] ?></small>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </li>
</ul>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>
            <div class="p-4">
                
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card text-bg-primary p-3 text-center">
                            <h5><?= count($today['arrivals']) ?></h5>
                            <small>Today's Arrivals</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-bg-warning p-3 text-center">
                            <h5><?= count($today['departures']) ?></h5>
                            <small>Today's Departures</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-bg-success p-3 text-center">
                            <h5><?= count($checkedIn) ?></h5>
                            <small>Currently In-House</small>
                        </div>
                    </div>
                </div>

               
                <h4>Today's Arrivals</h4>
                <div class="card shadow-sm mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr><th>ID</th><th>Guest</th><th>Room</th><th>Type</th><th>Check Out</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                            <?php if (empty($today['arrivals'])): ?>
                                <tr><td colspan="6" class="text-center py-3">No arrivals today.</td></tr>
                            <?php else: ?>
                                <?php foreach ($today['arrivals'] as $r): ?>
                                    <tr>
                                        <td>#<?= (int)$r['id'] ?></td>
                                        <td><?= htmlspecialchars($r['guest_name']) ?></td>
                                        <td><?= htmlspecialchars($r['room_no'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($r['room_type_name']) ?></td>
                                        <td><?= $r['check_out'] ?></td>
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

                
                <h4>Today's Departures</h4>
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr><th>ID</th><th>Guest</th><th>Room</th><th>Status</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                            <?php if (empty($today['departures'])): ?>
                                <tr><td colspan="5" class="text-center py-3">No departures today.</td></tr>
                            <?php else: ?>
                                <?php foreach ($today['departures'] as $r): ?>
                                    <tr>
                                        <td>#<?= (int)$r['id'] ?></td>
                                        <td><?= htmlspecialchars($r['guest_name']) ?></td>
                                        <td><?= htmlspecialchars($r['room_no']) ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($r['status']) ?></span></td>
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
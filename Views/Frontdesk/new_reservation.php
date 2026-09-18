<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';
require_once __DIR__ . '/../../Models/Reservation.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['frontdesk','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$fd      = new FrontdeskController();
$guests  = $fd->listGuests();
$types   = $fd->listRoomTypes();
$err     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = new Reservation();
    $res->guest_id     = (int)($_POST['guest_id'] ?? 0);
    $res->room_type_id = (int)($_POST['room_type_id'] ?? 0);
    $res->check_in     = $_POST['check_in'] ?? '';
    $res->check_out    = $_POST['check_out'] ?? '';
    $res->adults       = (int)($_POST['adults'] ?? 1);
    $res->children     = (int)($_POST['children'] ?? 0);
    $res->source       = 'walk_in';

    if ($res->guest_id <= 0 || $res->room_type_id <= 0 || empty($res->check_in) || empty($res->check_out)) {
        $err = 'Please fill all required fields.';
    } elseif ($res->check_in < date('Y-m-d')) {
        $err = 'Check-in date cannot be in the past.';
    } elseif ($res->check_out <= $res->check_in) {
        $err = 'Check-out must be after check-in.';
    } else {
        $reservation_id = $fd->createReservation($res);
        if ($reservation_id) {
            
            header('Location: pre_auth_cash.php?reservation_id=' . $reservation_id);
            exit;
        } else {
            $err = 'Failed to create reservation. Please check room availability.';
        }
    }
}

$page_title = 'New Reservation';
$active = 'new_reservation';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <a href="reservations.php" class="nav-link"><i class="bi bi-calendar-check me-2"></i>Reservations</a>
                <a href="new_reservation.php" class="nav-link active"><i class="bi bi-plus-circle me-2"></i>New Reservation</a>
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
                <div class="card shadow-sm w-50 mx-auto">
                    <div class="card-body">
                        <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back</a>
                        <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Guest *</label>
                                <select name="guest_id" class="form-select" required>
                                    <option value="">-- Select Guest --</option>
                                    <?php foreach ($guests as $g): ?>
                                        <option value="<?= (int)$g['id'] ?>">  
                                            <?= htmlspecialchars($g['full_name']) ?> (<?= $g['phone'] ?? '' ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Room Type *</label>
                                <select name="room_type_id" class="form-select" required>
                                    <option value="">-- Select Room Type --</option>
                                    <?php foreach ($types as $t): ?>
                                        <option value="<?= (int)$t['id'] ?>"
                                            <?= (isset($_POST['room_type_id']) && (int)$_POST['room_type_id'] === (int)$t['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['name']) ?> (<?= number_format($t['base_price'], 2) ?> EGP / night)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Check-in *</label>
                                    <input type="date" name="check_in" class="form-control" required
                                           min="<?= date('Y-m-d') ?>"
                                           value="<?= htmlspecialchars($_POST['check_in'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Check-out *</label>
                                    <input type="date" name="check_out" class="form-control" required
                                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                           value="<?= htmlspecialchars($_POST['check_out'] ?? '') ?>">
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
                                <i class="bi bi-calendar-plus"></i> Create Reservation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
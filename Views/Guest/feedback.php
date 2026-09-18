<?php
require_once __DIR__ . '/../../Controllers/DBController.php';
require_once __DIR__ . '/../../Controllers/GuestController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'guest') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$gc = new GuestController();
$guest = $gc->getGuestByUserId($_SESSION['userId']);
$message = '';

if (isset($_POST['submit_feedback'])) {
    $db = DBController::getInstance();
    $rating  = (int)$_POST['rating'];
    $comment = $db->escape($_POST['comment'] ?? '');
    $reservation_id = (int)$_POST['reservation_id'];
    $db->insert("INSERT INTO feedback (guest_id, reservation_id, rating, comment) VALUES ({$guest['id']}, $reservation_id, $rating, '$comment')");
    $message = 'Thank you for your feedback!';
}

$reservations = $gc->getMyReservations($guest['id']);

$page_title = $guest['full_name'] . ' - Feedback';
$active = 'feedback';
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
                <a href="reserv.php" class="nav-link "><i class="bi bi-plus-circle me-2"></i>New Reservation</a>
                <a href="reservations.php" class="nav-link"><i class="bi bi-calendar me-2"></i>My Reservations</a>
                <a href="profile.php" class="nav-link"><i class="bi bi-person-circle me-2"></i>Profile</a>
                <a href="feedback.php" class="nav-link active"><i class="bi bi-star me-2"></i>Feedback</a>
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
                <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
                <div class="card shadow-sm w-50 mx-auto">
                    <div class="card-body">
                        <h5>Leave a review</h5>
                        <form method="post">
                            <div class="mb-3">
                                <label>Reservation</label>
                                <select name="reservation_id" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($reservations as $r): ?>
                                        <option value="<?= (int)$r['id'] ?>">#<?= $r['id'] ?> - Room <?= htmlspecialchars($r['room_no']??'?') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Rating (1-5)</label>
                                <input type="number" name="rating" class="form-control" min="1" max="5" required>
                            </div>
                            <div class="mb-3">
                                <label>Comment</label>
                                <textarea name="comment" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" name="submit_feedback" class="btn btn-primary w-100">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
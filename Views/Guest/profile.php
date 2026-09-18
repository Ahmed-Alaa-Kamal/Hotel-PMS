<?php
require_once __DIR__ . '/../../Controllers/GuestController.php';
require_once __DIR__ . '/../../Models/Guest.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'guest') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$gc = new GuestController();
$guest = $gc->getGuestByUserId($_SESSION['userId']);
if (!$guest) die('Guest profile not found.');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guestObj = new Guest();
    $guestObj->id          = $guest['id'];   
    $guestObj->full_name   = $_POST['full_name'] ?? '';
    $guestObj->email       = $_POST['email'] ?? '';
    $guestObj->phone       = $_POST['phone'] ?? '';
    $guestObj->doc_type    = $_POST['doc_type'] ?? '';
    $guestObj->doc_number  = $_POST['doc_number'] ?? '';
    $guestObj->nationality = $_POST['nationality'] ?? '';
    $guestObj->preferences = $_POST['preferences'] ?? '';

    if ($gc->updateProfile($guestObj)) {
        $message = 'Profile updated successfully.';
        $guest = $gc->getGuestByUserId($_SESSION['userId']); // تحديث
    } else {
        $message = 'Update failed.';
    }
}

$page_title = 'My Profile';
$active = 'profile';
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
                <a href="profile.php" class="nav-link active"><i class="bi bi-person-circle me-2"></i>Profile</a>
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
                <div class="card shadow-sm w-50 mx-auto">
                    <div class="card-body">
                        <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                        <form method="post">
                            <div class="mb-3"><label>Full Name</label><input name="full_name" class="form-control" value="<?= htmlspecialchars($guest['full_name']) ?>" required></div>
                            <div class="mb-3"><label>Email</label><input name="email" class="form-control" value="<?= htmlspecialchars($guest['email'] ?? '') ?>"></div>
                            <div class="mb-3"><label>Phone</label><input name="phone" class="form-control" value="<?= htmlspecialchars($guest['phone'] ?? '') ?>"></div>
                            <div class="mb-3"><label>Document Type</label>
                                <select name="doc_type" class="form-select">
                                    <option value="">--</option>
                                    <option value="passport" <?= ($guest['doc_type']??'')=='passport'?'selected':'' ?>>Passport</option>
                                    <option value="id_card" <?= ($guest['doc_type']??'')=='id_card'?'selected':'' ?>>ID Card</option>
                                </select>
                            </div>
                            <div class="mb-3"><label>Document Number</label><input name="doc_number" class="form-control" value="<?= htmlspecialchars($guest['doc_number'] ?? '') ?>"></div>
                            <div class="mb-3"><label>Nationality</label><input name="nationality" class="form-control" value="<?= htmlspecialchars($guest['nationality'] ?? '') ?>"></div>
                            <div class="mb-3"><label>Preferences</label><textarea name="preferences" class="form-control" rows="2"><?= htmlspecialchars($guest['preferences'] ?? '') ?></textarea></div>
                            <button class="btn btn-primary w-100">Update Profile</button>
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
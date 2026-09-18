<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['frontdesk','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$fd = new FrontdeskController();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { die('Invalid reservation ID.'); }

$res = $fd->getReservation($id);
if (!$res) { die('Reservation not found.'); }

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = $_POST['cancel_reason'] ?? 'Cancelled by front desk';
    if ($fd->cancelReservation($id, $reason)) {
        $success = "Reservation #$id has been cancelled.";
    } else {
        $error = 'Cancellation failed. Reservation may already be checked in or cancelled.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Reservation #<?= $id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-body">
                <a href="reservation_detail.php?id=<?= $id ?>" class="btn btn-outline-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Back
                </a>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <a href="reservation_detail.php?id=<?= $id ?>" class="btn btn-primary">Back to Reservation</a>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <a href="reservation_detail.php?id=<?= $id ?>" class="btn btn-primary">Back</a>
                <?php else: ?>
                    <h4 class="mb-3">Cancel Reservation #<?= $id ?></h4>
                    <p><strong>Guest:</strong> <?= htmlspecialchars($res['guest_name']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($res['status']) ?></p>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Reason (optional)</label>
                            <input type="text" name="cancel_reason" class="form-control" placeholder="Why is this being cancelled?">
                        </div>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle"></i> Confirm Cancel</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
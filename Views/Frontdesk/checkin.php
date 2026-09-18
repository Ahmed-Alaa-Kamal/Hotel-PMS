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

$errorMessage = '';
$result = $fd->checkIn($id, $errorMessage);

if ($result === true) {
    $message = 'Guest checked in successfully. Room is now occupied.';
} else {
    $message = $errorMessage ?: 'Check‑in failed. Room may not be ready.';
}

header("refresh:3;url=reservation_detail.php?id=$id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Check‑In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow text-center p-5">
            <h3><?= $result === true ? '✅ Check‑In Successful' : '❌ Check‑In Failed' ?></h3>
            <p class="mt-3"><?= htmlspecialchars($message) ?></p>
            <a href="reservation_detail.php?id=<?= $id ?>" class="btn btn-primary mt-3">Back to Reservation</a>
        </div>
    </div>
</body>
</html>
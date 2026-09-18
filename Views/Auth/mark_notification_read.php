<?php

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$notifId  = (int)($_GET['id'] ?? 0);
$redirect = $_GET['redirect'] ?? '/hotel_pms/Views/HkSup/index.php'; // مسار افتراضي

if ($notifId > 0) {
    require_once __DIR__ . '/../../Controllers/DBController.php';
    $db = DBController::getInstance();
    $db->update("UPDATE notifications SET is_read = 1 WHERE id = $notifId AND user_id = " . (int)$_SESSION['userId']);
}

header('Location: ' . $redirect);
exit;
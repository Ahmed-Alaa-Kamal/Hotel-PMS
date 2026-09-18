<?php

session_start();
if (!isset($_SESSION['userId'])) {
    echo '0';
    exit;
}
require_once __DIR__ . '/../../Controllers/DBController.php';
$db = DBController::getInstance();
$uid = (int)$_SESSION['userId'];
$row = $db->selectOne("SELECT COUNT(*) AS c FROM notifications WHERE user_id = $uid AND is_read = 0");
echo $row['c'] ?? 0;
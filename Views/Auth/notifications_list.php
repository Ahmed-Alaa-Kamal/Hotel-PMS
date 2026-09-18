<?php

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userId'])) {
    echo '<li><span class="dropdown-item text-muted">No notifications</span></li>';
    exit;
}
require_once __DIR__ . '/../../Controllers/DBController.php';
$db = DBController::getInstance();
$uid = (int)$_SESSION['userId'];
$items = $db->select("SELECT * FROM notifications WHERE user_id = $uid ORDER BY created_at DESC LIMIT 5");

if (empty($items)) {
    echo '<li><span class="dropdown-item text-muted">No notifications</span></li>';
} else {
    foreach ($items as $n) {
        $isUnread = (int)($n['is_read'] ?? 0) === 0;
        $bold = $isUnread ? 'fw-bold' : '';
        $link = htmlspecialchars($n['link'] ?? '#');
        $msg = htmlspecialchars($n['message']);
        $date = htmlspecialchars($n['created_at']);
        $url = "/hotel_pms/Views/Auth/mark_notification_read.php?id={$n['id']}&redirect=" . urlencode($n['link'] ?? '#');
        echo "<li><a class='dropdown-item $bold' href='$url'><div>$msg</div><small class='text-muted'>$date</small></a></li>";
    }
}
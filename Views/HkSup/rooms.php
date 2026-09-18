<?php
$allowed = ['hksup','admin','manager']; include __DIR__ . '/../_partials/guard.php';
require_once __DIR__ . '/../../Controllers/HkSupController.php';
$rooms = (new HkSupController())->listRooms();
?>
<h1>Room Status Board</h1>
<div class="card"><table>
<tr><th>Room</th><th>Floor</th><th>Status</th><th>Notes</th></tr>
<?php foreach ($rooms as $r) { ?>
<tr><td><?= $r['room_no'] ?></td><td><?= $r['floor'] ?></td>
<td><span class="badge b-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
<td><?= htmlspecialchars($r['notes']) ?></td></tr>
<?php } ?></table></div>

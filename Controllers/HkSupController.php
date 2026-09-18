<?php
require_once __DIR__ . '/DBController.php';
require_once __DIR__ . '/../Models/HkTask.php';
require_once __DIR__ . '/../Models/Maintenance.php';
require_once __DIR__ . '/../Models/Notification.php';
class HkSupController {
    private $db;

    public function __construct()
    {
        $this->db = DBController::getInstance();
    }

    public function listAllTasks($status) {
        $sql = "SELECT t.*, r.room_no, r.floor, u.full_name AS assignee
                FROM hk_tasks t
                JOIN rooms r ON r.id = t.room_id
                LEFT JOIN users u ON u.id = t.assigned_to";
        if ($status !== '' && $status !== null && $status !== false) {
            $st = $this->db->escape($status);
            return $this->db->select($sql . " WHERE t.status = '$st' ORDER BY t.id DESC");
        }
        return $this->db->select($sql . " ORDER BY t.id DESC LIMIT 200");
    }

    public function assignTask($task_id, $hk_user_id) {
        $tid = (int)$task_id;
        $uid = (int)$hk_user_id;
        $this->db->update(
            "UPDATE hk_tasks SET assigned_to = $uid, status = 'assigned' WHERE id = $tid"
        );
        return true;
    }

    public function createTask(HkTask $task): int|false
    {
        $rid  = (int)$task->room_id;
        $type = $this->db->escape($task->task_type);
        $prio = $this->db->escape($task->priority);
        $nt   = $this->db->escape($task->notes ?? '');

        $aid = ($task->assigned_to !== null && $task->assigned_to !== '') ? (int)$task->assigned_to : 'null';
        $status = ($aid === 'null') ? 'pending' : 'assigned';

        return $this->db->insert(
            "INSERT INTO hk_tasks (room_id, task_type, priority, assigned_to, notes, status) 
             VALUES ($rid, '$type', '$prio', $aid, '$nt', '$status')"
        );
    }

public function inspectAndApprove($task_id, $supervisor_id) {
    $tid = (int)$task_id;
    $sid = (int)$supervisor_id;
    $t = $this->db->selectOne("SELECT * FROM hk_tasks WHERE id = $tid");
    if ($t === null || $t['status'] !== 'done') return false;

    $this->db->update(
        "UPDATE hk_tasks SET status = 'inspected', inspected_at = NOW(), inspected_by = $sid WHERE id = $tid"
    );
    $this->db->update("UPDATE rooms SET status = 'clean' WHERE id = " . (int)$t['room_id']);
    $this->deductMinibarForTask($task_id);

  
    $room = $this->db->selectOne("SELECT room_no FROM rooms WHERE id = " . (int)$t['room_id']);
    $roomNo = $room['room_no'] ?? 'N/A';
    $frontdeskUsers = $this->db->select("SELECT id FROM users WHERE role = 'frontdesk' AND active = 1");
    foreach ($frontdeskUsers as $fd) {
        $notif = new Notification();
        $notif->user_id = (int)$fd['id'];
        $notif->message = "Room {$roomNo} is now ready for check‑in.";
        $notif->link = "/hotel_pms/Views/Frontdesk/reservations.php"; // أو رابط الغرفة نفسها
        $this->db->addNotification($notif);
    }

    return true;
}

    public function inspectAndReject($task_id, $supervisor_id, $notes) {
        $tid = (int)$task_id;
        $sid = (int)$supervisor_id;
        $t = $this->db->selectOne("SELECT * FROM hk_tasks WHERE id = $tid");
        if ($t === null || $t['status'] !== 'done') return false;

        $nt = $this->db->escape($notes);
        $this->db->update(
            "UPDATE hk_tasks SET status = 'rejected', inspected_at = NOW(), inspected_by = $sid, notes = '$nt' WHERE id = $tid"
        );

        $this->db->insert(
            "INSERT INTO hk_tasks (room_id, task_type, status, priority, notes) 
             VALUES (" . (int)$t['room_id'] . ", 'clean', 'pending', 'high', 'Re-clean after rejection')"
        );
        return true;
    }

    public function listMaintenance($status) {
        $sql = "SELECT m.*, r.room_no FROM maintenance m JOIN rooms r ON r.id = m.room_id";
        if ($status !== '' && $status !== null) {
            $st = $this->db->escape($status);
            return $this->db->select($sql . " WHERE m.status = '$st' ORDER BY m.id DESC");
        }
        return $this->db->select($sql . " ORDER BY m.id DESC LIMIT 200");
    }

    
    public function listHousekeepers() {
        return $this->db->select("SELECT id, full_name FROM users WHERE role = 'hk' AND active = 1");
    }

    
    public function listRooms() {
        return $this->db->select("SELECT * FROM rooms ORDER BY floor, room_no");
    }

    
    private function deductMinibarForTask($task_id)
    {
        $task = $this->db->selectOne(
            "SELECT t.*, r.room_no 
             FROM hk_tasks t 
             JOIN rooms r ON r.id = t.room_id 
             WHERE t.id = " . (int)$task_id
        );
        if (!$task) return;

        $items = $this->db->select(
            "SELECT item_name, qty FROM minibar_stock WHERE qty > 0"
        );
        if (!$items || count($items) === 0) return;

        foreach ($items as $item) {
            $item_name = $this->db->escape($item['item_name']);
            $this->db->update(
                "UPDATE minibar_stock 
                 SET qty = qty - 1 
                 WHERE item_name = '$item_name' AND qty >= 1"
            );
        }
    }

    public function createMaintenanceRequest(Maintenance $m,int $userid): int|false
    {
        $rid  = (int)$m->room_id;
        $desc = $this->db->escape($m->description);
        $prio = $this->db->escape($m->priority);
        $uid  = $userid ;

        $id = $this->db->insert(
            "INSERT INTO maintenance (room_id, description, priority, status, reported_by)
             VALUES ($rid, '$desc', '$prio', 'open', $uid)"
        );
        if ($id) {
            $this->db->update("UPDATE rooms SET status = 'out_of_order' WHERE id = $rid");
        }
        return $id;
    }

    public function resolveMaintenance($maintenance_id, $resolution) {
        $mid = (int)$maintenance_id;
        $req = $this->db->selectOne("SELECT * FROM maintenance WHERE id = $mid");
        if (!$req) return false;

        $res = $this->db->escape($resolution);

        $this->db->update(
            "UPDATE maintenance SET status = 'resolved', resolution = '$res', resolved_at = NOW() WHERE id = $mid"
        );
        $this->db->update("UPDATE rooms SET status = 'dirty' WHERE id = " . (int)$req['room_id']);

        $this->db->insert(
            "INSERT INTO hk_tasks (room_id, task_type, status, priority, notes)
             VALUES (" . (int)$req['room_id'] . ", 'clean', 'pending', 'high', 'Post-maintenance cleaning')"
        );
        return true;
    }

    public function listLostFound()
{
    return $this->db->select(
        "SELECT lf.*, r.room_no 
         FROM lost_found lf 
         LEFT JOIN rooms r ON r.id = lf.room_id 
         ORDER BY lf.id DESC"
    );
}

public function addLostFound($room_id, $description, $found_by)
{
    $rid  = (int)$room_id;
    $desc = $this->db->escape($description);
    $uid  = (int)$found_by;

    return $this->db->insert(
        "INSERT INTO lost_found (room_id, description, status, found_by)
         VALUES ($rid, '$desc', 'held', $uid)"
    );
}

public function returnLostFound($item_id, $returned_to)
{
    $id  = (int)$item_id;
    $ret = $this->db->escape($returned_to);

    return $this->db->update(
        "UPDATE lost_found 
         SET status = 'returned', 
             returned_to = '$ret', 
             returned_at = NOW() 
         WHERE id = $id"
    );
}
}

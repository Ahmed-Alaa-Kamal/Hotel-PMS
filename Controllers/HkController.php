<?php
require_once __DIR__ . '/DBController.php';
require_once __DIR__ . '/../Models/HkTask.php';

class HkController {
    private $db;
    public function __construct()
    {
        $this->db = DBController::getInstance();
    }
    public function listMyTasks($user_id) {
        $uid = (int)$user_id;
        return $this->db->select(
            "SELECT t.*, r.room_no, r.floor
             FROM hk_tasks t
             JOIN rooms r ON r.id = t.room_id
             WHERE (t.assigned_to = $uid )
               AND t.status IN ('pending','assigned','in_progress','rejected')
             ORDER BY FIELD(t.priority,'high','normal','low'), t.id"
        );
    }

    public function startTask($task_id, $user_id): bool
    {
        $tid = (int)$task_id;
        $uid = (int)$user_id;
        return $this->db->update(
            "UPDATE hk_tasks SET status = 'in_progress', assigned_to = $uid, started_at = NOW() 
             WHERE id = $tid AND status = 'assigned'"
        );
    }

    public function completeTask($task_id, $notes): bool
    {
        $tid = (int)$task_id;
        $nt = $this->db->escape($notes);
        return $this->db->update(
            "UPDATE hk_tasks SET status = 'done', completed_at = NOW(), notes = '$nt' WHERE id = $tid"
        );
    }

  public function getTaskById($task_id) {
    $tid = (int)$task_id;
    return $this->db->selectOne(
        "SELECT t.*, r.room_no, r.floor 
         FROM hk_tasks t 
         JOIN rooms r ON r.id = t.room_id 
         WHERE t.id = $tid"
    );
}

 
public function restartTask($task_id, $user_id) {
    $tid = (int)$task_id;
    $uid = (int)$user_id;
    $task = $this->getTaskById($tid);
    if (!$task) return false;
    if ($task['status'] !== 'rejected') return false;
    if ((int)$task['assigned_to'] !== $uid) return false;

    return $this->db->update(
        "UPDATE hk_tasks SET status = 'in_progress', started_at = NOW(), 
         notes = CONCAT(IFNULL(notes,''), '\n[Restarted on ', NOW(), ']') 
         WHERE id = $tid"
    );
}

public function getMyTaskHistory($user_id) {
    $uid = (int)$user_id;
    return $this->db->select(
        "SELECT t.*, r.room_no, r.floor
         FROM hk_tasks t
         JOIN rooms r ON r.id = t.room_id
         WHERE t.assigned_to = $uid
           AND t.status IN ('done','inspected','rejected')
         ORDER BY t.completed_at DESC"
    );
}
}

<?php

class DBController
{
    private static $instance = null;

    public $dbHost     = "localhost";
    public $dbUser     = "root";
    public $dbPassword = "";
    public $dbName     = "hotel_pms";
    public $connection;

    private function __construct()
    {
        $this->connection = new mysqli(
            $this->dbHost,
            $this->dbUser,
            $this->dbPassword,
            $this->dbName
        );

        if ($this->connection->connect_error) {
            die("Connection Error: " . $this->connection->connect_error);
        }

        $this->connection->set_charset('utf8mb4');
    }



    public static function getInstance(): DBController
    {
        if (self::$instance === null) {
            self::$instance = new DBController();
        }
        return self::$instance;
    }

    

    public function escape($value): string
    {
        return mysqli_real_escape_string($this->connection, (string)$value);
    }

    public function select(string $qry)
    {
        $result = $this->connection->query($qry);
        if (!$result) {
            echo "Error: " . mysqli_error($this->connection);
            return false;
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function selectOne(string $qry)
    {
        $rows = $this->select($qry);
        return ($rows && count($rows) > 0) ? $rows[0] : null;
    }

    public function insert(string $qry)
    {
        $result = $this->connection->query($qry);
        if (!$result) {
            echo "Insert Error: " . mysqli_error($this->connection);
            return false;
        }
        return $this->connection->insert_id;
    }

    public function update(string $qry)
    {
        $result = $this->connection->query($qry);
        if (!$result) {
            echo "Update Error: " . mysqli_error($this->connection);
            return false;
        }
        return true;
    }

    public function delete(string $qry)
    {
        $result = $this->connection->query($qry);
        if (!$result) {
            echo "Delete Error: " . mysqli_error($this->connection);
            return false;
        }
        return true;
    }

public function addNotification(Notification $notif): int|false
{
    $uid = (int)$notif->user_id;
    $msg = $this->escape($notif->message);
    $lnk = $notif->link ? "'" . $this->escape($notif->link) . "'" : "NULL";
    return $this->insert("INSERT INTO notifications (user_id, message, link) VALUES ($uid, '$msg', $lnk)");
}

public function getNotifications($user_id): array
{
    $uid = (int)$user_id;
    $unread = $this->selectOne("SELECT COUNT(*) AS c FROM notifications WHERE user_id = $uid AND is_read = 0");
    $list = $this->select("SELECT * FROM notifications WHERE user_id = $uid ORDER BY created_at DESC LIMIT 5");
    return [
        'count' => $unread['c'] ?? 0,
        'items' => $list
    ];
}

public function markNotificationRead($notif_id): bool
{
    $id = (int)$notif_id;
    return $this->update("UPDATE notifications SET is_read = 1 WHERE id = $id");
}
}

<?php
require_once __DIR__ . '/DBController.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    private $db;
    public $error = '';

    public function __construct()
    {
        $this->db = DBController::getInstance();
    }

    public function login(User $user): bool
    {
        $u = $this->db->escape($user->username);
        $row = $this->db->selectOne("SELECT * FROM users WHERE username = '$u' AND active = 1");
        if ($row === null) {
            $this->error = 'Invalid username or password.';
            return false;
        }
        if (!password_verify($user->password, $row['password'])) {
            $this->error = 'Invalid username or password.';
            return false;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['userId']   = (int)$row['id'];
        $_SESSION['userName'] = $row['full_name'];
        $_SESSION['userRole'] = $row['role'];
        return true;
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        session_destroy();
    }

public function register(User $user): int|false
{
    if (empty(trim($user->username)) || empty(trim($user->full_name)) ||
        empty(trim($user->email)) || empty(trim($user->password))) {
        $this->error = 'All fields are required.';
        return false;
    }
    if (strlen($user->password) < 6) {
        $this->error = 'Password must be at least 6 characters.';
        return false;
    }

    $u = $this->db->escape($user->username);
    $e = $this->db->escape($user->email);
    $exists = $this->db->selectOne("SELECT id FROM users WHERE username = '$u' OR email = '$e'");
    if ($exists !== null) {
        $this->error = 'Username or email already exists.';
        return false;
    }

    $hash     = password_hash($user->password, PASSWORD_BCRYPT);
    $fullName = $this->db->escape($user->full_name);
    $phone    = $this->db->escape($user->phone ?? '');
    $role     = 'guest';
    $insertedId = $this->db->insert(
        "INSERT INTO users (username, password, full_name, email, phone, role, active)
         VALUES ('$u', '$hash', '$fullName', '$e', '$phone', '$role', 1)"
    );

    if (!$insertedId) {
        $this->error = 'Registration failed.';
        return false;
    }

    $this->db->insert(
        "INSERT INTO guests (user_id, full_name, email, phone)
         VALUES ($insertedId, '$fullName', '$e', '$phone')"
    );

    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['userId']   = (int)$insertedId;
    $_SESSION['userName'] = $user->full_name;
    $_SESSION['userRole'] = $role;

    return (int)$insertedId;
}
}

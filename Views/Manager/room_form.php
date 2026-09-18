<?php
require_once __DIR__ . '/../../Controllers/ManagerController.php';
require_once __DIR__ . '/../../Models/Room.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'manager') {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$mgr   = new ManagerController();
$types = $mgr->listRoomTypes();
$err   = '';
$room  = null;
$id    = $_GET['id'] ?? null;

if ($id) {
$room = $mgr->getRoomById($id);

    if (!$room) die('Room not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomObj = new Room();
    $roomObj->room_no = $_POST['room_no'] ?? '';
    $roomObj->room_type_id = $_POST['room_type_id'] ?? '';
    $roomObj->floor = $_POST['floor'] ?? 1;
    $roomObj->status = $_POST['status'] ?? 'clean';
    $roomObj->notes = $_POST['description'] ?? '';

    $image_name = $room['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = __DIR__ . '/../../uploads/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_name);
    }
    $roomObj->image = $image_name;

    if (!empty($roomObj->room_no) && !empty($roomObj->room_type_id)) {
        if ($id) {
            $mgr->updateRoom($id, $roomObj);
            header('Location: rooms.php');
            exit;
        } else {
            $result = $mgr->addRoom($roomObj);
            if ($result) {
                header('Location: rooms.php');
                exit;
            } else {
                $err = 'Room number already exists.';
            }
        }
    } else {
        $err = 'Please fill required fields.';
    }
}

$page_title = $id ? 'Edit Room : ' . htmlspecialchars($room['room_no']) : 'Add Room';
$active = 'rooms';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { background: #1a252f; min-height: 100vh; }
        .sidebar .nav-link { color: #b0bec5; border-radius: .35rem; margin-bottom: .2rem; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
        .sidebar .nav-link.active { background: #3498db; color: #fff !important; }
        .topbar { background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    </style>
</head>
<body>
<div class="container-fluid g-0">
    <div class="row g-0">
        <div class="col-md-2 sidebar p-3">
            <h5 class="text-white mb-4"><i class="bi bi-building"></i> Hotel PMS</h5>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i>Users</a>
                <a href="guests.php" class="nav-link"><i class="bi bi-person-badge me-2"></i>Guests</a>
                <a href="rooms.php" class="nav-link active"><i class="bi bi-door-open me-2"></i>Rooms</a>
                <a href="room_types.php" class="nav-link"><i class="bi bi-tags me-2"></i>Room Types</a>
                <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart me-2"></i>Reports</a>
                <a href="feedback.php" class="nav-link"><i class="bi bi-star me-2"></i>Feedback</a>
                <a href="messages.php" class="nav-link"><i class="bi bi-envelope me-2"></i>Messages</a>
                <a href="blacklist.php" class="nav-link "><i class="bi bi-shield-exclamation me-2"></i>Blacklist</a>
                                <a href="blacklist_approval.php" class="nav-link "><i class="bi bi-check-circle me-2"></i>Approval</a>

            </nav>
        </div>
        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (Manager)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>
            <div class="p-4">
                <div class="card shadow-sm w-50 mx-auto">
                    <div class="card-body">
                        <a href="rooms.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back</a>
                        <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Room Number *</label>
                                <input type="text" name="room_no" class="form-control" required
                                       value="<?= htmlspecialchars($room['room_no'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Room Type *</label>
                                <select name="room_type_id" class="form-select" required>
                                    <?php foreach ($types as $t): ?>
                                        <option value="<?= (int)$t['id'] ?>"
                                            <?= (isset($room) && (int)$room['room_type_id'] === (int)$t['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['name']) ?> (<?= number_format($t['base_price'],2) ?> EGP)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Floor</label>
                                <input type="number" name="floor" class="form-control" min="1"
                                       value="<?= (int)($room['floor'] ?? 1) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>

                                        <input type="text" class="form-control" value="<?= ucfirst($room['status'] ?? 'N/A') ?>" readonly disabled>
                                         <small class="text-muted">Status cannot be changed here. Use Housekeeping Supervisor dashboard.</small>

                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($room['notes'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Image</label>
                                <?php if (!empty($room['image'])): ?>
                                    <div class="mb-2"><img src="/hotel_pms/uploads/<?= htmlspecialchars($room['image']) ?>" width="150"></div>
                                <?php endif; ?>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-<?= $id ? 'pencil' : 'plus-circle' ?>"></i>
                                <?= $id ? 'Update' : 'Add' ?> Room
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
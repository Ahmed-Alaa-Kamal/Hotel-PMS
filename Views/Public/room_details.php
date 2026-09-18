<?php
require_once __DIR__ . '/../../Controllers/ManagerController.php';

$mgr = new ManagerController();
$id = (int)$_GET['id'];

$room = $mgr->getRoomById($id);

if (!$room) {
    http_response_code(404);
    echo '<div class="container mt-5"><h2>Room not found</h2></div>';
    exit;
}

$page_title = 'Room ' . $room['room_no'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .navbar { background: #2c3e50; }
        footer { background: #2c3e50; color: white; padding: 2rem 0; margin-top: 3rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">🏨 Boutique Hotel</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link active" href="rooms.php">Rooms</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Auth/login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                     class="img-fluid rounded" alt="Room image">
            </div>
            <div class="col-md-6">
                <h2><?= htmlspecialchars($room['room_no']) ?> - <?= htmlspecialchars($room['type_name']) ?></h2>
                <p class="text-muted">Floor: <?= $room['floor'] ?></p>
                <h4 class="text-primary"><?= number_format($room['base_price'], 2) ?> EGP / night</h4>
                <p><?= nl2br(htmlspecialchars($room['notes'] ?? 'No description available.')) ?></p>
                <p>Status: <span class="badge bg-<?= $room['status'] == 'clean' ? 'success' : 'warning' ?>"><?= $room['status'] ?></span></p>
                <a href="../Auth/login.php" class="btn btn-success btn-lg">Book Now</a>
            </div>
        </div>
    </div>

    <footer class="text-center">
        <div class="container"><p>&copy; <?= date('Y') ?> Boutique Hotel. All rights reserved.</p></div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
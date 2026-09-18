<?php
require_once __DIR__ . '/../../Controllers/ManagerController.php';

$mgr = new ManagerController();
$rooms = $mgr->listAllRoomsWithStatus();

$page_title = 'Rooms & Suites';
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
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
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
        <h2 class="text-center mb-4">Our Rooms</h2>
        <div class="row g-4">
            <?php foreach ($rooms as $room): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                         class="card-img-top" style="height:200px; object-fit:cover;">
                    <div class="card-body">
                        <h5><?= htmlspecialchars($room['room_no']) ?> - <?= htmlspecialchars($room['type_name']) ?></h5>
                        <p><?= number_format($room['base_price'], 2) ?> EGP / night</p>
                        <a href="room_details.php?id=<?= $room['id'] ?>" class="btn btn-sm btn-primary">Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="text-center">
        <div class="container"><p>&copy; <?= date('Y') ?> Boutique Hotel. All rights reserved.</p></div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
require_once __DIR__ . '/../../Controllers/ManagerController.php';

$mgr = new ManagerController();
$allRooms = $mgr->listAllRoomsWithStatus(); 
$rooms = array_slice($allRooms, 0, 6);      

$page_title = 'Boutique Hotel - Welcome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .hero {
            background: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80') center/cover no-repeat;
            color: white;
            padding: 6rem 0;
            text-align: center;
            position: relative;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
        }
        .hero-content { position: relative; z-index: 1; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .navbar { background: #2c3e50; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        footer { background: #2c3e50; color: white; padding: 2rem 0; margin-top: 3rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">🏨 Boutique Hotel</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="rooms.php">Rooms</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Auth/login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="hero-content">
            <h1>Welcome to Boutique Hotel</h1>
            <p class="lead">Experience luxury and comfort in every stay</p>
            <a href="rooms.php" class="btn btn-light btn-lg">Explore Rooms</a>
        </div>
    </div>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Featured Rooms</h2>
        <div class="row g-4">
            <?php foreach ($rooms as $room): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                         class="card-img-top" style="height:200px; object-fit:cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($room['room_no']) ?> - <?= htmlspecialchars($room['type_name']) ?></h5>
                        <p class="card-text text-muted">Floor <?= $room['floor'] ?></p>
                        <p class="card-text fw-bold"><?= number_format($room['base_price'], 2) ?> EGP / night</p>
                        <a href="room_details.php?id=<?= $room['id'] ?>" class="btn btn-outline-primary">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="text-center">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Boutique Hotel. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
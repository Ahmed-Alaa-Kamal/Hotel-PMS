<?php
$page_title = 'About Us';
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
                    <li class="nav-item"><a class="nav-link active" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="rooms.php">Rooms</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Auth/login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>About Our Hotel</h2>
        <div class="row mt-4">
            <div class="col-md-6">
                <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                     class="img-fluid rounded" alt="Hotel exterior">
            </div>
            <div class="col-md-6">
                <p>Boutique Hotel is a charming small hotel offering personalized service and elegant rooms. Located in the heart of the city, we provide a unique blend of modern amenities and traditional hospitality.</p>
                <p>With 20 uniquely designed rooms, a spa, a rooftop café, and a dedicated team, we ensure your stay is unforgettable.</p>
                <ul>
                    <li>Free high-speed Wi-Fi</li>
                    <li>24/7 room service</li>
                    <li>Concierge & airport transfer</li>
                    <li>Fitness center & spa</li>
                </ul>
            </div>
        </div>
    </div>

    <footer class="text-center">
        <div class="container"><p>&copy; <?= date('Y') ?> Boutique Hotel. All rights reserved.</p></div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
require_once __DIR__ . '/../../Controllers/FrontdeskController.php';
require_once __DIR__ . '/../../Controllers/GuestController.php';
require_once __DIR__ . '/../../Models/Guest.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userRole']) || !in_array($_SESSION['userRole'], ['frontdesk','admin','manager'])) {
    header('Location: /hotel_pms/Views/Auth/login.php');
    exit;
}

$fd      = new FrontdeskController();
$gc      = new GuestController();
$search  = $_GET['search'] ?? '';
$err     = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_guest'])) {
    $full_name    = trim($_POST['full_name']    ?? '');
    $email        = trim($_POST['email']        ?? '');
    $phone        = trim($_POST['phone']        ?? '');
    $doc_type     = $_POST['doc_type']          ?? '';
    $doc_number   = trim($_POST['doc_number']   ?? '');
    $nationality  = trim($_POST['nationality']  ?? '');

    if (empty($full_name) || empty($email) || empty($phone)) {
        $err = 'Full name, email, and phone are required.';
    } 
     else {
                $guest = new Guest();
                $guest->full_name   = $full_name;
                $guest->email       = $email;
                $guest->phone       = $phone;
                $guest->doc_type    = $doc_type;
                $guest->doc_number  = $doc_number;
                $guest->nationality = $nationality;
                $guestPassword = $_POST['password'] ?? '';
                    
                $result = $gc->createGuest($guest,$guestPassword);
                if ($result) {
                    $success = 'Guest added successfully.';
                } else {
                    $err = 'Failed to add guest.';
                }
            
        }

}


$guests = [];
if (!empty($search)) {
    $guests = $gc->searchGuests($search);
} else {
    $guests = $fd->listGuests();
}


$page_title = 'Guests';
$active = 'guests';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
                <a href="index.php"          class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="reservations.php"   class="nav-link"><i class="bi bi-calendar-check me-2"></i>Reservations</a>
                <a href="new_reservation.php"class="nav-link"><i class="bi bi-plus-circle me-2"></i>New Reservation</a>
                <a href="guests.php"         class="nav-link active"><i class="bi bi-people me-2"></i>Guests</a>
            </nav>
        </div>

       
        <div class="col-md-10">
            <div class="topbar px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                <div>
                    <span class="me-3"><?= htmlspecialchars($_SESSION['userName']) ?> (Front Desk)</span>
                    <a href="/hotel_pms/Views/Auth/login.php?logout=1" class="btn btn-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

            <div class="p-4">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form class="d-flex" method="get">
                            <input type="text" name="search" class="form-control me-2"
                                   placeholder="Search by name, email, or phone..."
                                   value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-primary"
                                data-bs-toggle="modal" data-bs-target="#addGuestModal">
                            <i class="bi bi-plus-circle"></i> Add Guest
                        </button>
                    </div>
                </div>

                <?php if ($err): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Nationality</th>
                                    <th>VIP</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($guests)): ?>
                                <tr><td colspan="8" class="text-center py-4">No guests found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($guests as $g): ?>
                                <tr>
                                    <td><?= (int)$g['id'] ?></td>
                                    <td><?= htmlspecialchars($g['full_name']) ?></td>
                                    <td><?= htmlspecialchars($g['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($g['phone'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($g['nationality'] ?? '') ?></td>
                                    <td>
                                        <?php if ($g['vip'] == 1 || in_array($g['loyalty_tier'], ['gold','platinum'])): ?>
                                            <span class="badge bg-warning text-dark">⭐ VIP</span>
                                        <?php endif; ?>
                                    </td>                                    
                                    <td><?= (int)($g['loyalty_points'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addGuestModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content" id="addGuestForm">
      <div class="modal-header">
        <h5 class="modal-title">Add New Guest</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="add_guest" value="1">

        <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control form-control-sm" placeholder="Password" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone *</label>
            <input type="text" name="phone" class="form-control" required>
        </div>



        <div class="mb-3">
            <label class="form-label">Document Type</label>
            <select name="doc_type" class="form-select">
                <option value="">-- Select --</option>
                <option value="passport">Passport</option>
                <option value="id_card">ID Card</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Document Number</label>
            <input type="text" name="doc_number" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Nationality</label>
            <input type="text" name="nationality" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Guest</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('addGuestForm').addEventListener('submit', function(e) {
    const dobInput = document.getElementById('dobInput');
    const dob      = new Date(dobInput.value);
    const today    = new Date();
    let age18      = new Date(dob);
    age18.setFullYear(age18.getFullYear() + 18);

    if (!dobInput.value || age18 > today) {
        dobInput.classList.add('is-invalid');
        e.preventDefault();
    } else {
        dobInput.classList.remove('is-invalid');
    }
});
</script>
</body>
</html>
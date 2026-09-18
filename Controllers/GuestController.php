<?php
require_once __DIR__ . '/DBController.php';
require_once __DIR__ . '/../Models/Guest.php';

class GuestController {
    private $db;
  public function __construct()
    {
        $this->db = DBController::getInstance();
    }
    public function getGuestByUserId($user_id) {
        $uid = (int)$user_id;
        return $this->db->selectOne("SELECT * FROM guests WHERE user_id = $uid");
    }

    public function updateProfile(Guest $guest): bool
    {
        $gid  = (int)$guest->id;
        $fn   = $this->db->escape($guest->full_name);
        $em   = $this->db->escape($guest->email);
        $ph   = $this->db->escape($guest->phone);
        $dt   = $this->db->escape($guest->doc_type);
        $dn   = $this->db->escape($guest->doc_number);
        $nat  = $this->db->escape($guest->nationality);
        $pref = $this->db->escape($guest->preferences);
        return $this->db->update(
            "UPDATE guests SET full_name='$fn', email='$em', phone='$ph', doc_type='$dt', 
             doc_number='$dn', nationality='$nat', preferences='$pref' WHERE id = $gid"
        );
    }

    public function getMyReservations($guest_id) {
        $gid = (int)$guest_id;
        return $this->db->select(
            "SELECT r.*, rt.name AS room_type_name, rm.room_no
             FROM reservations r
             JOIN room_types rt ON rt.id = r.room_type_id
             LEFT JOIN rooms rm ON rm.id = r.room_id
             WHERE r.guest_id = $gid
             ORDER BY r.id DESC"
        );
    }

    public function getMyFolioForReservation($reservation_id) {
        $rid = (int)$reservation_id;
        return $this->db->selectOne(
            "SELECT * FROM folios WHERE reservation_id = $rid ORDER BY id DESC LIMIT 1"
        );
    }



    public function getMyLoyaltyHistory($guest_id) {
        $gid = (int)$guest_id;
        return $this->db->select(
            "SELECT * FROM loyalty_transactions WHERE guest_id = $gid ORDER BY id DESC LIMIT 100"
        );
    }

    public function createGuest(Guest $guest, string $password): int|false
{
    
    if (empty(trim($password))) {
        return false; 
    }

    $fn  = $this->db->escape($guest->full_name);
    $em  = $this->db->escape($guest->email);
    $ph  = $this->db->escape($guest->phone);
    $dt  = $this->db->escape($guest->doc_type ?? '');
    $dn  = $this->db->escape($guest->doc_number ?? '');
    $nat = $this->db->escape($guest->nationality ?? '');

    $username = !empty($em) ? $em : 'guest_' . time();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    
    $existingUser = $this->db->selectOne("SELECT id FROM users WHERE username = '$username'");
    if ($existingUser) {
        return false; 
    }

    $userId = $this->db->insert(
        "INSERT INTO users (username, password, full_name, email, phone, role, active)
         VALUES ('$username', '$hashedPassword', '$fn', '$em', '$ph', 'guest', 1)"
    );

    if (!$userId) return false;

    return $this->db->insert(
        "INSERT INTO guests (user_id, full_name, email, phone, doc_type, doc_number, nationality)
         VALUES ($userId, '$fn', '$em', '$ph', '$dt', '$dn', '$nat')"
    );
}

public function searchGuests($term)
{
    $t = $this->db->escape($term);
    return $this->db->select(
        "SELECT * FROM guests 
         WHERE full_name LIKE '%$t%' OR email LIKE '%$t%' OR phone LIKE '%$t%' 
         ORDER BY full_name LIMIT 50"
    );
}


public function getStayHistory($guest_id)
{
    $gid = (int)$guest_id;

  
    $stays = $this->db->selectOne(
        "SELECT COUNT(*) AS cnt FROM reservations 
         WHERE guest_id = $gid AND status IN ('checked_out','no_show')"
    )['cnt'] ?? 0;


    $nights = $this->db->selectOne(
        "SELECT COALESCE(SUM(DATEDIFF(check_out, check_in)), 0) AS nights 
         FROM reservations 
         WHERE guest_id = $gid AND status IN ('checked_out','no_show')"
    )['nights'] ?? 0;

  
    $spend = $this->db->selectOne(
        "SELECT COALESCE(SUM(f.total_charges), 0) AS total 
         FROM folios f 
         JOIN reservations r ON f.reservation_id = r.id 
         WHERE r.guest_id = $gid AND f.status = 'closed'"
    )['total'] ?? 0;


    $avg = $stays > 0 ? round($spend / $stays, 2) : 0;

    return [
        'stays'  => (int)$stays,
        'nights' => (int)$nights,
        'spend'  => number_format($spend, 2),
        'avg'    => number_format($avg, 2)
    ];
}

public function getFolio($folio_id)
{
    return $this->db->selectOne("SELECT * FROM folios WHERE id = " . (int)$folio_id);
}

public function getFolioCharges($folio_id)
{
    return $this->db->select(
        "SELECT * FROM folio_charges WHERE folio_id = " . (int)$folio_id . " ORDER BY id"
    );
}

public function getFolioPayments($folio_id)
{
    return $this->db->select(
        "SELECT * FROM payments WHERE folio_id = " . (int)$folio_id . " ORDER BY id"
    );
}
}

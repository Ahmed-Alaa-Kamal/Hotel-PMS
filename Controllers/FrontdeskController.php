<?php
require_once __DIR__ . '/DBController.php';
require_once __DIR__ . '/../Models/Reservation.php';
require_once __DIR__ . '/../Models/Room.php';
require_once __DIR__ . '/../Models/Guest.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/FolioCharge.php';

class FrontdeskController
{
    private $db;

    public function __construct()
    {
        $this->db = DBController::getInstance();
    }

    public function searchAvailableRooms($check_in, $check_out, $room_type_id) {
        $ci = $this->db->escape($check_in);
        $co = $this->db->escape($check_out);
        $rt = (int)$room_type_id;

        $sql = "SELECT r.* FROM rooms r
                WHERE r.occupancy = 'available' AND r.status = 'clean'
                  AND r.room_type_id = $rt
                  AND r.id NOT IN (
                      SELECT IFNULL(room_id,0) FROM reservations
                      WHERE status IN ('booked','checked_in')
                        AND NOT (check_out <= '$ci' OR check_in >= '$co')
                  )
                ORDER BY r.floor, r.room_no";
        return $this->db->select($sql);
    }

    public function allocateBestRoom($room_type_id, $check_in, $check_out) {
        $rooms = $this->searchAvailableRooms($check_in, $check_out, $room_type_id);
        if (count($rooms) === 0) return null;
        return $rooms[0];
    }

public function createReservation(Reservation $reservation)
{
   
    $rt = $this->db->selectOne(
        "SELECT base_price FROM room_types WHERE id = " . (int)$reservation->room_type_id
    );
    if ($rt === null) return null;
    $rate = $rt['base_price'];

   
    $src = $reservation->source ?? 'website';
    if ($src === 'walk_in') {
        $rate = $this->calculateWalkInPrice($rate);
    }

 
    $room = $this->allocateBestRoom(
        $reservation->room_type_id,
        $reservation->check_in,
        $reservation->check_out
    );
    $room_id = $room ? (int)$room['id'] : 0;

  
    $gid    = (int)$reservation->guest_id;
    $rt_id  = (int)$reservation->room_type_id;
    $ci     = $this->db->escape($reservation->check_in);
    $co     = $this->db->escape($reservation->check_out);
    $ad     = (int)$reservation->adults;
    $ch     = (int)$reservation->children;
    $src    = $this->db->escape($src);
  
    $status = $this->db->escape($reservation->status ?? 'pending');

 
    return $this->db->insert(
        "INSERT INTO reservations (guest_id, room_id, room_type_id, check_in, check_out, adults, children, rate, source, status)
         VALUES ($gid, $room_id, $rt_id, '$ci', '$co', $ad, $ch, $rate, '$src', '$status')"
    );
}

    public function processPreAuth($reservation_id) {
        $rid = (int)$reservation_id;
        $r = $this->db->selectOne("SELECT * FROM reservations WHERE id = $rid");
        if ($r === null) return false;

        $nights = (strtotime($r['check_out']) - strtotime($r['check_in'])) / 86400;
        if ($nights < 1) $nights = 1;
        $hold = $r['rate'] * $nights;

        $this->db->update("UPDATE reservations SET pre_auth_amount = $hold, pre_auth_status = 'held' WHERE id = $rid");
        return $hold;
    }

    

public function checkIn($reservation_id, &$errorMessage = null)
{
    $rid = (int)$reservation_id;
    $r   = $this->db->selectOne("SELECT * FROM reservations WHERE id = $rid");
    if ($r === null) {
        $errorMessage = 'Reservation not found.';
        return false;
    }
    if ($r['status'] !== 'booked') {
        $errorMessage = 'Reservation is not in booked status.';
        return false;
    }

  
    if ($r['room_id'] === null) {
        $room = $this->allocateBestRoom($r['room_type_id'], $r['check_in'], $r['check_out']);
        if ($room === null) {
            $errorMessage = 'No available rooms of this type.';
            return false;
        }
        $roomId = (int)$room['id'];
        $this->db->update("UPDATE reservations SET room_id = $roomId WHERE id = $rid");
        $r['room_id'] = $roomId;
    }

    $room = $this->db->selectOne("SELECT * FROM rooms WHERE id = " . (int)$r['room_id']);


    if ($room['occupancy'] !== 'available') {
        $errorMessage = 'Room ' . $room['room_no'] . ' is currently occupied. Cannot check‑in.';
        return false;
    }
    if (!in_array($room['status'], ['clean', 'inspected'])) {
        $errorMessage = 'Room ' . $room['room_no'] . ' is not ready (status: ' . $room['status'] . '). Please wait until it is cleaned.';
        return false;
    }

    // Check‑In مسموح
    $this->db->update("UPDATE reservations SET status = 'checked_in' WHERE id = $rid");
    $this->db->update("UPDATE rooms SET occupancy = 'occupied' WHERE id = " . (int)$r['room_id']);

    return true;
}

    public function postCharge(FolioCharge $charge)
{
    $fid   = (int)$charge->folio_id;
    $total = $charge->amount * $charge->qty;
    $ct    = $this->db->escape($charge->charge_type);
    $desc  = $this->db->escape($charge->description);
    $uid   = (int)$charge->posted_by;

    $this->db->insert(
        "INSERT INTO folio_charges (folio_id, charge_type, description, amount, qty, posted_by)
         VALUES ($fid, '$ct', '$desc', $total, {$charge->qty}, $uid)"
    );

    $row = $this->db->selectOne(
        "SELECT COALESCE(SUM(amount), 0) AS new_total 
         FROM folio_charges 
         WHERE folio_id = $fid AND voided = 0"
    );
    $newTotal = (float)$row['new_total'];

    $this->db->update(
        "UPDATE folios SET
            total_charges = $newTotal,
            balance       = $newTotal - total_payments
         WHERE id = $fid"
    );
}


    public function recordPayment(Payment $payment){
    $fid  = (int)$payment->folio_id;
    $amt  = $payment->amount;
    $meth = $this->db->escape($payment->method);
    $ref  = $this->db->escape($payment->reference);
    $uid  = (int)$payment->received_by;

    $inserted = $this->db->insert(
        "INSERT INTO payments (folio_id, amount, method, reference, received_by)
         VALUES ($fid, $amt, '$meth', '$ref', $uid)"
    );
    if (!$inserted) return false;

    $row = $this->db->selectOne(
        "SELECT COALESCE(SUM(amount), 0) AS new_paid FROM payments WHERE folio_id = $fid"
    );
    $newPaid = (float)$row['new_paid'];

    $this->db->update(
        "UPDATE folios SET
            total_payments = $newPaid,
            balance        = total_charges - $newPaid
         WHERE id = $fid"
    );

    $folio   = $this->db->selectOne("SELECT guest_id FROM folios WHERE id = $fid");
    $gid     = (int)$folio['guest_id'];
    $pts     = (int)$amt;

    $this->db->update(
        "UPDATE guests SET loyalty_points = loyalty_points + $pts WHERE id = $gid"
    );
    $this->db->insert(
        "INSERT INTO loyalty_transactions (guest_id, points, type, reference)
         VALUES ($gid, $pts, 'earn', 'Payment of $amt EGP on Folio $fid')"
    );

    $g = $this->db->selectOne("SELECT loyalty_points FROM guests WHERE id = $gid");
    $tier = 'bronze';
    if ($g['loyalty_points'] >= 5000)     $tier = 'platinum';
    elseif ($g['loyalty_points'] >= 2000) $tier = 'gold';
    elseif ($g['loyalty_points'] >= 500)  $tier = 'silver';

    $this->db->update("UPDATE guests SET loyalty_tier = '$tier' WHERE id = $gid");

    return true;
}

    public function checkOut($reservation_id) {
        $rid = (int)$reservation_id;
        $r = $this->db->selectOne("SELECT * FROM reservations WHERE id = $rid");
        if ($r === null || $r['status'] !== 'checked_in') return false;

        $folio = $this->db->selectOne("SELECT * FROM folios WHERE reservation_id = $rid AND status = 'open'");
        if ($folio === null) return false;
        if ($folio['balance'] > 0) return 'unpaid';

        $this->db->update("UPDATE folios SET status = 'closed', closed_at = NOW() WHERE id = " . (int)$folio['id']);
        $this->db->update("UPDATE reservations SET status = 'checked_out' WHERE id = $rid");
        $this->db->update("UPDATE rooms SET occupancy = 'available', status = 'dirty' WHERE id = " . (int)$r['room_id']);

        
        $this->db->insert(
            "INSERT INTO hk_tasks (room_id, task_type, status, priority)
             VALUES (" . (int)$r['room_id'] . ", 'clean', 'pending', 'normal')"
        );

        return true;
    }

    public function cancelReservation($reservation_id, $reason) {
        $rid = (int)$reservation_id;
        $r = $this->db->selectOne("SELECT * FROM reservations WHERE id = $rid");
        if ($r === null || $r['status'] !== 'booked') return false;

        $reas = $this->db->escape($reason);
        $this->db->update("UPDATE reservations SET status = 'cancelled', notes = '$reas' WHERE id = $rid");

        if ($r['pre_auth_status'] === 'held') {
            $this->db->update("UPDATE reservations SET pre_auth_status = 'released' WHERE id = $rid");
        }
        return true;
    }

    public function listTodayArrivalsAndDepartures() {
        $arrivals = $this->db->select(
            "SELECT r.*, g.full_name AS guest_name, rt.name AS room_type_name
             FROM reservations r
             JOIN guests g ON g.id = r.guest_id
             JOIN room_types rt ON rt.id = r.room_type_id
             WHERE r.check_in = CURDATE() AND r.status = 'booked'
             ORDER BY r.id DESC"
        );

        $departures = $this->db->select(
            "SELECT r.*, g.full_name AS guest_name, rm.room_no
             FROM reservations r
             JOIN guests g ON g.id = r.guest_id
             LEFT JOIN rooms rm ON rm.id = r.room_id
             WHERE r.check_out = CURDATE() AND r.status = 'checked_in'
             ORDER BY r.id DESC"
        );

        return ['arrivals' => $arrivals, 'departures' => $departures];
    }

public function listReservations($status) {
    $sql = "SELECT r.*, g.full_name AS guest_name, rt.name AS room_type_name, rm.room_no,
                   g.vip, g.loyalty_tier
            FROM reservations r
            JOIN guests g ON g.id = r.guest_id
            JOIN room_types rt ON rt.id = r.room_type_id
            LEFT JOIN rooms rm ON rm.id = r.room_id";
    if ($status !== '' && $status !== null) {
        $st = $this->db->escape($status);
        return $this->db->select($sql . " WHERE r.status = '$st' ORDER BY r.id DESC");
    }
    return $this->db->select($sql . " ORDER BY r.id DESC LIMIT 200");
}

    public function getReservation($id) {
        $rid = (int)$id;
        return $this->db->selectOne(
            "SELECT r.*, g.full_name AS guest_name, g.email AS guest_email, g.phone AS guest_phone,
                    rt.name AS room_type_name, rm.room_no
             FROM reservations r
             JOIN guests g ON g.id = r.guest_id
             JOIN room_types rt ON rt.id = r.room_type_id
             LEFT JOIN rooms rm ON rm.id = r.room_id
             WHERE r.id = $rid"
        );
    }

    /**
 * [9] تسعير ديناميكي للحجوزات المباشرة (Walk‑In)
 * بيحسب السعر حسب نسبة الإشغال الحالية
 */
public function calculateWalkInPrice($basePrice)
{
    // عدد الغرف الكلي والمشغولة
    $totalRooms = (int)$this->db->selectOne("SELECT COUNT(*) AS c FROM rooms")['c'];
    $occupied   = (int)$this->db->selectOne("SELECT COUNT(*) AS c FROM rooms WHERE occupancy = 'occupied'")['c'];

    if ($totalRooms == 0) return $basePrice;

    $occupancyRate = ($occupied / $totalRooms) * 100;

    if ($occupancyRate > 80) {
        return round($basePrice * 1.4, 2);        // طلب عالي → سعر أعلى 40%
    } elseif ($occupancyRate > 50) {
        return round($basePrice * 1.2, 2);        // طلب متوسط → سعر أعلى 20%
    } else {
        return round($basePrice * 0.9, 2);        // طلب منخفض → خصم 10%
    }
}

    public function getFolio($folio_id) {
        return $this->db->selectOne("SELECT * FROM folios WHERE id = " . (int)$folio_id);
    }

    public function getFolioByReservation($reservation_id) {
        return $this->db->selectOne("SELECT * FROM folios WHERE reservation_id = " . (int)$reservation_id . " ORDER BY id DESC LIMIT 1");
    }

    public function getFolioCharges($folio_id) {
        return $this->db->select("SELECT * FROM folio_charges WHERE folio_id = " . (int)$folio_id . " ORDER BY id");
    }

    public function getFolioPayments($folio_id) {
        return $this->db->select("SELECT * FROM payments WHERE folio_id = " . (int)$folio_id . " ORDER BY id");
    }

    public function listGuests() {
        return $this->db->select("SELECT * FROM guests ORDER BY id DESC LIMIT 200");
    }

    public function listRoomTypes() {
        return $this->db->select("SELECT * FROM room_types ORDER BY base_price");
    }

    public function listServices() {
        return $this->db->select("SELECT * FROM services WHERE active = 1 ORDER BY category, name");
    }
    
/**
 * معالجة عدم الحضور (No‑Show)
 * بتحرر الغرفة، وتسحب مبلغ الـ Pre‑Auth كغرامة، وتقفل الفوليو
 */
public function markNoShow($reservation_id)
{
    $rid = (int)$reservation_id;
    $r   = $this->db->selectOne("SELECT * FROM reservations WHERE id = $rid");
    if ($r === null || $r['status'] !== 'booked') return false;

    // 1. تحرير الغرفة
    if ($r['room_id']) {
        $this->db->update("UPDATE rooms SET occupancy = 'available', status = 'clean' WHERE id = " . (int)$r['room_id']);
        $this->db->update("UPDATE reservations SET room_id = NULL WHERE id = $rid");
    }

    // 2. معالجة الفوليو
    $folio = $this->db->selectOne("SELECT * FROM folios WHERE reservation_id = $rid AND status = 'open'");
    if ($folio) {
        // المدفوع (Pre‑Auth)
        $paidRow = $this->db->selectOne("SELECT COALESCE(SUM(amount),0) AS amt FROM payments WHERE folio_id = " . (int)$folio['id']);
        $paid    = (float)$paidRow['amt'];

        // 1. عكس إيراد الغرفة والضريبة (خصم مساوي)
        $roomCharge = $this->db->selectOne("SELECT COALESCE(SUM(amount),0) AS amt FROM folio_charges WHERE folio_id = " . (int)$folio['id'] . " AND charge_type IN ('room','tax')");
        $roomTotal  = (float)$roomCharge['amt'];
        if ($roomTotal > 0) {
            $this->db->insert(
                "INSERT INTO folio_charges (folio_id, charge_type, description, amount, qty)
                 VALUES ({$folio['id']}, 'discount', 'Room & Tax Reversal (No‑Show)', -$roomTotal, 1)"
            );
        }

        // 2. إضافة بند الغرامة (Penalty)
        if ($paid > 0) {
            $this->db->insert(
                "INSERT INTO folio_charges (folio_id, charge_type, description, amount, qty)
                 VALUES ({$folio['id']}, 'penalty', 'No‑Show Penalty (forfeited Pre‑Auth)', $paid, 1)"
            );
        }

        // 3. تحديث الإجماليات وإغلاق الفوليو
        $this->db->update("UPDATE folios SET total_charges = (SELECT COALESCE(SUM(amount),0) FROM folio_charges WHERE folio_id = " . (int)$folio['id'] . "), balance = 0 WHERE id = " . (int)$folio['id']);
        $this->db->update("UPDATE folios SET status = 'closed', closed_at = NOW() WHERE id = " . (int)$folio['id']);
    }

    // 3. تغيير حالة الحجز
    $this->db->update("UPDATE reservations SET status = 'no_show', pre_auth_status = 'charged' WHERE id = $rid");

    return true;
}




public function applyTax($folio_id)
{
    $fid = (int)$folio_id;
        $fid = (int)$folio_id;
    // تحقق إن الضريبة مش موجودة
    $existing = $this->db->selectOne(
        "SELECT id FROM folio_charges WHERE folio_id = $fid AND charge_type = 'tax' AND voided = 0 LIMIT 1"
    );
    if ($existing) return; // الضريبة موجودة بالفعل
    $row = $this->db->selectOne(
        "SELECT COALESCE(SUM(amount), 0) AS room_total
         FROM folio_charges
         WHERE folio_id = $fid  AND voided = 0"
    );
    $roomTotal = (float)$row['room_total'];
    if ($roomTotal <= 0) return;
 
    $vat = round($roomTotal * 0.14, 2);
 
    $this->db->insert(
        "INSERT INTO folio_charges (folio_id, charge_type, description, amount, qty)
         VALUES ($fid, 'tax', 'VAT (14% on room charges)', $vat, 1)"
    );
 
    $newTotal = (float)$this->db->selectOne("SELECT COALESCE(SUM(amount),0) AS t FROM folio_charges WHERE folio_id=$fid")['t'];
$this->db->update("UPDATE folios SET total_charges=$newTotal, balance=$newTotal - total_payments WHERE id=$fid");
}

public function confirmReservation($reservation_id)
{
    $rid = (int)$reservation_id;
    $r = $this->db->selectOne("SELECT * FROM reservations WHERE id = $rid");
    if ($r === null) return false;

    $nights = (strtotime($r['check_out']) - strtotime($r['check_in'])) / 86400;
    if ($nights < 1) $nights = 1;
    $roomTotal = $r['rate'] * $nights;


    $estimatedVat = round($roomTotal * 0.14, 2);
    $preAuthAmount = round(($roomTotal + $estimatedVat) * 0.20, 2);


    $this->db->update("UPDATE reservations SET pre_auth_amount = $preAuthAmount, pre_auth_status = 'held' WHERE id = $rid");

    $folioId = $this->db->insert(
        "INSERT INTO folios (reservation_id, guest_id, status, total_charges, balance)
         VALUES ($rid, " . (int)$r['guest_id'] . ", 'open', $roomTotal, $roomTotal)"
    );
    $this->db->update("UPDATE reservations SET status = 'booked' WHERE id = $rid AND status = 'pending_payment'");

    $this->db->insert(
        "INSERT INTO folio_charges (folio_id, charge_type, description, amount, qty)
         VALUES ($folioId, 'room', 'Room charge for $nights night(s)', $roomTotal, $nights)"
    );

    $this->applyTax($folioId);

    return $folioId;
}


// public function createReservationWithStatus(Reservation $reservation)
// {
//     $rt = $this->db->selectOne(
//         "SELECT base_price FROM room_types WHERE id = " . (int)$reservation->room_type_id
//     );
//     if ($rt === null) return null;
//     $rate = $rt['base_price'];

    
//     $room = $this->allocateBestRoom(
//         $reservation->room_type_id,
//         $reservation->check_in,
//         $reservation->check_out
//     );
//     $room_id = $room ? (int)$room['id'] : 'NULL';

    
//     $gid    = (int)$reservation->guest_id;
//     $rt_id  = (int)$reservation->room_type_id;
//     $ci     = $this->db->escape($reservation->check_in);
//     $co     = $this->db->escape($reservation->check_out);
//     $ad     = (int)$reservation->adults;
//     $ch     = (int)$reservation->children;
//     $src    = $this->db->escape($reservation->source ?? 'website');
//     $status = $this->db->escape($reservation->status ?? 'booked');

    
//     return $this->db->insert(
//         "INSERT INTO reservations (guest_id, room_id, room_type_id, check_in, check_out, adults, children, rate, source, status)
//          VALUES ($gid, $room_id, $rt_id, '$ci', '$co', $ad, $ch, $rate, '$src', '$status')"
//     );
// }

public function getReservationsByStatus($status)
{
    $st = $this->db->escape($status);
    return $this->db->select(
        "SELECT r.*, g.full_name AS guest_name, rt.name AS room_type_name
         FROM reservations r
         JOIN guests g ON g.id = r.guest_id
         JOIN room_types rt ON rt.id = r.room_type_id
         WHERE r.status = '$st'
         ORDER BY r.check_in"
    );
}


public function updateReservationStatus($reservation_id, $status)
{
    $rid = (int)$reservation_id;
    $st  = $this->db->escape($status);
    return $this->db->update("UPDATE reservations SET status = '$st' WHERE id = $rid");
}


}

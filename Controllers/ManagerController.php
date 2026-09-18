<?php
require_once __DIR__ . '/DBController.php';
require_once __DIR__ . '/../Models/Room.php';
require_once __DIR__ . '/../Models/User.php';


class ManagerController
{
    private $db;

    public function __construct()
    {
        $this->db = DBController::getInstance();
    }

    public function dashboardKpis() {
        $rooms = $this->db->selectOne("SELECT COUNT(*) AS c FROM rooms");
        $occ   = $this->db->selectOne("SELECT COUNT(*) AS c FROM rooms WHERE occupancy = 'occupied'");
        $rev   = $this->db->selectOne("SELECT IFNULL(SUM(amount),0) AS s FROM payments WHERE DATE(received_at) = CURDATE()");
        $arr   = $this->db->selectOne("SELECT COUNT(*) AS c FROM reservations WHERE check_in = CURDATE() AND status = 'booked'");
        $dep   = $this->db->selectOne("SELECT COUNT(*) AS c FROM reservations WHERE check_out = CURDATE() AND status = 'checked_in'");
        $ooo   = $this->db->selectOne("SELECT COUNT(*) AS c FROM rooms WHERE status = 'out_of_order'");

        $occupancy = 0;
        if ($rooms['c'] > 0) $occupancy = round(($occ['c'] / $rooms['c']) * 100, 1);

        return [
            'rooms_total'    => $rooms['c'],
            'rooms_occupied' => $occ['c'],
            'occupancy_pct'  => $occupancy,
            'revenue_today'  => $rev['s'],
            'arrivals_today' => $arr['c'],
            'departures_today'=> $dep['c'],
            'out_of_order'   => $ooo['c']
        ];
    }

    public function occupancyReport($from, $to) {
        $f = $this->db->escape($from);
        $t = $this->db->escape($to);
        return $this->db->select(
            "SELECT DATE(check_in) AS d, COUNT(*) AS bookings, SUM(rate) AS revenue
             FROM reservations
             WHERE check_in BETWEEN '$f' AND '$t' AND status IN ('booked','checked_in','checked_out')
             GROUP BY DATE(check_in) ORDER BY d"
        );
    }

    public function revenueReport($from, $to) {
        $f = $this->db->escape($from);
        $t = $this->db->escape($to);
        return $this->db->select(
            "SELECT DATE(received_at) AS d, method, SUM(amount) AS total
             FROM payments
             WHERE DATE(received_at) BETWEEN '$f' AND '$t'
             GROUP BY DATE(received_at), method ORDER BY d, method"
        );
    }

    public function topGuests() {
        return $this->db->select(
            "SELECT g.id, g.full_name, g.loyalty_tier, g.loyalty_points,
                    COUNT(r.id) AS stays,
                    IFNULL(SUM(f.total_charges),0) AS spend
             FROM guests g
             LEFT JOIN reservations r ON r.guest_id = g.id
             LEFT JOIN folios f ON f.reservation_id = r.id
             GROUP BY g.id
             ORDER BY spend DESC LIMIT 20"
        );
    }

    public function runNightAudit($user_id) {
        $today = date('Y-m-d');
        $exists = $this->db->selectOne("SELECT id FROM night_audit WHERE audit_date = '$today'");
        if ($exists !== null) return 0;

        $ns = $this->db->select(
            "SELECT id, room_id FROM reservations WHERE status = 'booked' AND check_in <= '$today'"
        );
        $no_show_count = 0;
        foreach ($ns as $r) {
            $rid = (int)$r['id'];
            $this->db->update("UPDATE reservations SET status = 'no_show' WHERE id = $rid");
            $no_show_count++;
        }

        $rev = $this->db->selectOne("SELECT IFNULL(SUM(total_charges),0) AS s FROM folios WHERE DATE(created_at) = '$today'");
        $pay = $this->db->selectOne("SELECT IFNULL(SUM(amount),0) AS s FROM payments WHERE DATE(received_at) = '$today'");
        $occ = $this->db->selectOne("SELECT COUNT(*) AS c FROM rooms WHERE occupancy = 'occupied'");
        $av  = $this->db->selectOne("SELECT COUNT(*) AS c FROM rooms WHERE occupancy = 'available' AND status = 'clean'");

        $uid = (int)$user_id;
        $this->db->insert(
            "INSERT INTO night_audit (audit_date, total_revenue, total_payments, rooms_occupied, rooms_available, no_shows, run_by)
             VALUES ('$today', {$rev['s']}, {$pay['s']}, {$occ['c']}, {$av['c']}, $no_show_count, $uid)"
        );
        $this->db->insert(
            "INSERT INTO audit_log (user_id, action, entity, details) 
             VALUES ($uid, 'night_audit', 'system', 'No-shows: $no_show_count')"
        );
        return $no_show_count;
    }

        public function blacklistGuest($guest_id, $blacklist, $reason = '')
        {
            $gid = (int)$guest_id;
            $val = $blacklist ? 1 : 0;
            $reasonEscaped = $this->db->escape($reason);

            $this->db->update("UPDATE guests SET blacklisted = $val, ban_reason = '$reasonEscaped' WHERE id = $gid");
            return true;
        }

    
    
    
    
    
    
    

    
    
    
    
    
    
    
    
    

    
    
    
    
    
    
    
    

    public function listNightAudits() {
        return $this->db->select("SELECT * FROM night_audit ORDER BY audit_date DESC LIMIT 60");
    }

    
    public function addRoom(Room $room)
    {
        $room_no = $this->db->escape($room->room_no);
        $exists = $this->db->selectOne("SELECT id FROM rooms WHERE room_no = '$room_no'");
        if ($exists !== null) return false;
        $type_id = (int)$room->room_type_id;
        $floor   = (int)$room->floor;
        $desc    = $this->db->escape($room->notes);
        $image   = $room->image ? "'" . $this->db->escape($room->image) . "'" : "NULL";
        return $this->db->insert(
            "INSERT INTO rooms (room_no, room_type_id, floor, occupancy, status, notes, image)
             VALUES ('$room_no', $type_id, $floor,'available','clean', '$desc', $image)"
        );
    }

    public function getRoomById($id)
{
    return $this->db->selectOne(
        "SELECT r.*, rt.name AS type_name, rt.base_price
         FROM rooms r
         JOIN room_types rt ON rt.id = r.room_type_id
         WHERE r.id = " . (int)$id
    );
}

    public function listAllRoomsWithStatus() {
    return $this->db->select(
        "SELECT r.id, r.room_no, r.floor, r.occupancy, r.status, r.notes, r.image,
                rt.name AS type_name, rt.base_price
         FROM rooms r JOIN room_types rt ON rt.id = r.room_type_id
         ORDER BY r.floor, r.room_no"
    );
}

    
    public function listGuests() {
        return $this->db->select("SELECT * FROM guests ORDER BY id DESC LIMIT 200");
    }

    public function listUsers() {
        return $this->db->select("SELECT * FROM users ORDER BY id DESC LIMIT 200");
    }

    
    public function listRoomTypes() {
        return $this->db->select("SELECT * FROM room_types ORDER BY base_price");
    }

    
    public function listServices() {
        return $this->db->select("SELECT * FROM services ORDER BY category, name");
    }

    
    
    public function createUser(User $user)
    {
        $u = $this->db->escape($user->username);
        $check = $this->db->selectOne("SELECT id FROM users WHERE username = '$u'");
        if ($check !== null) return false;
        $hash = password_hash($user->password, PASSWORD_BCRYPT);
        $fn   = $this->db->escape($user->full_name);
        $em   = $this->db->escape($user->email);
        $ph   = $this->db->escape($user->phone);
        $role = $this->db->escape($user->role);
        $userId = $this->db->insert(
            "INSERT INTO users (username, password, full_name, email, phone, role, active)
             VALUES ('$u', '$hash', '$fn', '$em', '$ph', '$role', 1)"
        );
        if ($userId && $role === 'guest') {
            $this->db->insert(
                "INSERT INTO guests (user_id, full_name, email, phone) 
                 VALUES ($userId, '$fn', '$em', '$ph')"
            );
        }
        return $userId;
}

    public function updateRoom($id, Room $room): bool
    {
        $room_no = $this->db->escape($room->room_no);
        $type_id = (int)$room->room_type_id;
        $floor   = (int)$room->floor;
        $status  = $this->db->escape($room->status);
        $desc    = $this->db->escape($room->notes);
        $img_sql = '';
        if ($room->image) {
            $img = $this->db->escape($room->image);
            $img_sql = ", image='$img'";
        }
        return $this->db->update(
            "UPDATE rooms SET room_no='$room_no', room_type_id=$type_id, 
             floor=$floor, status='$status', notes='$desc' $img_sql WHERE id=$id"
        );
    }

    

public function getUserById($id)
{
    return $this->db->selectOne("SELECT * FROM users WHERE id = " . (int)$id);
}

    public function updateUser($id, User $user): bool
    {
        $u  = $this->db->escape($user->username);
        $fn = $this->db->escape($user->full_name);
        $em = $this->db->escape($user->email);
        $ph = $this->db->escape($user->phone);
        $role = $this->db->escape($user->role);
        $sql = "UPDATE users SET username='$u', full_name='$fn', email='$em', phone='$ph', role='$role'";
        if (!empty($user->password)) {
            $hash = password_hash($user->password, PASSWORD_BCRYPT);
            $sql .= ", password='$hash'";
        }
        $sql .= " WHERE id = $id";
        return $this->db->update($sql);
    }

    public function generateFullReportPDF($from, $to)
    {
    require_once __DIR__ . '/../libs/dompdf/autoload.inc.php';

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new \Dompdf\Dompdf($options);

    
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Full Report</title>';
    $html .= '<style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1 { text-align: center; color: #2c3e50; }
        h2 { color: #3498db; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; color: #333; }
        .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #888; }
    </style>';
    $html .= '</head><body>';
    $html .= '<h1>Boutique Hotel - Full Report</h1>';
    $html .= '<p style="text-align:center;">Period: ' . $from . ' ~ ' . $to . '</p>';

    // ========== القسم 1: الإشغال ==========
    $html .= '<h2>1. Occupancy Report</h2>';
    $occupancyData = $this->occupancyReport($from, $to);
    if (empty($occupancyData)) {
        $html .= '<p>No occupancy data for this period.</p>';
    } else {
        $html .= '<table><thead><tr><th>Date</th><th>Bookings</th><th>Revenue (EGP)</th></tr></thead><tbody>';
        foreach ($occupancyData as $row) {
            $html .= '<tr><td>' . $row['d'] . '</td><td>' . $row['bookings'] . '</td><td>' . number_format($row['revenue'], 2) . '</td></tr>';
        }
        $html .= '</tbody></table>';
    }

    // ========== القسم 2: الإيرادات ==========
    $html .= '<h2>2. Revenue Report</h2>';
    $revenueData = $this->revenueReport($from, $to);
    if (empty($revenueData)) {
        $html .= '<p>No revenue data for this period.</p>';
    } else {
        $html .= '<table><thead><tr><th>Date</th><th>Method</th><th>Total (EGP)</th></tr></thead><tbody>';
        foreach ($revenueData as $row) {
            $html .= '<tr><td>' . $row['d'] . '</td><td>' . $row['method'] . '</td><td>' . number_format($row['total'], 2) . '</td></tr>';
        }
        $html .= '</tbody></table>';
    }

    // ========== القسم 3: أفضل النزلاء ==========
    $html .= '<h2>3. Top Guests</h2>';
    $topGuestsData = $this->topGuests();
    if (empty($topGuestsData)) {
        $html .= '<p>No guest data available.</p>';
    } else {
        $html .= '<table><thead><tr><th>ID</th><th>Name</th><th>Tier</th><th>Points</th><th>Stays</th><th>Spend (EGP)</th></tr></thead><tbody>';
        foreach ($topGuestsData as $row) {
            $html .= '<tr><td>' . $row['id'] . '</td><td>' . htmlspecialchars($row['full_name']) . '</td><td>' . $row['loyalty_tier'] . '</td><td>' . $row['loyalty_points'] . '</td><td>' . $row['stays'] . '</td><td>' . number_format($row['spend'], 2) . '</td></tr>';
        }
        $html .= '</tbody></table>';
    }

    $html .= '<div class="footer">Generated on ' . date('Y-m-d H:i:s') . '</div>';
    $html .= '</body></html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('Full_Report_' . $from . '_to_' . $to . '.pdf', ["Attachment" => true]);
    exit;
}}

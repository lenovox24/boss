<?php
// File: hokiraja/api_get_wallet_transactions.php
// REVISI TOTAL: Menggabungkan data Deposit & Withdraw

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';

// 1. Validasi Keamanan: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Ambil Filter dari Request
$date_range_str = $_GET['date_range'] ?? '';
$type_filter = $_GET['type'] ?? 'all';

// 3. Bangun Query SQL secara dinamis dan aman
$params = [];
$types = "";

// Query untuk Deposit
$sql_deposit = "
    SELECT 
        'Deposit' as transaction_type, 
        amount, 
        status, 
        created_at
    FROM deposit_transactions
    WHERE user_id = ?
";
$params_deposit = [$user_id];
$types_deposit = "i";

// Query untuk Withdraw
$sql_withdraw = "
    SELECT 
        'Withdraw' as transaction_type, 
        amount, 
        status, 
        created_at
    FROM transactions
    WHERE user_id = ? AND type = 'withdraw'
";
$params_withdraw = [$user_id];
$types_withdraw = "i";

// Tambahkan filter tanggal jika diberikan
if (!empty($date_range_str) && count($dates = explode(' - ', $date_range_str)) === 2) {
    $start_date = $dates[0] . ' 00:00:00';
    $end_date = $dates[1] . ' 23:59:59';

    $sql_deposit .= " AND created_at BETWEEN ? AND ?";
    $params_deposit[] = $start_date;
    $params_deposit[] = $end_date;
    $types_deposit .= "ss";

    $sql_withdraw .= " AND created_at BETWEEN ? AND ?";
    $params_withdraw[] = $start_date;
    $params_withdraw[] = $end_date;
    $types_withdraw .= "ss";
}

// Gabungkan query berdasarkan filter tipe
$final_sql = "";
if ($type_filter === 'deposit') {
    $final_sql = $sql_deposit;
    $params = $params_deposit;
    $types = $types_deposit;
} elseif ($type_filter === 'withdraw') {
    $final_sql = $sql_withdraw;
    $params = $params_withdraw;
    $types = $types_withdraw;
} else { // 'all' atau lainnya
    $final_sql = "($sql_deposit) UNION ALL ($sql_withdraw)";
    $params = array_merge($params_deposit, $params_withdraw);
    $types = $types_deposit . $types_withdraw;
}

$final_sql .= " ORDER BY created_at DESC";

// 4. Eksekusi Query
$results = [];
$stmt = $conn->prepare($final_sql);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result_set = $stmt->get_result();
    while ($row = $result_set->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query: ' . $conn->error]);
    $conn->close();
    exit();
}

// 5. Kirim Hasil
$conn->close();
echo json_encode($results);

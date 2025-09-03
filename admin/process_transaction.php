<?php
// File: admin/process_transaction.php (REVISI FINAL: Memproses Deposit/Withdraw dari Tabel Berbeda)

session_start();
require_once '../includes/db_connect.php';

// Keamanan: Cek login admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Validasi input
if (!isset($_GET['id']) || !isset($_GET['action']) || !in_array($_GET['action'], ['approve', 'reject']) || !isset($_GET['type']) || !in_array($_GET['type'], ['deposit', 'withdraw'])) {
    $_SESSION['error_message'] = "Aksi atau tipe transaksi tidak valid.";
    header("Location: manage_transactions.php");
    exit();
}

$transaction_id = (int)$_GET['id'];
$action = $_GET['action'];
$type = $_GET['type']; // Tipe transaksi: 'deposit' atau 'withdraw'

// Mulai Database Transaction: semua query harus berhasil, atau tidak sama sekali.
$conn->begin_transaction();

try {
    $table_name = '';
    $id_column = 'id'; // Default ID column name
    $amount_column = 'amount'; // Default amount column name

    if ($type === 'deposit') {
        $table_name = 'deposit_transactions';
    } elseif ($type === 'withdraw') {
        $table_name = 'transactions'; // Asumsi withdraw masih di tabel 'transactions'
    } else {
        throw new Exception("Tipe transaksi tidak dikenal.");
    }

    // 1. Kunci dan ambil data transaksi yang masih pending
    // Menggunakan Prepared Statement untuk nama tabel dinamis lebih kompleks atau tidak direkomendasikan.
    // Pastikan $table_name berasal dari daftar yang aman (sudah divalidasi di atas).
    $stmt = $conn->prepare("SELECT * FROM " . $table_name . " WHERE " . $id_column . " = ? AND status = 'pending' FOR UPDATE");
    if (!$stmt) {
        throw new Exception("Prepare failed on select transaction: " . $conn->error);
    }
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Transaksi tidak ditemukan atau sudah diproses.");
    }

    $transaction = $result->fetch_assoc();
    $user_id = $transaction['user_id'];
    $amount = $transaction[$amount_column]; // Ambil jumlah dari kolom amount
    // $type di sini adalah dari GET param, bukan dari kolom DB. Kolom DB bisa saja 'type' atau 'channel_type'.
    // Untuk withdraw kita ambil 'type' dari DB 'transactions'. Untuk deposit kita pakai 'channel_type' dari DB.
    // Namun, yang penting di sini adalah $type dari GET param ('deposit'/'withdraw') untuk logika saldo.


    // 2. Jika aksi adalah 'approve', update saldo user
    if ($action === 'approve') {
        if ($type === 'deposit') { // Ini adalah deposit (dari deposit_transactions)
            // Tambah saldo user
            $update_balance_stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            if (!$update_balance_stmt) {
                throw new Exception("Prepare failed on user balance update (deposit): " . $conn->error);
            }
            $update_balance_stmt->bind_param("di", $amount, $user_id);
            $update_balance_stmt->execute();
        } elseif ($type === 'withdraw') { // Ini adalah withdraw (dari transactions)
            // Kurangi saldo user, pastikan saldo mencukupi
            $update_balance_stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?");
            if (!$update_balance_stmt) {
                throw new Exception("Prepare failed on user balance update (withdraw): " . $conn->error);
            }
            $update_balance_stmt->bind_param("did", $amount, $user_id, $amount);
            $update_balance_stmt->execute();

            // Cek apakah saldo cukup. Jika tidak, batalkan.
            if ($update_balance_stmt->affected_rows === 0) {
                throw new Exception("Gagal memproses withdraw: Saldo user tidak mencukupi atau transaksi sudah diproses.");
            }
        }
    }

    // 3. Update status transaksi menjadi 'approved' atau 'rejected'
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';

    // Update kolom status di tabel yang sesuai
    $update_trans_stmt = $conn->prepare("UPDATE " . $table_name . " SET status = ?, processed_at = NOW() WHERE " . $id_column . " = ?");
    if (!$update_trans_stmt) {
        throw new Exception("Prepare failed on transaction status update: " . $conn->error);
    }
    $update_trans_stmt->bind_param("si", $new_status, $transaction_id);
    $update_trans_stmt->execute();

    // Jika semua query berhasil, commit perubahan
    $conn->commit();
    $_SESSION['success_message'] = "Transaksi #" . $transaction_id . " (" . ucfirst($type) . ") berhasil di-" . $action . ".";
} catch (Exception $e) {
    // Jika ada error, batalkan semua perubahan (rollback)
    $conn->rollback();
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
}

// Redirect kembali ke halaman manajemen transaksi, menjaga filter type
header("Location: manage_transactions.php?type=" . $type);
exit();

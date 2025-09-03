<?php
// File: process_deposit_non_api.php (REVISI FINAL 3: Perbaikan Tipe Data bind_param yang BENAR)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';

// Validasi dasar: pastikan user login dan request method adalah POST
if (!isset($_SESSION['user_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['deposit_error'] = "Sesi tidak valid atau metode request salah.";
    header("Location: deposit.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. CEK ULANG: Pastikan tidak ada deposit yang masih PENDING untuk user ini
$check_stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM deposit_transactions WHERE user_id = ? AND status = 'pending'");
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$pending_count = $check_stmt->get_result()->fetch_assoc()['pending_count'];
$check_stmt->close();

if ($pending_count > 0) {
    $_SESSION['deposit_error'] = "Gagal, Anda masih memiliki transaksi deposit yang sedang diproses.";
    header("Location: deposit.php");
    exit();
}

// 2. PROSES DATA JIKA AMAN
$amount_raw = $_POST['amount'] ?? '';
$amount = (float)str_replace('.', '', $amount_raw);
$bonus_id = filter_input(INPUT_POST, 'bonus_id', FILTER_VALIDATE_INT) ?: null;
$admin_deposit_account_id = filter_input(INPUT_POST, 'admin_deposit_account_id', FILTER_VALIDATE_INT) ?: null;
$remark = filter_input(INPUT_POST, 'remark', FILTER_SANITIZE_STRING);
$channel_type = $_POST['channel_type'] ?? 'bank_transfer';
$payment_method_code = $_POST['payment_method_code'] ?? null;
if ($channel_type === 'bank_transfer' && !empty($admin_deposit_account_id)) {
    $stmt_get_code = $conn->prepare("SELECT method_code FROM admin_deposit_accounts WHERE id = ?");
    $stmt_get_code->bind_param("i", $admin_deposit_account_id);
    $stmt_get_code->execute();
    $result_code = $stmt_get_code->get_result();
    if ($row_code = $result_code->fetch_assoc()) {
        $payment_method_code = $row_code['method_code'];
    }
    $stmt_get_code->close();
}
if (empty($amount) || $amount < 10000) {
    $_SESSION['deposit_error'] = "Jumlah deposit tidak valid atau kurang dari minimum (IDR 10.000).";
    header("Location: deposit.php");
    exit();
}

$proof_filename = NULL;
if (isset($_FILES['proof_of_transfer']) && $_FILES['proof_of_transfer']['error'] == UPLOAD_ERR_OK) {
    $target_dir = "assets/uploads/proofs/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $file_extension = strtolower(pathinfo($_FILES['proof_of_transfer']['name'], PATHINFO_EXTENSION));
    $unique_filename = uniqid('proof_', true) . '.' . $file_extension;
    $target_file = $target_dir . $unique_filename;
    if (move_uploaded_file($_FILES['proof_of_transfer']['tmp_name'], $target_file)) {
        $proof_filename = $unique_filename;
    }
}

// 3. SIMPAN KE DATABASE
$status = 'pending';
$stmt = $conn->prepare("INSERT INTO deposit_transactions (user_id, channel_type, payment_method_code, admin_deposit_account_id, amount, bonus_id, proof_of_transfer_url, remark, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Tipe data yang benar: i, s, s, i, d, i, s, s, s -> total 9
$stmt->bind_param("issidisss", $user_id, $channel_type, $payment_method_code, $admin_deposit_account_id, $amount, $bonus_id, $proof_filename, $remark, $status);
if ($stmt->execute()) {
    $_SESSION['deposit_submitted_alert'] = "Permintaan deposit Anda telah berhasil dikirim dan sedang diproses.";
    header("Location: beranda.php");
} else {
    $_SESSION['deposit_error'] = "Terjadi kesalahan teknis saat menyimpan data: " . $stmt->error;
    header("Location: deposit.php");
}

$stmt->close();
$conn->close();
exit();

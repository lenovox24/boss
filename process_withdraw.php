<?php
// File: Hokiraja/process_withdraw.php

session_start();
require_once 'includes/db_connect.php';

// 1. Validasi Keamanan & Input
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: withdraw.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$amount_raw = $_POST['amount'] ?? '0';
$password = $_POST['password'] ?? '';
$amount = (float)str_replace('.', '', $amount_raw);

// Validasi minimal withdraw
if ($amount < 50000) {
    $_SESSION['withdraw_error'] = "Jumlah penarikan minimal adalah IDR 50.000.";
    header("Location: withdraw.php");
    exit();
}

// 2. Verifikasi Password Pengguna
$stmt_user = $conn->prepare("SELECT password, balance FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['withdraw_error'] = "Password yang Anda masukkan salah.";
    header("Location: withdraw.php");
    exit();
}

// 3. Cek Saldo Pengguna
if ($amount > $user['balance']) {
    $_SESSION['withdraw_error'] = "Saldo Anda tidak mencukupi untuk melakukan penarikan ini.";
    header("Location: withdraw.php");
    exit();
}

// 4. PENJAGA: Cek apakah sudah ada permintaan withdraw yang pending
$check_stmt = $conn->prepare("SELECT id FROM transactions WHERE user_id = ? AND type = 'withdraw' AND status = 'pending'");
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    $_SESSION['withdraw_error'] = "Anda sudah memiliki permintaan penarikan yang sedang diproses.";
    header("Location: withdraw.php");
    exit();
}
$check_stmt->close();


// 5. Simpan Permintaan Withdraw ke Database
$stmt_insert = $conn->prepare(
    "INSERT INTO transactions (user_id, type, amount, status, created_at) VALUES (?, 'withdraw', ?, 'pending', NOW())"
);
$stmt_insert->bind_param("id", $user_id, $amount);

if ($stmt_insert->execute()) {
    // Set session untuk notifikasi SweetAlert di halaman beranda
    $_SESSION['withdraw_submitted_alert'] = "Permintaan penarikan Anda sebesar IDR " . number_format($amount, 0, ',', '.') . " telah berhasil dikirim dan sedang diproses.";
    header("Location: beranda.php");
} else {
    $_SESSION['withdraw_error'] = "Terjadi kesalahan pada server. Silakan coba lagi.";
    header("Location: withdraw.php");
}

$stmt_insert->close();
$conn->close();
exit();

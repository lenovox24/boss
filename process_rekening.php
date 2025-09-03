<?php
// File: Hokiraja/process_rekening.php

session_start();
require_once 'includes/db_connect.php';

// 1. Validasi Keamanan & Input
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: rekening.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$bank_name = $_POST['bank_name'] ?? '';
$account_number = $_POST['account_number'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi input tidak boleh kosong
if (empty($bank_name) || empty($account_number) || empty($password)) {
    $_SESSION['rekening_error'] = "Semua kolom wajib diisi.";
    header("Location: rekening.php");
    exit();
}

// 2. Verifikasi Password Pengguna
$stmt_user = $conn->prepare("SELECT password, full_name FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['rekening_error'] = "Password yang Anda masukkan salah.";
    header("Location: rekening.php");
    exit();
}

// 3. Cek apakah bank sudah ada untuk user ini
$check_bank_stmt = $conn->prepare("SELECT id FROM user_banks WHERE user_id = ? AND bank_name = ?");
$check_bank_stmt->bind_param("is", $user_id, $bank_name);
$check_bank_stmt->execute();
if ($check_bank_stmt->get_result()->num_rows > 0) {
    $_SESSION['rekening_error'] = "Anda sudah memiliki rekening untuk " . htmlspecialchars($bank_name) . ".";
    header("Location: rekening.php");
    exit();
}
$check_bank_stmt->close();

// 4. Ambil nama dari rekening utama
$primary_bank_stmt = $conn->prepare("SELECT account_name FROM user_banks WHERE user_id = ? AND is_primary = 1");
$primary_bank_stmt->bind_param("i", $user_id);
$primary_bank_stmt->execute();
$primary_bank = $primary_bank_stmt->get_result()->fetch_assoc();
$account_name = $primary_bank['account_name'];
$primary_bank_stmt->close();

// 5. Simpan Rekening Baru ke Database
$insert_stmt = $conn->prepare(
    "INSERT INTO user_banks (user_id, bank_name, account_number, account_name, is_primary) VALUES (?, ?, ?, ?, 0)"
);
$insert_stmt->bind_param("isss", $user_id, $bank_name, $account_number, $account_name);

if ($insert_stmt->execute()) {
    $_SESSION['rekening_success'] = "Rekening baru berhasil ditambahkan!";
} else {
    $_SESSION['rekening_error'] = "Terjadi kesalahan pada server. Gagal menambahkan rekening.";
}

$insert_stmt->close();
$conn->close();
header("Location: rekening.php");
exit();

<?php
// File: Hokiraja/process_profil.php

session_start();
require_once 'includes/db_connect.php';

// 1. Validasi Keamanan & Input
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: profil.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$old_password = $_POST['old_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];
$bank_digits = $_POST['bank_digits'];

// 2. Ambil data user saat ini dari DB
$stmt_user = $conn->prepare(
    "SELECT u.password, ub.account_number 
     FROM users u
     LEFT JOIN user_banks ub ON u.id = ub.user_id AND ub.is_primary = 1
     WHERE u.id = ?"
);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

// 3. Lakukan semua validasi
if (!$user_data) {
    $_SESSION['profil_error'] = "Data pengguna tidak ditemukan.";
} elseif (!password_verify($old_password, $user_data['password'])) {
    $_SESSION['profil_error'] = "Password Lama yang Anda masukkan salah.";
} elseif ($new_password !== $confirm_password) {
    $_SESSION['profil_error'] = "Konfirmasi password baru tidak cocok.";
} elseif (strlen($new_password) < 6 || !preg_match('/[A-Za-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
    $_SESSION['profil_error'] = "Password baru harus minimal 6 digit dan mengandung huruf serta angka.";
} elseif (substr($user_data['account_number'], -4) !== $bank_digits) {
    $_SESSION['profil_error'] = "4 digit terakhir nomor rekening tidak cocok.";
} else {
    // 4. Jika semua validasi lolos, update password
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update_stmt->bind_param("si", $hashed_new_password, $user_id);

    if ($update_stmt->execute()) {
        $_SESSION['profil_success'] = "Password Anda berhasil diperbarui!";
    } else {
        $_SESSION['profil_error'] = "Terjadi kesalahan pada server. Gagal memperbarui password.";
    }
    $update_stmt->close();
}

$conn->close();
header("Location: profil.php");
exit();

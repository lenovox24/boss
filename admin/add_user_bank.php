<?php
// File: admin/add_user_bank.php

session_start();
require_once '../includes/db_connect.php';

// Keamanan: Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Cek jika metode request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $bank_name = $_POST['bank_name'];
    $account_number = $_POST['account_number'];
    $account_name = $_POST['account_name'];
    $is_primary = isset($_POST['is_primary']) ? 1 : 0;

    // Jika rekening ini diset sebagai utama, pastikan tidak ada rekening utama lain untuk user ini
    if ($is_primary == 1) {
        $update_stmt = $conn->prepare("UPDATE user_banks SET is_primary = 0 WHERE user_id = ?");
        $update_stmt->bind_param("i", $user_id);
        $update_stmt->execute();
        $update_stmt->close();
    }

    // Masukkan data rekening baru
    $stmt = $conn->prepare("INSERT INTO user_banks (user_id, bank_name, account_number, account_name, is_primary) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $user_id, $bank_name, $account_number, $account_name, $is_primary);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Rekening bank baru berhasil ditambahkan.";
    } else {
        $_SESSION['error_message'] = "Gagal menambahkan rekening bank: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();

    // Redirect kembali ke halaman kelola rekening user
    header("Location: view_user_banks.php?user_id=" . $user_id);
    exit();
} else {
    // Jika diakses langsung, redirect ke halaman manajemen user
    header("Location: manage_users.php");
    exit();
}

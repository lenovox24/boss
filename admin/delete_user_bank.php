<?php
// File: admin/delete_user_bank.php

session_start();
require_once '../includes/db_connect.php';

// Keamanan: Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Cek apakah ID rekening bank ada di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID Rekening tidak valid.";
    header("Location: manage_users.php");
    exit();
}

$bank_id = $_GET['id'];

// Ambil user_id terlebih dahulu untuk redirect kembali
$stmt_select = $conn->prepare("SELECT user_id FROM user_banks WHERE id = ?");
$stmt_select->bind_param("i", $bank_id);
$stmt_select->execute();
$result = $stmt_select->get_result();
if ($result->num_rows === 1) {
    $bank = $result->fetch_assoc();
    $user_id = $bank['user_id'];

    // Hapus record dari database
    $stmt_delete = $conn->prepare("DELETE FROM user_banks WHERE id = ?");
    $stmt_delete->bind_param("i", $bank_id);

    if ($stmt_delete->execute()) {
        $_SESSION['success_message'] = "Rekening bank berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus rekening bank: " . $stmt_delete->error;
    }
    $stmt_delete->close();

    // Redirect kembali ke halaman kelola rekening user
    header("Location: view_user_banks.php?user_id=" . $user_id);
    exit();
} else {
    $_SESSION['error_message'] = "Rekening bank tidak ditemukan.";
    header("Location: manage_users.php");
    exit();
}

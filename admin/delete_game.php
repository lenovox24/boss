<?php
// File: admin/delete_game.php

// Mulai session untuk menggunakan notifikasi
session_start();

// Sertakan file koneksi dan penjaga
require_once '../includes/db_connect.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Cek apakah ID game ada di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID Game tidak valid.";
    header("Location: manage_games.php");
    exit();
}

$game_id = $_GET['id'];

// 1. Ambil nama file gambar dari database sebelum menghapus record
$stmt_select = $conn->prepare("SELECT gambar_thumbnail FROM games WHERE id = ?");
$stmt_select->bind_param("i", $game_id);
$stmt_select->execute();
$result = $stmt_select->get_result();
if ($result->num_rows === 1) {
    $game = $result->fetch_assoc();
    $image_to_delete = $game['gambar_thumbnail'];

    // 2. Hapus record dari database
    $stmt_delete = $conn->prepare("DELETE FROM games WHERE id = ?");
    $stmt_delete->bind_param("i", $game_id);

    if ($stmt_delete->execute()) {
        // 3. Jika record berhasil dihapus, hapus file gambar dari server (hanya untuk file lokal)
        if (!empty($image_to_delete) && !filter_var($image_to_delete, FILTER_VALIDATE_URL)) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/hokiraja/assets/images/games/' . $image_to_delete;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $_SESSION['success_message'] = "Game berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus game: " . $stmt_delete->error;
    }
    $stmt_delete->close();
} else {
    $_SESSION['error_message'] = "Game tidak ditemukan.";
}

$stmt_select->close();
$conn->close();

// Redirect kembali ke halaman manajemen game
header("Location: manage_games.php");
exit();

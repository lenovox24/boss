<?php
// File: admin/delete_provider.php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: manage_games.php?tab=providers");
    exit();
}

$provider_id = (int)$_GET['id'];

// Ambil nama file logo sebelum menghapus record
$stmt = $conn->prepare("SELECT logo_provider FROM providers WHERE id = ?");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $provider = $result->fetch_assoc();
    $logo_to_delete = $provider['logo_provider'];

    // Hapus record dari database
    $delete_stmt = $conn->prepare("DELETE FROM providers WHERE id = ?");
    $delete_stmt->bind_param("i", $provider_id);
    if ($delete_stmt->execute()) {
        // Hapus file logo dari server
        if (!empty($logo_to_delete)) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/hokiraja/assets/images/providers/' . $logo_to_delete;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $_SESSION['success_message'] = "Provider berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus provider.";
    }
    $delete_stmt->close();
} else {
    $_SESSION['error_message'] = "Provider tidak ditemukan.";
}
$stmt->close();
$conn->close();

header("Location: manage_games.php?tab=providers");
exit();

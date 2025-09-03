<?php
// File: admin/delete_promo.php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: manage_promo.php");
    exit();
}

$promo_id = (int)$_GET['id'];

// Ambil nama file gambar sebelum menghapus record
$stmt = $conn->prepare("SELECT image_url FROM promotions WHERE id = ?");
$stmt->bind_param("i", $promo_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $promo = $result->fetch_assoc();
    $image_to_delete = $promo['image_url'];

    // Hapus record dari database
    $delete_stmt = $conn->prepare("DELETE FROM promotions WHERE id = ?");
    $delete_stmt->bind_param("i", $promo_id);
    if ($delete_stmt->execute()) {
        // Hapus file gambar dari server
        if (!empty($image_to_delete)) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/hokiraja/assets/images/promos/' . $image_to_delete;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $_SESSION['success_message'] = "Promo berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus promo.";
    }
    $delete_stmt->close();
} else {
    $_SESSION['error_message'] = "Promo tidak ditemukan.";
}
$stmt->close();
$conn->close();

header("Location: manage_promo.php");
exit();

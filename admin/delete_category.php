<?php
// File: admin/delete_category.php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: manage_games.php?tab=categories");
    exit();
}

$category_id = (int)$_GET['id'];

// Ambil nama file gambar sebelum menghapus record
$stmt = $conn->prepare("SELECT image FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $category = $result->fetch_assoc();
    $image_to_delete = $category['image'];

    // Hapus record dari database
    $delete_stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $delete_stmt->bind_param("i", $category_id);
    if ($delete_stmt->execute()) {
        // Hapus file gambar dari server
        if (!empty($image_to_delete)) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/hokiraja/assets/images/categories/' . $image_to_delete;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $_SESSION['success_message'] = "Kategori berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus kategori.";
    }
    $delete_stmt->close();
} else {
    $_SESSION['error_message'] = "Kategori tidak ditemukan.";
}
$stmt->close();
$conn->close();

header("Location: manage_games.php?tab=categories");
exit();

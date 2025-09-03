<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_accounts.php");
    exit();
}

// Ambil data dari form
$account_id = $_POST['account_id'] ?? null;
$method_code = $_POST['method_code'];
$account_name = $_POST['account_name'];
$account_number = $_POST['account_number'];
$min_deposit = $_POST['min_deposit'];
$max_deposit = $_POST['max_deposit'];
$is_active = isset($_POST['is_active']) ? 1 : 0;

if ($account_id) {
    // Mode Edit
    $stmt = $conn->prepare("UPDATE admin_deposit_accounts SET method_code = ?, account_name = ?, account_number = ?, min_deposit = ?, max_deposit = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("sssddii", $method_code, $account_name, $account_number, $min_deposit, $max_deposit, $is_active, $account_id);
    $_SESSION['success_message'] = "Rekening berhasil diperbarui!";
} else {
    // Mode Tambah
    $stmt = $conn->prepare("INSERT INTO admin_deposit_accounts (method_code, account_name, account_number, min_deposit, max_deposit, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssddi", $method_code, $account_name, $account_number, $min_deposit, $max_deposit, $is_active);
    $_SESSION['success_message'] = "Rekening baru berhasil ditambahkan!";
}

$stmt->execute();
$stmt->close();
$conn->close();

header("Location: manage_accounts.php");
exit();

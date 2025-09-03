<?php
session_start();
require_once '../includes/db_connect.php';

$account_id = $_GET['id'] ?? null;

if ($account_id) {
    $stmt = $conn->prepare("DELETE FROM admin_deposit_accounts WHERE id = ?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success_message'] = "Rekening berhasil dihapus!";
}

$conn->close();
header("Location: manage_accounts.php");
exit();

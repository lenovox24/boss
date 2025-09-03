<?php
// File: Hokiraja/get_memos.php (REVISI PENUH - DIJAMIN BENAR)
session_start();
require_once 'includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$box = $_GET['box'] ?? 'inbox';
$memos = [];

if ($box === 'inbox') {
    // Mengambil pesan yang dikirim OLEH ADMIN untuk user ini
    $sql = "SELECT m.id, m.subject, m.sent_at, m.is_read, a.username as sender_name 
            FROM memos m 
            JOIN admins a ON m.sender_id = a.id AND m.sender_type = 'admin'
            WHERE m.recipient_id = ? AND m.recipient_deleted = 0
            ORDER BY m.sent_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else { // 'sent'
    // Mengambil pesan yang dikirim OLEH USER ini untuk admin
    $sql = "SELECT id, subject, sent_at, is_read FROM memos 
            WHERE sender_id = ? AND sender_type = 'user' AND sender_deleted = 0
            ORDER BY sent_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $memos[] = $row;
}

echo json_encode($memos);
$stmt->close();
$conn->close();

<?php
// File: Hokiraja/admin/api_admin_get_memos.php (REVISI TOTAL)
session_start();
require_once '../includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Akses ditolak. Sesi tidak valid.']);
    exit;
}

$action = $_GET['action'] ?? '';
$admin_id = $_SESSION['admin_id'];

// === BAGIAN UNTUK MENGAMBIL DAFTAR PERCAKAPAN ===
if ($action === 'get_conversations') {
    // Query yang benar untuk mengambil semua pengguna yang pernah berinteraksi memo
    $sql = "SELECT 
                u.id as user_id,
                u.username,
                (SELECT subject FROM memos WHERE (sender_id = u.id AND sender_type = 'user') OR (recipient_id = u.id AND sender_type = 'admin') ORDER BY sent_at DESC LIMIT 1) as last_subject,
                (SELECT sent_at FROM memos WHERE (sender_id = u.id AND sender_type = 'user') OR (recipient_id = u.id AND sender_type = 'admin') ORDER BY sent_at DESC LIMIT 1) as last_message_time,
                (SELECT COUNT(*) FROM memos WHERE sender_id = u.id AND sender_type = 'user' AND recipient_id = ? AND is_read = 0) as unread_count
            FROM 
                users u
            WHERE EXISTS (
                SELECT 1 FROM memos m WHERE m.sender_id = u.id OR (m.recipient_id = u.id AND m.sender_type = 'admin')
            )
            ORDER BY 
                last_message_time DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $conversations = [];
    if ($result) {
        $conversations = $result->fetch_all(MYSQLI_ASSOC);
    }

    echo json_encode($conversations);
}

// === BAGIAN UNTUK MENGAMBIL ISI PESAN DARI SATU PERCAKAPAN ===
elseif ($action === 'get_messages' && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];

    // Tandai semua pesan dari user ini yang belum dibaca sebagai "sudah dibaca"
    $update_sql = "UPDATE memos SET is_read = 1 WHERE recipient_id = ? AND sender_id = ? AND sender_type = 'user' AND is_read = 0";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $admin_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Ambil semua pesan antara admin dan user yang dipilih
    $sql = "SELECT sender_type, subject, body, sent_at 
            FROM memos 
            WHERE (sender_id = ? AND sender_type = 'user' AND sender_deleted = 0) 
               OR (recipient_id = ? AND sender_id = ? AND sender_type = 'admin' AND recipient_deleted = 0)
            ORDER BY sent_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $user_id, $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($messages);
}

$conn->close();

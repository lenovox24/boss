<?php
// File: Hokiraja/process_memo.php
session_start();
require_once 'includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

switch ($action) {
    case 'send':
        // Logika untuk mengirim memo dari user ke admin
        $subject = $data['subject'];
        $body = $data['body'];
        // Asumsi admin recipient id adalah 1, atau bisa dicari dari db
        $admin_id = 1;

        $sql = "INSERT INTO memos (sender_id, sender_type, recipient_id, subject, body) VALUES (?, 'user', ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $user_id, $admin_id, $subject, $body);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengirim memo.']);
        }
        break;

    case 'delete':
        // Logika untuk menghapus memo
        $memo_ids = $data['ids'];
        $box = $data['box'];
        $id_placeholders = implode(',', array_fill(0, count($memo_ids), '?'));

        $field_to_update = ($box === 'inbox') ? 'recipient_deleted' : 'sender_deleted';

        $sql = "UPDATE memos SET $field_to_update = 1 WHERE id IN ($id_placeholders) AND ";
        $sql .= ($box === 'inbox') ? "recipient_id = ?" : "sender_id = ?";

        $stmt = $conn->prepare($sql);
        $types = str_repeat('i', count($memo_ids)) . 'i';
        $params = array_merge($memo_ids, [$user_id]);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus memo.']);
        }
        break;

    // Tambahkan case untuk 'mark_read' jika diperlukan

    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
        break;
}

$conn->close();

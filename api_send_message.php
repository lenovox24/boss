<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$message = trim(htmlspecialchars($data['message'] ?? ''));
$session_id = $data['session_id'] ?? session_id();

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong.']);
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$sender_type = 'user';

$stmt = $conn->prepare("INSERT INTO live_chat_messages (session_id, user_id, sender_type, message) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siss", $session_id, $user_id, $sender_type, $message);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim pesan.']);
}
$stmt->close();
$conn->close();

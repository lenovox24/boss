<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';
header('Content-Type: application/json');

// Pastikan admin sudah login
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit();
}
$admin_id = $_SESSION['admin_id'];

$data = json_decode(file_get_contents('php://input'), true);
$message = trim(htmlspecialchars($data['message'] ?? ''));
$session_id = $data['session_id'] ?? '';

if (empty($message) || empty($session_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    exit();
}

// Perbarui status 'last_seen' admin untuk menandakan online
$update_admin = $conn->prepare("UPDATE admins SET last_seen = NOW() WHERE id = ?");
$update_admin->bind_param("i", $admin_id);
$update_admin->execute();
$update_admin->close();

// Simpan pesan balasan dari admin
$sender_type = 'admin';
$stmt = $conn->prepare("INSERT INTO live_chat_messages (session_id, sender_type, message) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $session_id, $sender_type, $message);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim pesan.']);
}
$stmt->close();
$conn->close();

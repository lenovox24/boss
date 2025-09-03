<?php
// File: Hokiraja/admin/api_admin_send_memo.php (REVISI TOTAL)

session_start();
require_once '../includes/db_connect.php';
header('Content-Type: application/json');

// 1. Validasi Sesi Admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Sesi admin tidak valid.']);
    exit;
}
$admin_id = $_SESSION['admin_id'];

// 2. Ambil dan Validasi Input
$data = json_decode(file_get_contents('php://input'), true);
$recipient_id = $data['recipient_id'] ?? null;
$subject = trim($data['subject'] ?? '');
$body = trim($data['body'] ?? '');

if (empty($recipient_id) || empty($subject) || empty($body)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap. Semua kolom wajib diisi.']);
    exit;
}

// 3. Persiapkan dan Eksekusi Query dengan Aman
$sql = "INSERT INTO memos (sender_id, sender_type, recipient_id, subject, body) VALUES (?, 'admin', ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Gagal mempersiapkan query: ' . $conn->error]);
    exit;
}

// Tipe data: i (admin_id), i (recipient_id), s (subject), s (body)
$stmt->bind_param("iiss", $admin_id, $recipient_id, $subject, $body);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Memo berhasil terkirim.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan memo ke database: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

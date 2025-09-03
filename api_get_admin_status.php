<?php
require_once 'includes/db_connect.php';
header('Content-Type: application/json');

// Cek admin yang aktif dalam 5 menit terakhir
$stmt = $conn->query("SELECT COUNT(*) as online_count FROM admins WHERE last_seen > NOW() - INTERVAL 5 MINUTE");
$admin = $stmt->fetch_assoc();

echo json_encode(['status' => ($admin['online_count'] > 0) ? 'online' : 'offline']);
$conn->close();

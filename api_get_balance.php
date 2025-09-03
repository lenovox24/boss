<?php
// File: api_get_balance.php
// API untuk mengambil saldo terbaru pengguna

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';

// Atur header sebagai JSON
header('Content-Type: application/json');

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak ditemukan.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil saldo terbaru dari database
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $balance = $user['balance'];

    // Kirim respons dalam format JSON
    echo json_encode([
        'status' => 'success',
        'balance' => $balance,
        'formatted_balance' => 'IDR ' . number_format($balance, 0, ',', '.')
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan.']);
}

$stmt->close();
$conn->close();

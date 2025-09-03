<?php
// File: Hokiraja/get_referral_data.php
session_start();
require_once 'includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$view = $_GET['view'] ?? '';

if ($view === 'anggota') {
    $stmt = $conn->prepare(
        "SELECT username, registration_date 
         FROM users 
         WHERE referred_by = (SELECT referral_code FROM users WHERE id = ?)"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
} elseif ($view === 'bonus') {
    $stmt = $conn->prepare(
        "SELECT rb.amount, rb.created_at, u.username as from_username
         FROM referral_bonuses rb
         JOIN users u ON rb.from_user_id = u.id
         WHERE rb.user_id = ?
         ORDER BY rb.created_at DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
} else {
    echo json_encode([]);
}

$conn->close();

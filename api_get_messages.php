<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';
header('Content-Type: application/json');

$session_id = $_GET['session_id'] ?? session_id();

$stmt = $conn->prepare("SELECT sender_type, message, timestamp FROM live_chat_messages WHERE session_id = ? ORDER BY timestamp ASC");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($messages);
$stmt->close();
$conn->close();

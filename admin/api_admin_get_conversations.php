<?php
require_once '../includes/db_connect.php';
header('Content-Type: application/json');

$sql = "
    SELECT 
        lc.session_id, 
        u.username,
        (SELECT message FROM live_chat_messages WHERE session_id = lc.session_id ORDER BY timestamp DESC LIMIT 1) as last_message,
        (SELECT timestamp FROM live_chat_messages WHERE session_id = lc.session_id ORDER BY timestamp DESC LIMIT 1) as last_message_time
    FROM live_chat_messages lc
    LEFT JOIN users u ON lc.user_id = u.id
    GROUP BY lc.session_id
    ORDER BY last_message_time DESC
";

$result = $conn->query($sql);
$conversations = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($conversations);
$conn->close();

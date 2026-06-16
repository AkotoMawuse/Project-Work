<?php
// Ensure this works both when included in chat.php and when called directly via AJAX
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($conn)) {
    require 'db.php';
}

if (!isset($_SESSION['student_id'])) {
    exit('Unauthorized');
}

$current_user_id = $_SESSION['student_id'];
$receiver_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($receiver_id <= 0) {
    exit('');
}

// Fetch messages chronological
$stmt = $conn->prepare("SELECT sender_id, message, created_at FROM messages 
                       WHERE (sender_id = ? AND receiver_id = ?) 
                       OR (sender_id = ? AND receiver_id = ?) 
                       ORDER BY created_at ASC");
$stmt->bind_param("iiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id);
$stmt->execute();
$history = $stmt->get_result();

if ($history->num_rows > 0) {
    while($msg = $history->fetch_assoc()) {
        $is_sent_by_me = ($msg['sender_id'] == $current_user_id);
        $sender_label = $is_sent_by_me ? 'Me' : 'Them';
        $class = $is_sent_by_me ? 'sent' : '';
        echo '<div class="message ' . $class . '" title="' . htmlspecialchars($msg['created_at']) . '">';
        echo '<div style="font-size: 0.75rem; color: #777; margin-bottom: 0.2rem; font-weight: bold;">' . $sender_label . '</div>';
        echo htmlspecialchars($msg['message']);
        echo '</div>';
    }
} else {
    echo '<p style="text-align: center; color: #888;">No messages yet. Say hi!</p>';
}
?>

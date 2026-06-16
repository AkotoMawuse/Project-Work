<?php
session_start();
require 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['student_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message']) && isset($_POST['receiver_id'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $message = trim($_POST['message']);
    
    if (!empty($message) && $receiver_id > 0 && $receiver_id != $current_user_id) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $current_user_id, $receiver_id, $message);
        $stmt->execute();
    }
    
    // Redirect back to the chat page preserving the active user view
    header("Location: chat.php?user_id=" . $receiver_id);
    exit();
} else {
    header("Location: chat.php");
    exit();
}
?>

<?php
session_start();
require 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['student_id'];
$receiver_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch all other users for the sidebar
$sidebar_stmt = $conn->prepare("SELECT id, name, department FROM students WHERE id != ? ORDER BY name ASC");
$sidebar_stmt->bind_param("i", $current_user_id);
$sidebar_stmt->execute();
$all_students = $sidebar_stmt->get_result();

$receiver = null;
if ($receiver_id > 0 && $receiver_id != $current_user_id) {
    // Fetch receiver details
    $stmt = $conn->prepare("SELECT name FROM students WHERE id = ?");
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $receiver = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Student Relationship System</title>
    <link rel="stylesheet" href="assets/style.css">
    <script>
        // Auto scroll to bottom
        function scrollToBottom() {
            var chatDiv = document.getElementById("chat-box");
            if (chatDiv) {
                chatDiv.scrollTop = chatDiv.scrollHeight;
            }
        }
        
        window.onload = scrollToBottom;

        <?php if ($receiver): ?>
        // Auto refresh messages using AJAX
        setInterval(function() {
            fetch('load_messages.php?user_id=<?php echo $receiver_id; ?>')
                .then(response => response.text())
                .then(html => {
                    var chatBox = document.getElementById('chat-box');
                    var isScrolledToBottom = chatBox.scrollHeight - chatBox.clientHeight <= chatBox.scrollTop + 1;
                    chatBox.innerHTML = html;
                    if (isScrolledToBottom) {
                        scrollToBottom();
                    }
                });
        }, 3000);
        <?php endif; ?>
    </script>
</head>
<body>
    <header>
        <div class="logo"><a href="index.html" style="margin:0; text-decoration:none; color: white;">Student Relationship System</a></div>
        <nav>
            <a href="dashboard.php" style="margin-right: 1rem;">Dashboard</a>
            <a href="chat.php" style="margin-right: 1rem;">Global Chat</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container" style="max-width: 1000px; display: flex; gap: 1rem; flex-direction: row; align-items: stretch; height: 75vh;">
        
        <!-- Left Sidebar -->
        <div class="chat-sidebar" style="flex: 1; border: 1px solid #ccc; border-radius: 6px; background: #fafafa; overflow-y: auto;">
            <h3 style="padding: 1rem; border-bottom: 1px solid #ccc; background: white; margin: 0; position: sticky; top: 0;">Students</h3>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php if ($all_students->num_rows > 0): ?>
                    <?php while($student = $all_students->fetch_assoc()): ?>
                        <li>
                            <a href="chat.php?user_id=<?php echo $student['id']; ?>" class="chat-user-link <?php echo ($student['id'] == $receiver_id) ? 'active' : ''; ?>" style="display: block; padding: 1rem; border-bottom: 1px solid #eee; text-decoration: none; color: #333;">
                                <strong style="color: #005f8a;"><?php echo htmlspecialchars($student['name']); ?></strong><br>
                                <small style="color: #777;"><?php echo htmlspecialchars($student['department']); ?></small>
                            </a>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li style="padding: 1rem; color: #777;">No other students found.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main" style="flex: 2; border: 1px solid #ccc; border-radius: 6px; background: white; display: flex; flex-direction: column;">
            <?php if ($receiver): ?>
                <h3 style="padding: 1rem; border-bottom: 1px solid #ccc; background: #f9f9f9; margin: 0;">Chat with <?php echo htmlspecialchars($receiver['name']); ?></h3>
                
                <div class="messages" id="chat-box" style="flex-grow: 1; height: auto; border: none; border-bottom: 1px solid #ccc; margin: 0; border-radius: 0;">
                    <!-- Messages loaded here -->
                    <?php include 'load_messages.php'; // Initial load ?>
                </div>

                <form method="POST" action="send_message.php" style="display: flex; gap: 0.5rem; padding: 1rem; background: #f9f9f9;">
                    <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
                    <input type="text" name="message" required placeholder="Type a message..." style="flex-grow: 1; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" autocomplete="off">
                    <button type="submit" class="btn">Send</button>
                </form>
            <?php else: ?>
                <div style="flex-grow: 1; display: flex; align-items: center; justify-content: center; color: #888;">
                    <p>Select a student from the sidebar to start chatting.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

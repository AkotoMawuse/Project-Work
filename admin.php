<?php
session_start();
require 'db.php';

// Handle Admin Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['admin_logged_in']);
    header("Location: admin.php");
    exit();
}

// Handle Admin Login
$login_error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: admin.php");
            exit();
        } else {
            $login_error = "Invalid credentials";
        }
    } else {
        $login_error = "Invalid credentials";
    }
}

// Handle Admin Deletions
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    if (isset($_GET['delete_user'])) {
        $del_id = intval($_GET['delete_user']);
        $conn->query("DELETE FROM students WHERE id = $del_id");
        $conn->query("DELETE FROM messages WHERE sender_id = $del_id OR receiver_id = $del_id");
        header("Location: admin.php");
        exit();
    }
    if (isset($_GET['delete_message'])) {
        $del_msg_id = intval($_GET['delete_message']);
        $conn->query("DELETE FROM messages WHERE id = $del_msg_id");
        header("Location: admin.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Student Relationship System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <div class="logo"><a href="index.html" style="margin:0; text-decoration:none; color: white;">Student Relationship System</a></div>
        <nav>
            <?php if (isset($_SESSION['admin_logged_in'])): ?>
                <a href="admin.php?action=logout">Admin Logout</a>
            <?php else: ?>
                <a href="index.html">Home</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <?php if (!isset($_SESSION['admin_logged_in'])): ?>
            <!-- Admin Login Form -->
            <div style="max-width: 400px; margin: 0 auto;">
                <h2>Admin Login</h2>
                <?php if ($login_error): ?>
                    <div style="color: red; margin-bottom: 1rem;"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required value="admin">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required value="vasco123">
                    </div>
                    <button type="submit" name="login" class="btn">Login as Admin</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Admin Dashboard -->
            <h2>Admin Dashboard</h2>
            
            <h3 style="margin-top: 2rem;">All Students</h3>
            <?php
            $students_res = $conn->query("SELECT * FROM students ORDER BY id DESC");
            if ($students_res->num_rows > 0):
            ?>
            <div style="overflow-x: auto;">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Interests</th>
                        <th>Action</th>
                    </tr>
                    <?php while($s = $students_res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $s['id']; ?></td>
                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                        <td><?php echo htmlspecialchars($s['email']); ?></td>
                        <td><?php echo htmlspecialchars($s['department']); ?></td>
                        <td><?php echo htmlspecialchars($s['interests']); ?></td>
                        <td>
                            <a href="admin.php?delete_user=<?php echo $s['id']; ?>" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.3rem 0.6rem;" onclick="return confirm('Are you sure you want to delete this user and all their messages?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?>
                <p>No students found.</p>
            <?php endif; ?>

            <h3 style="margin-top: 2rem;">Recent Messages</h3>
            <?php
            $msg_sql = "SELECT m.id, m.message, m.created_at, 
                               s1.name as sender, s2.name as receiver 
                        FROM messages m 
                        LEFT JOIN students s1 ON m.sender_id = s1.id 
                        LEFT JOIN students s2 ON m.receiver_id = s2.id 
                        ORDER BY m.created_at DESC LIMIT 50";
            $msg_res = $conn->query($msg_sql);
            if ($msg_res && $msg_res->num_rows > 0):
            ?>
            <div style="overflow-x: auto;">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Time</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Message</th>
                        <th>Action</th>
                    </tr>
                    <?php while($m = $msg_res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $m['id']; ?></td>
                        <td><?php echo $m['created_at']; ?></td>
                        <td><?php echo $m['sender'] ? htmlspecialchars($m['sender']) : 'Deleted User'; ?></td>
                        <td><?php echo $m['receiver'] ? htmlspecialchars($m['receiver']) : 'Deleted User'; ?></td>
                        <td><?php echo htmlspecialchars(substr($m['message'], 0, 50)); ?><?php echo strlen($m['message']) > 50 ? '...' : ''; ?></td>
                        <td>
                            <a href="admin.php?delete_message=<?php echo $m['id']; ?>" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.3rem 0.6rem;" onclick="return confirm('Delete this message?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?>
                <p>No messages found.</p>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>
</html>

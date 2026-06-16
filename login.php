<?php
session_start();
require 'db.php';

if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['student_id'] = $row['id'];
                $_SESSION['student_name'] = $row['name'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No user found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Relationship System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <div class="logo"><a href="index.html" style="margin:0; text-decoration:none; color: white;">Student Relationship System</a></div>
        <nav>
            <a href="register.php">Register</a>
        </nav>
    </header>

    <div class="container" style="max-width: 400px;">
        <h2>Student Login</h2>
        <?php if ($error): ?>
            <div style="color: red; margin-bottom: 1rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
            <p style="margin-top: 1rem;">Don't have an account? <a href="register.php" style="color: #007bb5;">Register here</a>.</p>
        </form>
    </div>
</body>
</html>

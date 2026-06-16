<?php
session_start();
require 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $department = trim($_POST['department']);
    $interests = trim($_POST['interests']);

    if (empty($name) || empty($email) || empty($password) || empty($department)) {
        $error = "All fields except interests are required.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO students (name, email, password, department, interests) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hashed_password, $department, $interests);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Relationship System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <div class="logo"><a href="index.html" style="margin:0; text-decoration:none; color: white;">Student Relationship System</a></div>
        <nav>
            <a href="login.php">Login</a>
        </nav>
    </header>

    <div class="container" style="max-width: 500px;">
        <h2>Register</h2>
        <?php if ($error): ?>
            <div style="color: red; margin-bottom: 1rem;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div style="color: green; margin-bottom: 1rem;"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Department</label>
                <select name="department" required>
                    <option value="">Select Department</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Engineering">Engineering</option>
                    <option value="Business">Business</option>
                    <option value="Medicine">Medicine</option>
                    <option value="Arts">Arts</option>
                </select>
            </div>
            <div class="form-group">
                <label>Interests (comma separated)</label>
                <input type="text" name="interests" placeholder="e.g., coding, music, sports">
            </div>
            <button type="submit" class="btn">Register</button>
            <p style="margin-top: 1rem;">Already have an account? <a href="login.php" style="color: #007bb5;">Login here</a>.</p>
        </form>
    </div>
</body>
</html>

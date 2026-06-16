<?php
session_start();
require 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student details
$stmt = $conn->prepare("SELECT name, department, interests FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    header("Location: logout.php");
    exit();
}

// Fetch matches (same department or shared interests)
// We will split interests and build a LIKE query for a simple match
$interests_arr = array_map('trim', explode(',', $student['interests']));
$interest_query_parts = [];
foreach ($interests_arr as $interest) {
    if (!empty($interest)) {
        $interest_query_parts[] = "interests LIKE '%" . $conn->real_escape_string($interest) . "%'";
    }
}
$interest_sql = count($interest_query_parts) > 0 ? implode(' OR ', $interest_query_parts) : "1=0";

// Find matches excluding self
$sql = "SELECT id, name, department, interests FROM students 
        WHERE id != $student_id 
        AND (department = '" . $conn->real_escape_string($student['department']) . "' 
        OR $interest_sql) LIMIT 20";

$matches_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Relationship System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <div class="logo"><a href="index.html" style="margin:0; text-decoration:none; color: white;">Student Relationship System</a></div>
        <nav>
            <span style="margin-right: 1rem; font-weight: bold;">Hello, <?php echo htmlspecialchars($student['name']); ?></span>
            <a href="chat.php" style="margin-right: 1rem;">Global Chat</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Your Profile</h2>
        <div style="background: #f9f9f9; padding: 1rem; border-radius: 6px; margin-bottom: 2rem;">
            <p><strong>Department:</strong> <?php echo htmlspecialchars($student['department']); ?></p>
            <p><strong>Interests:</strong> <?php echo htmlspecialchars($student['interests']); ?></p>
        </div>

        <h2>Suggested Matches</h2>
        <p style="margin-bottom: 1rem;">Students from your department or with similar interests.</p>
        
        <?php if ($matches_result && $matches_result->num_rows > 0): ?>
            <?php while($match = $matches_result->fetch_assoc()): ?>
                <div class="match-card">
                    <div>
                        <h3 style="margin-bottom: 0.2rem;"><?php echo htmlspecialchars($match['name']); ?></h3>
                        <p style="color: #666; font-size: 0.9rem;">
                            Department: <?php echo htmlspecialchars($match['department']); ?> | 
                            Interests: <?php echo htmlspecialchars($match['interests']); ?>
                        </p>
                    </div>
                    <div>
                        <a href="chat.php?user_id=<?php echo $match['id']; ?>" class="btn">Message</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #666;">No matches found yet. Check back later!</p>
        <?php endif; ?>
    </div>
</body>
</html>

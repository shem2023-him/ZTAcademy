<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "zta_app";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['answers'])) {
    die("Invalid submission.");
}

$user_id = $_SESSION['user_id'];
$module_id = intval($_POST['module_id']);
$answers = $_POST['answers'];

$score = 0;
$total = count($answers);

foreach ($answers as $quiz_id => $selected) {
    $stmt = $conn->prepare("SELECT correct_option FROM quizzes WHERE quiz_id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $correct = $result->fetch_assoc()['correct_option'];

    $is_correct = ($selected === $correct) ? 1 : 0;
    if ($is_correct) $score++;

    // Save result
    $insert = $conn->prepare("INSERT INTO quiz_results (user_id, quiz_id, selected_option, score) VALUES (?, ?, ?, ?)");
    $insert->bind_param("iisi", $user_id, $quiz_id, $selected, $is_correct);
    $insert->execute();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quiz Results | ZTA App</title>
</head>
<body>
    <h2>Quiz Results</h2>
    <p>You scored <?php echo $score; ?> out of <?php echo $total; ?>.</p>
    <a href="student_dashboard.php">⬅ Back to Dashboard</a>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Ensure module ID is provided
if (!isset($_GET['module_id'])) {
    die("Module ID is missing.");
}

$module_id = intval($_GET['module_id']);

// Fetch module
$module_sql = "SELECT title FROM modules WHERE module_id = $module_id";
$module_result = $conn->query($module_sql);
if ($module_result->num_rows !== 1) {
    die("Module not found.");
}
$module = $module_result->fetch_assoc();

// Fetch quizzes
$quiz_sql = "SELECT * FROM quizzes WHERE module_id = $module_id";
$quiz_result = $conn->query($quiz_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz - <?php echo htmlspecialchars($module['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>📝 Quiz: <?php echo htmlspecialchars($module['title']); ?></h2>
    <form action="quiz_results.php" method="post">
        <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
        <?php 
        $qnum = 1;
        while ($quiz = $quiz_result->fetch_assoc()): ?>
            <div class="mb-4">
                <p><b><?php echo $qnum++ . ". " . htmlspecialchars($quiz['question']); ?></b></p>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="quiz_<?php echo $quiz['quiz_id']; ?>" value="A" required>
                    <label class="form-check-label"><?php echo htmlspecialchars($quiz['option_a']); ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="quiz_<?php echo $quiz['quiz_id']; ?>" value="B">
                    <label class="form-check-label"><?php echo htmlspecialchars($quiz['option_b']); ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="quiz_<?php echo $quiz['quiz_id']; ?>" value="C">
                    <label class="form-check-label"><?php echo htmlspecialchars($quiz['option_c']); ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="quiz_<?php echo $quiz['quiz_id']; ?>" value="D">
                    <label class="form-check-label"><?php echo htmlspecialchars($quiz['option_d']); ?></label>
                </div>
            </div>
        <?php endwhile; ?>
        <button type="submit" class="btn btn-primary">Submit Quiz</button>
    </form>
    <br>
    <a href="student_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
</div>
</body>
</html>

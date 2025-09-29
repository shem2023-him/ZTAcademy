<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Get module_id from GET
$module_id = $_GET['module_id'] ?? null;
if (!$module_id) {
    die("Module not specified");
}

// Fetch module details
$sql = "SELECT * FROM modules WHERE module_id = $module_id";
$module_result = $conn->query($sql);
if ($module_result->num_rows === 0) {
    die("Module not found");
}
$module = $module_result->fetch_assoc();

// Fetch questions for this module
$sql = "SELECT * FROM quizzes WHERE module_id = $module_id";
$questions_result = $conn->query($sql);
$questions = [];
while ($row = $questions_result->fetch_assoc()) {
    $questions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Quiz - <?php echo htmlspecialchars($module['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2><?php echo htmlspecialchars($module['title']); ?> - Quiz</h2>
    <form action="submit_quiz.php" method="post">
        <!-- Hidden input to pass module_id -->
        <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="mb-3">
                <label><b>Q<?php echo $index + 1; ?>:</b> <?php echo htmlspecialchars($q['question_text']); ?></label>
                <input type="text" class="form-control" name="answers[<?php echo $q['quiz_id']; ?>]" required>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary">Submit Quiz</button>
        <a href="student_dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>

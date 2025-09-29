<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$module_id = $_GET['module_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$module_id) die("Module not specified");

// Fetch module title
$stmt = $conn->prepare("SELECT title FROM modules WHERE module_id = ?");
$stmt->bind_param("i", $module_id);
$stmt->execute();
$module = $stmt->get_result()->fetch_assoc();

// Fetch module questions
$sql = "SELECT * FROM quizzes WHERE module_id = ? ORDER BY quiz_id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $module_id);
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);

if (!$questions) die("No quizzes found for this module.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($module['title']); ?> - Take Quiz</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
<h2><?php echo htmlspecialchars($module['title']); ?> - Quiz</h2>
<form action="submit_quiz.php" method="post">
    <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
    
    <?php foreach ($questions as $i => $q): ?>
        <div class="mb-4 p-3 bg-white border rounded shadow-sm">
            <h5>Q<?php echo $i+1; ?>: <?php echo htmlspecialchars($q['question']); ?></h5>
            
            <?php foreach (['A','B','C','D'] as $opt): ?>
                <?php if (!empty($q['option_'.strtolower($opt)])): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" 
                               name="answers[<?php echo $q['quiz_id']; ?>]" 
                               value="<?php echo $opt; ?>" 
                               id="q<?php echo $q['quiz_id']; ?>_<?php echo $opt; ?>" required>
                        <label class="form-check-label" for="q<?php echo $q['quiz_id']; ?>_<?php echo $opt; ?>">
                            <?php echo $opt; ?>. <?php echo htmlspecialchars($q['option_'.strtolower($opt)]); ?>
                        </label>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    
    <button type="submit" class="btn btn-primary">Submit Quiz</button>
</form>
</div>
</body>
</html>

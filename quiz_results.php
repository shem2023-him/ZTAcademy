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

// Fetch questions and user answers
$sql = "
SELECT q.quiz_id, q.question, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option,
       qr.selected_option
FROM quizzes q
LEFT JOIN (SELECT quiz_id, selected_option FROM quiz_results WHERE user_id = ?) qr
ON q.quiz_id = qr.quiz_id
WHERE q.module_id = ?
ORDER BY q.quiz_id ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $module_id);
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);

// Compute statistics
$total_questions = count($questions);
$correct_answers = 0;
$option_map = ['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'];

foreach ($questions as $q) {
    if ($q['selected_option'] === $q['correct_option']) {
        $correct_answers++;
    }
}

$percentage = ($total_questions > 0) ? round(($correct_answers / $total_questions) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($module['title']); ?> - Quiz Results</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.correct { background-color: #d4edda; }
.incorrect { background-color: #f8d7da; }
.notanswered { color: #6c757d; }
</style>
</head>
<body class="bg-light">
<div class="container mt-5">

<h2><?php echo htmlspecialchars($module['title']); ?> - Quiz Results</h2>
<a href="student_dashboard.php" class="btn btn-secondary mb-3">⬅ Back to Dashboard</a>

<!-- Quiz Statistics -->
<div class="mb-3">
    <h5>Quiz Summary:</h5>
    <p>Total Questions: <b><?php echo $total_questions; ?></b></p>
    <p>Correct Answers: <b><?php echo $correct_answers; ?></b></p>
    <p>Score Percentage: <b><?php echo $percentage; ?>%</b></p>
</div>

<!-- Results Table -->
<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Question</th>
            <th>Your Answer</th>
            <th>Correct Answer</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($questions as $i => $q): 
            $user_ans = $q['selected_option'];
            $correct_ans = $q['correct_option'];

            if (!$user_ans) {
                $status = 'Not Answered';
                $row_class = 'notanswered';
                $display_user = '<span class="text-muted">Not Answered</span>';
            } elseif ($user_ans === $correct_ans) {
                $status = 'Correct';
                $row_class = 'correct';
                $display_user = $user_ans . '. ' . htmlspecialchars($q[$option_map[$user_ans]]);
            } else {
                $status = 'Incorrect';
                $row_class = 'incorrect';
                $display_user = $user_ans . '. ' . htmlspecialchars($q[$option_map[$user_ans]]);
            }

            $display_correct = $correct_ans . '. ' . htmlspecialchars($q[$option_map[$correct_ans]]);
        ?>
        <tr class="<?php echo $row_class; ?>">
            <td><?php echo $i+1; ?></td>
            <td><?php echo htmlspecialchars($q['question']); ?></td>
            <td><?php echo $display_user; ?></td>
            <td><?php echo $display_correct; ?></td>
            <td><?php echo $status; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
</body>
</html>

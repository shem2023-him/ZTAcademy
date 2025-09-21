<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';
$user_id = $_SESSION['user_id'];

// Fetch all progress details
$sql = "
    SELECT m.module_id, m.title,
           COALESCE(MAX(qr.attempt_time), NULL) AS last_attempt,
           CASE 
               WHEN COUNT(q.quiz_id) = 0 THEN 'No Quiz'
               WHEN SUM(qr.score) IS NULL THEN 'Not Started'
               WHEN SUM(qr.score) = COUNT(q.quiz_id) THEN 'Completed'
               ELSE 'In Progress'
           END AS progress_status,
           (SELECT SUM(score) 
            FROM quiz_results 
            WHERE user_id = $user_id AND quiz_id IN (SELECT quiz_id FROM quizzes WHERE module_id = m.module_id)) AS latest_score,
           (SELECT COUNT(*) 
            FROM quizzes 
            WHERE module_id = m.module_id) AS total_questions
    FROM modules m
    LEFT JOIN quizzes q ON m.module_id = q.module_id
    LEFT JOIN quiz_results qr ON q.quiz_id = qr.quiz_id AND qr.user_id = $user_id
    GROUP BY m.module_id, m.title
    ORDER BY m.module_id ASC
";

$result = $conn->query($sql);

// For summary card
$totalModules = $result->num_rows;
$completedModules = 0;
$totalScore = 0;
$totalQuestions = 0;
$progressData = [];

while ($row = $result->fetch_assoc()) {
    $score = $row['latest_score'] ?? 0;
    $questions = $row['total_questions'] ?? 0;

    if ($row['progress_status'] === 'Completed') {
        $completedModules++;
    }
    $totalScore += $score;
    $totalQuestions += $questions;

    $progressData[] = $row;
}

$overallPercentage = ($totalQuestions > 0) ? round(($totalScore / $totalQuestions) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Progress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">

    <!-- Summary Card -->
    <div class="card text-center mb-4 shadow-sm">
        <div class="card-body">
            <h4 class="card-title">📊 Progress Overview</h4>
            <p class="card-text">
                Modules Completed: <b><?php echo $completedModules; ?>/<?php echo $totalModules; ?></b><br>
                Overall Progress: <b><?php echo $overallPercentage; ?>%</b>
            </p>
            <div class="progress" style="height: 25px;">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: <?php echo $overallPercentage; ?>%;" 
                     aria-valuenow="<?php echo $overallPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                     <?php echo $overallPercentage; ?>%
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Table -->
    <h2 class="mb-3">Module Breakdown</h2>
    <a href="student_dashboard.php" class="btn btn-secondary mb-3">⬅ Back to Dashboard</a>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Module</th>
                <th>Status</th>
                <th>Latest Score</th>
                <th>Completion</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($progressData as $row): 
                $score = $row['latest_score'] ?? 0;
                $total = $row['total_questions'] ?? 0;
                $percentage = ($total > 0) ? round(($score / $total) * 100, 1) : 0;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo $row['progress_status']; ?></td>
                <td><?php echo ($total > 0) ? "$score / $total" : "No Quiz"; ?></td>
                <td>
                    <?php if ($total > 0): ?>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar 
                                <?php echo ($percentage == 100) ? 'bg-success' : (($percentage >= 50) ? 'bg-info' : 'bg-warning'); ?>" 
                                role="progressbar" 
                                style="width: <?php echo $percentage; ?>%;" 
                                aria-valuenow="<?php echo $percentage; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                <?php echo $percentage; ?>%
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

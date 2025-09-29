<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';
$user_id = $_SESSION['user_id'];

$sql = "
SELECT m.module_id, m.title,
       COUNT(q.quiz_id) AS total_questions,
       SUM(IF(qr.score IS NULL,0,qr.score)) AS total_score
FROM modules m
LEFT JOIN quizzes q ON m.module_id = q.module_id
LEFT JOIN quiz_results qr ON q.quiz_id = qr.quiz_id AND qr.user_id = ?
GROUP BY m.module_id
ORDER BY m.module_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$progressData = [];
while ($row = $result->fetch_assoc()) {
    $percentage = ($row['total_questions'] > 0) ? round(($row['total_score'] / $row['total_questions']) * 100,1) : 0;
    $status = ($percentage==100) ? 'Completed' : (($percentage>0) ? 'In Progress' : 'Not Started');

    $progressData[] = [
        'title' => $row['title'],
        'percentage' => $percentage,
        'status' => $status
    ];
}
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
<h2>My Progress</h2>
<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>Module</th>
<th>Status</th>
<th>Completion</th>
</tr>
</thead>
<tbody>
<?php foreach($progressData as $p): ?>
<tr>
<td><?php echo htmlspecialchars($p['title']); ?></td>
<td><?php echo $p['status']; ?></td>
<td>
<div class="progress" style="height: 25px;">
<div class="progress-bar <?php echo ($p['percentage']==100)?'bg-success':'bg-info'; ?>" 
role="progressbar" style="width: <?php echo $p['percentage']; ?>%;" aria-valuenow="<?php echo $p['percentage']; ?>" aria-valuemin="0" aria-valuemax="100">
<?php echo $p['percentage']; ?>%
</div>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<a href="student_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
</div>
</body>
</html>

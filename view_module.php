<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Ensure module ID is provided
if (!isset($_GET['id'])) {
    die("Module ID is missing.");
}

$module_id = intval($_GET['id']);
$sql = "SELECT * FROM modules WHERE module_id = $module_id";
$result = $conn->query($sql);

if ($result->num_rows !== 1) {
    die("Module not found.");
}

$module = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($module['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4"><?php echo htmlspecialchars($module['title']); ?></h2>
    <p class="text-muted"><?php echo htmlspecialchars($module['description']); ?></p>
    <div class="card p-4 mb-3">
        <?php echo $module['content']; ?>
    </div>

    <?php if ($_SESSION['role'] === 'student'): ?>
        <a href="quiz.php?module_id=<?php echo $module['module_id']; ?>" class="btn btn-success">📝 Take Quiz</a>
        <a href="student_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
    <?php else: ?>
        <a href="manage_modules.php" class="btn btn-secondary">⬅ Back to Manage Modules</a>
        <a href="edit_module.php?id=<?php echo $module['module_id']; ?>" class="btn btn-warning">✏ Edit Module</a>
    <?php endif; ?>
</div>
</body>
</html>
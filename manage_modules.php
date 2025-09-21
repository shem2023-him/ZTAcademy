<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Safe include for database connection
if (file_exists("db_connect.php")) {
    include 'db_connect.php';
} else {
    die("<div style='padding:20px; font-family:sans-serif; color:#721c24; background:#f8d7da; border:1px solid #f5c6cb;'>
            <strong>Database Error:</strong> Could not find <code>db_connect.php</code>. 
            Please make sure the file exists in your project folder.
         </div>");
}

// Check if $conn exists
if (!isset($conn) || $conn->connect_error) {
    die("<div style='padding:20px; font-family:sans-serif; color:#721c24; background:#f8d7da; border:1px solid #f5c6cb;'>
            <strong>Database Error:</strong> Connection failed. Please verify your settings in <code>db_connect.php</code>.
         </div>");
}

// Fetch all modules
$sql = "SELECT module_id, title, description, created_at FROM modules ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Modules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Manage Modules</h2>
    <a href="admin_dashboard.php" class="btn btn-secondary mb-3">⬅ Back to Dashboard</a>
    <a href="add_module.php" class="btn btn-primary mb-3">➕ Add New Module</a>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['module_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . "..."; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <a href="view_module.php?id=<?php echo $row['module_id']; ?>" class="btn btn-sm btn-info">👁 View</a>
                        <a href="edit_module.php?id=<?php echo $row['module_id']; ?>" class="btn btn-sm btn-warning">✏ Edit</a>
                        <a href="delete_module.php?id=<?php echo $row['module_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this module?');">🗑 Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No modules found. Please add one.</div>
    <?php endif; ?>
</div>
</body>
</html>

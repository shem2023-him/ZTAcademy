<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Check if module ID is passed
if (!isset($_GET['id'])) {
    die("Module ID is missing.");
}

$module_id = intval($_GET['id']);
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $content = $conn->real_escape_string($_POST['content']);

    $update_sql = "UPDATE modules SET title='$title', description='$description', content='$content' WHERE module_id=$module_id";

    if ($conn->query($update_sql)) {
        $message = "<div class='alert alert-success'>Module updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating module: " . $conn->error . "</div>";
    }
}

// Fetch existing module data
$sql = "SELECT * FROM modules WHERE module_id=$module_id";
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
    <title>Edit Module</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Edit Module</h2>
    <a href="manage_modules.php" class="btn btn-secondary mb-3">⬅ Back to Manage Modules</a>
    <?php echo $message; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($module['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($module['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="10" required><?php echo htmlspecialchars($module['content']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
    </form>
</div>
</body>
</html>

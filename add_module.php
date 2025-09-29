<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'db_connect.php';

// Handle form submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $content = trim($_POST['content']);

    if ($title !== "" && $description !== "" && $content !== "") {
        $stmt = $conn->prepare("INSERT INTO modules (title, description, content) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $content);

        if ($stmt->execute()) {
            header("Location: manage_modules.php?success=1");
            exit();
        } else {
            $message = "❌ Error adding module: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "⚠️ All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Module - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: "Segoe UI", Roboto, sans-serif; margin:0; background:#f8f9fa; }
    .app-wrap { display:flex; min-height:100vh; }
    .sidebar {
        width:240px; background:linear-gradient(180deg,#6f42c1,#0d6efd);
        color:#fff; padding:20px;
    }
    .sidebar h4 { font-weight:700; }
    .sidebar .nav-link { color:#fff; margin-bottom:6px; border-radius:6px; }
    .sidebar .nav-link:hover { background:rgba(255,255,255,0.2); }
    .content-area { flex:1; padding:24px; }
    .card { border:none; border-radius:12px; }
  </style>
</head>
<body>
<div class="app-wrap">
  <!-- Sidebar -->
  <div class="sidebar">
    <h4 class="text-center mb-4">🛠 Admin</h4>
    <a class="nav-link" href="admin_dashboard.php">🏠 Dashboard</a>
    <a class="nav-link" href="manage_users.php">👥 Manage Users</a>
    <a class="nav-link" href="manage_modules.php">📚 Manage Modules</a>
    <a class="nav-link" href="manage_quizzes.php">📝 Manage Quizzes</a>
    <a class="nav-link" href="logout.php">🚪 Logout</a>
  </div>

  <!-- Content -->
  <div class="content-area">
    <h2 class="mb-4">➕ Add New Module</h2>

    <?php if ($message): ?>
      <div class="alert alert-warning"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm p-4">
      <form method="POST" action="">
        <div class="mb-3">
          <label class="form-label">Module Title</label>
          <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Short Description</label>
          <textarea name="description" class="form-control" rows="2" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Content (Notes & Illustrations)</label>
          <textarea name="content" class="form-control" rows="8" required></textarea>
        </div>
        <div class="d-flex justify-content-between">
          <a href="manage_modules.php" class="btn btn-outline-secondary">⬅ Back</a>
          <button type="submit" class="btn btn-primary">Save Module</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'db_connect.php';

// Get counts
$totalUsers = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'] ?? 0;
$totalModules = $conn->query("SELECT COUNT(*) AS c FROM modules")->fetch_assoc()['c'] ?? 0;
$totalQuizzes = $conn->query("SELECT COUNT(*) AS c FROM quizzes")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - ZTAcademy</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: "Segoe UI", Roboto, sans-serif; margin:0; }
    .app-wrap { display:flex; min-height:100vh; }
    .sidebar {
        width:240px; background:linear-gradient(180deg,#6f42c1,#0d6efd);
        color:#fff; padding:20px;
    }
    .sidebar h4 { font-weight:700; }
    .sidebar .nav-link { color:#fff; margin-bottom:6px; border-radius:6px; }
    .sidebar .nav-link:hover { background:rgba(255,255,255,0.2); }
    .content-area { flex:1; padding:24px; background:#f8f9fa; }
    .card { border:none; border-radius:12px; transition:.2s; }
    .card:hover { transform:translateY(-3px); box-shadow:0 6px 16px rgba(0,0,0,.15); }
    .stat-card { color:#fff; }
    footer { margin-top:40px; color:#6c757d; }
  </style>
</head>
<body>
<div class="app-wrap">
  <!-- Sidebar -->
  <div class="sidebar">
    <h4 class="text-center mb-4">🛠 Admin</h4>
    <a class="nav-link" href="manage_users.php">👥 Manage Users</a>
    <a class="nav-link" href="manage_modules.php">📚 Manage Modules</a>
    <a class="nav-link" href="manage_quizzes.php">📝 Manage Quizzes</a>
    <a class="nav-link" href="logout.php">🚪 Logout</a>
  </div>

  <!-- Content -->
  <div class="content-area">
    <h2 class="mb-4">Welcome, <span class="text-primary">Administrator</span> 👋</h2>
    <p class="text-muted">Use the controls below to manage the ZTAcademy platform.</p>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card bg-primary stat-card shadow">
          <div class="card-body">
            <h6 class="card-subtitle">Total Users</h6>
            <h3><?php echo $totalUsers; ?></h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-success stat-card shadow">
          <div class="card-body">
            <h6 class="card-subtitle">Total Modules</h6>
            <h3><?php echo $totalModules; ?></h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-warning text-dark stat-card shadow">
          <div class="card-body">
            <h6 class="card-subtitle">Total Quizzes</h6>
            <h3><?php echo $totalQuizzes; ?></h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Admin Controls -->
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <div class="col">
        <div class="card shadow h-100 text-center">
          <div class="card-body">
            <h5 class="card-title">👥 Manage Users</h5>
            <p class="card-text text-muted">View, edit, and manage user accounts.</p>
            <a href="manage_users.php" class="btn btn-primary">Go</a>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card shadow h-100 text-center">
          <div class="card-body">
            <h5 class="card-title">📚 Manage Modules</h5>
            <p class="card-text text-muted">Create, update, and organize modules.</p>
            <a href="manage_modules.php" class="btn btn-success">Go</a>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card shadow h-100 text-center">
          <div class="card-body">
            <h5 class="card-title">📝 Manage Quizzes</h5>
            <p class="card-text text-muted">Add, edit, and review quizzes.</p>
            <a href="manage_quizzes.php" class="btn btn-warning text-dark">Go</a>
          </div>
        </div>
      </div>
    </div>

    <footer class="mt-5 text-center">
      <small>© ZTAcademy — Admin Dashboard</small>
    </footer>
  </div>
</div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | ZTA App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">ZTA Admin Dashboard</span>
    <span class="text-white">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?> 🔑</span>
    <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</nav>

<div class="container mt-4">
  <h3 class="mb-4">Admin Controls</h3>
  
<div class="row g-4">
  <!-- Manage Users -->
  <div class="col-md-4">
    <div class="card shadow-sm h-100 border-0" style="background: linear-gradient(135deg, #4e73df, #224abe); color: white;">
      <div class="card-body text-center d-flex flex-column justify-content-between">
        <div>
          <i class="bi bi-people-fill display-4 mb-3"></i>
          <h5 class="card-title">Manage Users</h5>
          <p class="card-text">View and manage student and admin accounts.</p>
        </div>
        <a href="manage_users.php" class="btn btn-light fw-bold mt-3">Go</a>
      </div>
    </div>
  </div>

  <!-- Manage Modules -->
  <div class="col-md-4">
    <div class="card shadow-sm h-100 border-0" style="background: linear-gradient(135deg, #1cc88a, #0d6e3e); color: white;">
      <div class="card-body text-center d-flex flex-column justify-content-between">
        <div>
          <i class="bi bi-book-fill display-4 mb-3"></i>
          <h5 class="card-title">Manage Modules</h5>
          <p class="card-text">Add, edit, or remove ZTA learning modules.</p>
        </div>
        <a href="manage_modules.php" class="btn btn-light fw-bold mt-3">Go</a>
      </div>
    </div>
  </div>

  <!-- Manage Quizzes -->
  <div class="col-md-4">
    <div class="card shadow-sm h-100 border-0" style="background: linear-gradient(135deg, #f6c23e, #d39e00); color: white;">
      <div class="card-body text-center d-flex flex-column justify-content-between">
        <div>
          <i class="bi bi-pencil-square display-4 mb-3"></i>
          <h5 class="card-title">Manage Quizzes</h5>
          <p class="card-text">Create and update quizzes for each module.</p>
        </div>
        <a href="manage_quizzes.php" class="btn btn-light fw-bold mt-3">Go</a>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

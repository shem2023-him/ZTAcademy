<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "zta_app";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch modules
$sql = "SELECT module_id, title, description FROM modules ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard | ZTA App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Segoe UI', sans-serif; }
    .sidebar {
      height: 100vh;
      background: #0d6efd;
      color: white;
      position: fixed;
      width: 250px;
      padding-top: 20px;
    }
    .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      padding: 12px 20px;
    }
    .sidebar a:hover {
      background: #0a58ca;
    }
    .content {
      margin-left: 260px;
      padding: 20px;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h4 class="text-center mb-4">📘 ZTA Student</h4>
    <a href="student_dashboard.php">🏠 Dashboard</a>
    <a href="my_progress.php">📊 My Progress</a>
    <a href="student_dashboard.php">📚 Modules</a>
    <a class="nav-link" href="glossary.php">📖 Glossary</a>
    <a href="logout.php">🚪 Logout</a>
  </div>

  <!-- Main Content -->
  <div class="content">
    <h3>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h3>
    <p class="text-muted">Select a module below to begin learning.</p>

    <div class="row">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-md-6">
            <div class="card mb-4 shadow-sm">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                <a href="view_module.php?id=<?php echo $row['module_id']; ?>" class="btn btn-primary">Start Module</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No modules available yet.</p>
      <?php endif; ?>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Modules | ZTAcademy Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            line-height: 1.6;
        }
        
        /* Sidebar Styles */
        .sidebar {
            height: 100vh;
            background: linear-gradient(180deg, #6f42c1 0%, #0d6efd 100%);
            color: white;
            position: fixed;
            width: 280px;
            padding-top: 30px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .sidebar-brand {
            text-align: center;
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
        }
        .sidebar-brand h4 {
            font-weight: 700;
            margin: 0;
            color: white;
        }
        .sidebar-nav {
            padding: 0 15px;
        }
        .sidebar-nav a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 8px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .sidebar-nav a i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1em;
        }
        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #ffc107;
        }
        
        /* Main Content */
        .content {
            margin-left: 280px;
            padding: 30px;
            min-height: 100vh;
        }
        
        /* Header */
        .dashboard-header {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #198754;
        }
        
        /* Action Buttons */
        .action-buttons {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .btn-back {
            background: #6c757d;
            border: none;
            color: white;
        }
        .btn-add {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            border: none;
            color: white;
        }
        
        /* Modules Table */
        .modules-container {
            background: white;
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .table-header {
            background: linear-gradient(135deg, #0d6efd 0%, #224abe 100%);
            color: white;
            padding: 20px 25px;
            border-bottom: none;
        }
        .table-header h5 {
            margin: 0;
            font-weight: 600;
        }
        .table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            padding: 15px 20px;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }
        .table tbody td {
            padding: 20px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }
        .table tbody tr {
            transition: all 0.3s ease;
        }
        .table tbody tr:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Module Info */
        .module-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .module-description {
            color: #6c757d;
            font-size: 0.9em;
            line-height: 1.4;
        }
        .module-id {
            color: #6c757d;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }
        .created-date {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        /* Action Buttons in Table */
        .btn-action {
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.8em;
            font-weight: 500;
            margin: 2px;
            transition: all 0.3s ease;
        }
        .btn-action:hover {
            transform: translateY(-1px);
        }
        .btn-view {
            background: #0dcaf0;
            border: none;
            color: white;
        }
        .btn-edit {
            background: #ffc107;
            border: none;
            color: #212529;
        }
        .btn-delete {
            background: #dc3545;
            border: none;
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4em;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        /* Stats Cards */
        .stats-cards {
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            text-align: center;
            border-left: 4px solid #0d6efd;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content {
                margin-left: 0;
            }
            .table-responsive {
                border-radius: 8px;
            }
            .btn-action {
                display: block;
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="sidebar-brand">
        <h4><i class="fas fa-cogs me-2"></i>ZTAcademy Admin</h4>
    </div>
    
    <nav class="sidebar-nav">
        <a href="admin_dashboard.php">
            <i class="fas fa-tachometer-alt"></i>Dashboard
        </a>
        <a href="manage_users.php">
            <i class="fas fa-users"></i>Manage Users
        </a>
        <a href="manage_modules.php" class="active">
            <i class="fas fa-book"></i>Manage Modules
        </a>
        <a href="manage_quizzes.php">
            <i class="fas fa-tasks"></i>Manage Quizzes
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i>Logout
        </a>
    </nav>
</div>

<!-- Main Content -->
<div class="content">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-2">Manage Learning Modules</h1>
                <p class="text-muted mb-0">Create, edit, and organize educational content for students.</p>
            </div>
            <div class="col-auto">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; font-size: 1.5em;">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row stats-cards">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo $result ? $result->num_rows : 0; ?></div>
                <div class="stat-label">Total Modules</div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="admin_dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
        <a href="add_module.php" class="btn btn-add">
            <i class="fas fa-plus-circle me-2"></i>Add New Module
        </a>
    </div>

    <!-- Modules Table -->
    <div class="modules-container">
        <div class="table-header">
            <h5><i class="fas fa-list me-2"></i>All Learning Modules</h5>
        </div>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title & Description</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="module-id">#<?php echo $row['module_id']; ?></span>
                            </td>
                            <td>
                                <div class="module-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                <div class="module-description">
                                    <?php echo htmlspecialchars(substr($row['description'], 0, 100)) . "..."; ?>
                                </div>
                            </td>
                            <td>
                                <span class="created-date">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <a href="view_module.php?id=<?php echo $row['module_id']; ?>" class="btn btn-action btn-view">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <a href="edit_module.php?id=<?php echo $row['module_id']; ?>" class="btn btn-action btn-edit">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                    <a href="delete_module.php?id=<?php echo $row['module_id']; ?>" class="btn btn-action btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this module? This action cannot be undone.');">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h4 class="text-muted">No Modules Found</h4>
                <p class="text-muted">Get started by creating your first learning module.</p>
                <a href="add_module.php" class="btn btn-primary mt-3">
                    <i class="fas fa-plus-circle me-2"></i>Create First Module
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
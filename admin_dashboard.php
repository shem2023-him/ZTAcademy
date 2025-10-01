<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'db_connect.php';

// Get counts for dashboard stats
$totalUsers = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'] ?? 0;
$totalModules = $conn->query("SELECT COUNT(*) AS c FROM modules")->fetch_assoc()['c'] ?? 0;
$totalQuizzes = $conn->query("SELECT COUNT(*) AS c FROM quizzes")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | ZTAcademy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            line-height: 1.6;
            margin: 0;
            overflow-x: hidden;
        }
        
        /* Layout */
        .app-wrap {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #6f42c1 0%, #0d6efd 100%);
            color: #fff;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }
        .sidebar-brand {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }
        .sidebar-brand h4 {
            font-weight: 700;
            margin: 0;
            color: white;
        }
        .sidebar-nav {
            padding: 20px 15px;
        }
        .sidebar-nav .nav-link {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 8px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .sidebar-nav .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1em;
        }
        .sidebar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }
        .sidebar-nav .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #ffc107;
        }
        
        /* Main Content */
        .content-area {
            flex: 1;
            padding: 30px;
            background: #f8f9fa;
            margin-left: 280px;
            min-height: 100vh;
        }
        
        /* Header */
        .dashboard-header {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #6f42c1;
        }
        
        /* Stat Cards */
        .stat-card {
            border: none;
            border-radius: 12px;
            color: white;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        .stat-card .card-subtitle {
            font-size: 0.9em;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .stat-card h3 {
            font-size: 2.5em;
            font-weight: 700;
            margin: 0;
        }
        .stat-card .icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2.5em;
            opacity: 0.3;
        }
        
        /* Feature Cards */
        .feature-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            border-top: 4px solid transparent;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        .feature-card.users-card {
            border-top-color: #0d6efd;
        }
        .feature-card.modules-card {
            border-top-color: #198754;
        }
        .feature-card.quizzes-card {
            border-top-color: #ffc107;
        }
        .feature-card .card-body {
            padding: 25px;
            text-align: center;
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.5em;
        }
        .users-card .feature-icon {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }
        .modules-card .feature-icon {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        .quizzes-card .feature-icon {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        
        /* Footer */
        footer {
            margin-top: 60px;
            color: #6c757d;
            text-align: center;
            padding: 20px;
            border-top: 1px solid #dee2e6;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content-area {
                margin-left: 0;
            }
            .app-wrap {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="app-wrap">
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h4><i class="fas fa-cogs me-2"></i>ZTAcademy Admin</h4>
        </div>
        
        <nav class="sidebar-nav">
            <a class="nav-link active" href="admin_dashboard.php">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a class="nav-link" href="manage_users.php">
                <i class="fas fa-users"></i>Manage Users
            </a>
            <a class="nav-link" href="manage_modules.php">
                <i class="fas fa-book"></i>Manage Modules
            </a>
            <a class="nav-link" href="manage_quizzes.php">
                <i class="fas fa-tasks"></i>Manage Quizzes
            </a>
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt"></i>Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="content-area">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="h3 mb-2">Welcome, <span class="text-primary">Administrator</span>! 👋</h1>
                    <p class="text-muted mb-0">Manage the ZTAcademy platform and monitor system statistics.</p>
                </div>
                <div class="col-auto">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 60px; height: 60px; font-size: 1.5em;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-5">
            <div class="col-xl-4 col-md-6">
                <div class="stat-card bg-primary shadow">
                    <div class="card-subtitle">Total Users</div>
                    <h3><?php echo $totalUsers; ?></h3>
                    <i class="fas fa-users icon"></i>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="stat-card bg-success shadow">
                    <div class="card-subtitle">Total Modules</div>
                    <h3><?php echo $totalModules; ?></h3>
                    <i class="fas fa-book icon"></i>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="stat-card bg-warning shadow">
                    <div class="card-subtitle">Total Quizzes</div>
                    <h3><?php echo $totalQuizzes; ?></h3>
                    <i class="fas fa-tasks icon"></i>
                </div>
            </div>
        </div>

        <!-- Admin Controls -->
        <div class="row g-4">
            <div class="col-xl-4 col-md-6">
                <div class="card feature-card users-card h-100">
                    <div class="card-body">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title mb-3">Manage Users</h5>
                        <p class="card-text text-muted mb-4">
                            View, edit, and manage user accounts and permissions across the platform.
                        </p>
                        <a href="manage_users.php" class="btn btn-primary w-100">
                            <i class="fas fa-arrow-right me-2"></i>Access User Management
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card feature-card modules-card h-100">
                    <div class="card-body">
                        <div class="feature-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h5 class="card-title mb-3">Manage Modules</h5>
                        <p class="card-text text-muted mb-4">
                            Create, update, and organize learning modules and course content.
                        </p>
                        <a href="manage_modules.php" class="btn btn-success w-100">
                            <i class="fas fa-arrow-right me-2"></i>Access Module Management
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card feature-card quizzes-card h-100">
                    <div class="card-body">
                        <div class="feature-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h5 class="card-title mb-3">Manage Quizzes</h5>
                        <p class="card-text text-muted mb-4">
                            Add, edit, and review quizzes and assessment materials for students.
                        </p>
                        <a href="manage_quizzes.php" class="btn btn-warning w-100">
                            <i class="fas fa-arrow-right me-2"></i>Access Quiz Management
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <small>&copy; <?php echo date("Y"); ?> ZTAcademy — Admin Dashboard</small>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
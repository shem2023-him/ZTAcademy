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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($module['title']); ?> | ZTAcademy</title>
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
            background: linear-gradient(180deg, #0d6efd 0%, #224abe 100%);
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
        
        /* Module Header */
        .module-header {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #0d6efd;
        }
        .module-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .module-description {
            color: #6c757d;
            font-size: 1.1em;
            line-height: 1.6;
        }
        
        /* Content Card */
        .content-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            background: white;
        }
        .content-card .card-body {
            padding: 40px;
        }
        .module-content {
            line-height: 1.8;
            color: #495057;
        }
        .module-content h1, 
        .module-content h2, 
        .module-content h3, 
        .module-content h4 {
            color: #2c3e50;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .module-content h1 {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }
        .module-content p {
            margin-bottom: 20px;
        }
        .module-content ul, 
        .module-content ol {
            margin-bottom: 20px;
            padding-left: 20px;
        }
        .module-content li {
            margin-bottom: 8px;
        }
        .module-content code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        .module-content pre {
            background: #2d3748;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 20px 0;
        }
        .module-content blockquote {
            border-left: 4px solid #0d6efd;
            padding-left: 20px;
            margin: 20px 0;
            color: #6c757d;
            font-style: italic;
        }
        
        /* Action Buttons */
        .action-buttons {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .btn {
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .btn-quiz {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            border: none;
            color: white;
        }
        .btn-edit {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            border: none;
            color: #212529;
        }
        .btn-back {
            background: #6c757d;
            border: none;
            color: white;
        }
        
        /* Progress Indicator */
        .progress-indicator {
            background: #e7f1ff;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        .progress-text {
            color: #0d6efd;
            font-weight: 500;
            margin: 0;
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
            .content-card .card-body {
                padding: 25px;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="sidebar-brand">
        <h4>
            <i class="fas fa-graduation-cap me-2"></i>
            <?php echo $_SESSION['role'] === 'student' ? 'ZTAcademy Student' : 'ZTAcademy Admin'; ?>
        </h4>
    </div>
    
    <nav class="sidebar-nav">
        <?php if ($_SESSION['role'] === 'student'): ?>
            <a href="student_dashboard.php">
                <i class="fas fa-home"></i>Dashboard
            </a>
            <a href="my_progress.php">
                <i class="fas fa-chart-line"></i>My Progress
            </a>
            <a href="glossary.php">
                <i class="fas fa-book"></i>Glossary
            </a>
        <?php else: ?>
            <a href="admin_dashboard.php">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a href="manage_modules.php">
                <i class="fas fa-book"></i>Manage Modules
            </a>
            <a href="manage_users.php">
                <i class="fas fa-users"></i>Manage Users
            </a>
        <?php endif; ?>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i>Logout
        </a>
    </nav>
</div>

<!-- Main Content -->
<div class="content">
    <!-- Module Header -->
    <div class="module-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="module-title"><?php echo htmlspecialchars($module['title']); ?></h1>
                <p class="module-description">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo htmlspecialchars($module['description']); ?>
                </p>
            </div>
            <div class="col-auto">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; font-size: 1.5em;">
                    <i class="fas fa-lock"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if ($_SESSION['role'] === 'student'): ?>
        <div class="progress-indicator">
            <p class="progress-text">
                <i class="fas fa-lightbulb me-2"></i>
                Complete this module and take the quiz to track your progress!
            </p>
        </div>
    <?php endif; ?>

    <!-- Module Content -->
    <div class="content-card">
        <div class="card-body">
            <div class="module-content">
                <?php echo $module['content']; ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <div class="d-flex flex-wrap">
            <?php if ($_SESSION['role'] === 'student'): ?>
                <a href="quiz.php?module_id=<?php echo $module['module_id']; ?>" class="btn btn-quiz">
                    <i class="fas fa-tasks me-2"></i>Take Quiz
                </a>
                <a href="student_dashboard.php" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            <?php else: ?>
                <a href="manage_modules.php" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Back to Manage Modules
                </a>
                <a href="edit_module.php?id=<?php echo $module['module_id']; ?>" class="btn btn-edit">
                    <i class="fas fa-edit me-2"></i>Edit Module
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
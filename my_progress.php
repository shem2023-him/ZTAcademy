<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';
$user_id = $_SESSION['user_id'];

$sql = "
SELECT m.module_id, m.title,
       COUNT(q.quiz_id) AS total_questions,
       SUM(IF(qr.score IS NULL,0,qr.score)) AS total_score
FROM modules m
LEFT JOIN quizzes q ON m.module_id = q.module_id
LEFT JOIN quiz_results qr ON q.quiz_id = qr.quiz_id AND qr.user_id = ?
GROUP BY m.module_id
ORDER BY m.module_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$progressData = [];
$totalModules = 0;
$completedModules = 0;
$averageProgress = 0;

while ($row = $result->fetch_assoc()) {
    $percentage = ($row['total_questions'] > 0) ? round(($row['total_score'] / $row['total_questions']) * 100, 1) : 0;
    $status = ($percentage == 100) ? 'Completed' : (($percentage > 0) ? 'In Progress' : 'Not Started');
    
    if ($percentage == 100) $completedModules++;
    $totalModules++;
    $averageProgress += $percentage;

    $progressData[] = [
        'title' => $row['title'],
        'percentage' => $percentage,
        'status' => $status,
        'score' => $row['total_score'],
        'total_questions' => $row['total_questions']
    ];
}

if ($totalModules > 0) {
    $averageProgress = round($averageProgress / $totalModules, 1);
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Progress | ZTAcademy</title>
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
        
        /* Header */
        .dashboard-header {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #0d6efd;
        }
        
        /* Progress Stats */
        .progress-stats {
            background: linear-gradient(135deg, #0d6efd 0%, #224abe 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }
        .stat-item {
            text-align: center;
            padding: 15px;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        /* Progress Cards */
        .progress-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        .progress-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
        .progress-card.completed {
            border-left-color: #198754;
        }
        .progress-card.in-progress {
            border-left-color: #0d6efd;
        }
        .progress-card.not-started {
            border-left-color: #6c757d;
        }
        .card-body {
            padding: 25px;
        }
        .module-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .badge-completed {
            background: #d1e7dd;
            color: #0f5132;
        }
        .badge-in-progress {
            background: #cfe2ff;
            color: #084298;
        }
        .badge-not-started {
            background: #e2e3e5;
            color: #41464b;
        }
        
        /* Progress Bars */
        .progress-container {
            margin: 15px 0;
        }
        .progress {
            height: 12px;
            border-radius: 10px;
            background: #e9ecef;
            overflow: hidden;
        }
        .progress-bar {
            border-radius: 10px;
            transition: width 0.6s ease;
        }
        .progress-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            font-size: 0.9em;
            color: #6c757d;
        }
        .progress-percentage {
            font-weight: 600;
            color: #2c3e50;
        }
        
        /* Buttons */
        .btn-back {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
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
        }
    </style>
</head>
<body>

<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="sidebar-brand">
        <h4><i class="fas fa-graduation-cap me-2"></i>ZTAcademy Student</h4>
    </div>
    
    <nav class="sidebar-nav">
        <a href="student_dashboard.php">
            <i class="fas fa-home"></i>Dashboard
        </a>
        <a href="my_progress.php" class="active">
            <i class="fas fa-chart-line"></i>My Progress
        </a>
        <a href="glossary.php">
            <i class="fas fa-book"></i>Glossary
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
                <h1 class="h3 mb-2">My Learning Progress 📊</h1>
                <p class="text-muted mb-0">Track your completion and performance across all modules.</p>
            </div>
            <div class="col-auto">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; font-size: 1.5em;">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Statistics -->
    <div class="progress-stats">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $totalModules; ?></div>
                    <div class="stat-label">Total Modules</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $completedModules; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $averageProgress; ?>%</div>
                    <div class="stat-label">Average Progress</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Cards -->
    <div class="row">
        <div class="col-12">
            <?php if (!empty($progressData)): ?>
                <?php foreach($progressData as $progress): ?>
                    <div class="card progress-card <?php echo strtolower(str_replace(' ', '-', $progress['status'])); ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <h5 class="module-title"><?php echo htmlspecialchars($progress['title']); ?></h5>
                                    <span class="status-badge badge-<?php echo strtolower(str_replace(' ', '-', $progress['status'])); ?>">
                                        <i class="fas fa-<?php 
                                            echo $progress['status'] == 'Completed' ? 'check-circle' : 
                                                 ($progress['status'] == 'In Progress' ? 'sync-alt' : 'clock'); 
                                        ?> me-1"></i>
                                        <?php echo $progress['status']; ?>
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <div class="progress-container">
                                        <div class="progress">
                                            <div class="progress-bar 
                                                <?php echo $progress['percentage'] == 100 ? 'bg-success' : 
                                                      ($progress['percentage'] > 0 ? 'bg-primary' : 'bg-secondary'); ?>" 
                                                role="progressbar" 
                                                style="width: <?php echo $progress['percentage']; ?>%;"
                                                aria-valuenow="<?php echo $progress['percentage']; ?>" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                        <div class="progress-info">
                                            <span>Progress</span>
                                            <span class="progress-percentage"><?php echo $progress['percentage']; ?>%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <small class="text-muted">
                                        <?php echo $progress['score']; ?> / <?php echo $progress['total_questions']; ?> pts
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-chart-bar"></i>
                    <h4 class="text-muted">No Progress Data</h4>
                    <p class="text-muted">Start learning modules to track your progress here.</p>
                    <a href="student_dashboard.php" class="btn btn-primary mt-3">
                        <i class="fas fa-play-circle me-2"></i>Start Learning
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="student_dashboard.php" class="btn btn-secondary btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
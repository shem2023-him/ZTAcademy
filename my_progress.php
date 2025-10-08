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
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3a0ca3;
            --secondary: #7209b7;
            --success: #4cc9f0;
            --success-dark: #38b2ac;
            --warning: #f72585;
            --info: #560bad;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --text-dark: #2d3748;
            --text-light: #718096;
            --border-radius: 16px;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --gradient-secondary: linear-gradient(135deg, #7209b7 0%, #560bad 100%);
            --gradient-success: linear-gradient(135deg, #4cc9f0 0%, #38b2ac 100%);
            --gradient-warning: linear-gradient(135deg, #f72585 0%, #b5179e 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7ec 100%);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            height: 100vh;
            background: var(--gradient-primary);
            color: white;
            position: fixed;
            width: 280px;
            padding-top: 30px;
            box-shadow: var(--shadow);
            z-index: 1000;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
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
            font-size: 1.4rem;
        }
        
        .sidebar-nav {
            padding: 0 15px;
        }
        
        .sidebar-nav a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 14px 20px;
            margin: 6px 0;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-nav a i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.2em;
            transition: transform 0.3s ease;
        }
        
        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(8px);
            color: white;
        }
        
        .sidebar-nav a:hover i {
            transform: scale(1.1);
        }
        
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #ffd166;
            color: white;
            box-shadow: 0 4px 15px rgba(255, 209, 102, 0.3);
        }
        
        /* Main Content */
        .content {
            margin-left: 280px;
            padding: 30px;
            min-height: 100vh;
        }
        
        /* Header */
        .dashboard-header {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            border-left: 6px solid var(--primary);
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 120px;
            height: 120px;
            background: var(--gradient-primary);
            opacity: 0.05;
            border-radius: 0 0 0 100%;
        }
        
        .user-avatar {
            width: 70px;
            height: 70px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8em;
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
            transition: transform 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
        }
        
        /* Progress Stats */
        .progress-stats {
            background: var(--gradient-primary);
            color: white;
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        
        .progress-stats::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .progress-stats::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px 15px;
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 3em;
            font-weight: 800;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .stat-label {
            font-size: 1em;
            opacity: 0.9;
            font-weight: 500;
        }
        
        /* Progress Cards */
        .progress-card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: all 0.4s ease;
            margin-bottom: 25px;
            background: var(--card-bg);
            overflow: hidden;
            border-left: 6px solid;
            position: relative;
        }
        
        .progress-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: inherit;
            opacity: 0.3;
        }
        
        .progress-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .progress-card.completed {
            border-left-color: #4cc9f0;
            background: linear-gradient(135deg, #ffffff 0%, #f0fdfa 100%);
        }
        
        .progress-card.in-progress {
            border-left-color: #4361ee;
            background: linear-gradient(135deg, #ffffff 0%, #f0f4ff 100%);
        }
        
        .progress-card.not-started {
            border-left-color: #a0aec0;
            background: linear-gradient(135deg, #ffffff 0%, #f7fafc 100%);
        }
        
        .card-body {
            padding: 30px;
        }
        
        .module-title {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 12px;
            font-size: 1.3em;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.9em;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .badge-completed {
            background: linear-gradient(135deg, #4cc9f0 0%, #38b2ac 100%);
            color: white;
        }
        
        .badge-in-progress {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
        }
        
        .badge-not-started {
            background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
            color: white;
        }
        
        /* Progress Bars */
        .progress-container {
            margin: 20px 0;
        }
        
        .progress {
            height: 14px;
            border-radius: 12px;
            background: #e9ecef;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .progress-bar {
            border-radius: 12px;
            transition: width 0.8s ease-in-out;
            position: relative;
            overflow: hidden;
        }
        
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .progress-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            font-size: 0.95em;
        }
        
        .progress-percentage {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.1em;
        }
        
        .score-display {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 12px 20px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid #e9ecef;
        }
        
        .score-value {
            font-size: 1.4em;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 2px;
        }
        
        .score-label {
            font-size: 0.85em;
            color: var(--text-light);
            font-weight: 500;
        }
        
        /* Section Headers */
        .section-header {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
            position: relative;
        }
        
        .section-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--gradient-primary);
            border-radius: 3px;
        }
        
        /* Buttons */
        .btn-back {
            background: var(--gradient-secondary);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(114, 9, 183, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(114, 9, 183, 0.4);
        }
        
        .btn-primary-custom {
            background: var(--gradient-primary);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-light);
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .empty-state i {
            font-size: 5em;
            color: #e2e8f0;
            margin-bottom: 25px;
            opacity: 0.7;
        }
        
        /* Animation for cards */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .progress-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-radius: 0;
            }
            
            .content {
                margin-left: 0;
            }
        }
        
        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }
            
            .dashboard-header {
                padding: 20px;
            }
            
            .progress-stats {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 2.5em;
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
        <a href="resources.php">
            <i class="fas fa-folder-open"></i>Resources
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
                <p class="text-muted mb-0"><b>Track your completion and performance across all modules. Every step counts!</b></p>
            </div>
            <div class="col-auto">
                <div class="user-avatar">
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
    <h4 class="section-header"><i class="fas fa-tasks me-2"></i>Module Progress</h4>
    <div class="row">
        <div class="col-12">
            <?php if (!empty($progressData)): ?>
                <?php foreach($progressData as $progress): ?>
                    <div class="card progress-card <?php echo strtolower(str_replace(' ', '-', $progress['status'])); ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-lg-4 col-md-12 mb-3 mb-md-0">
                                    <h5 class="module-title"><?php echo htmlspecialchars($progress['title']); ?></h5>
                                    <span class="status-badge badge-<?php echo strtolower(str_replace(' ', '-', $progress['status'])); ?>">
                                        <i class="fas fa-<?php 
                                            echo $progress['status'] == 'Completed' ? 'check-circle' : 
                                                 ($progress['status'] == 'In Progress' ? 'sync-alt' : 'clock'); 
                                        ?>"></i>
                                        <?php echo $progress['status']; ?>
                                    </span>
                                </div>
                                <div class="col-lg-5 col-md-8 mb-3 mb-md-0">
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
                                            <span class="text-muted">Your progress</span>
                                            <span class="progress-percentage"><?php echo $progress['percentage']; ?>%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <div class="score-display">
                                        <div class="score-value"><?php echo $progress['score']; ?>/<?php echo $progress['total_questions']; ?></div>
                                        <div class="score-label">Points Earned</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-chart-bar"></i>
                    <h4 class="text-muted mt-3">No Progress Data Yet</h4>
                    <p class="text-muted mb-4">Start learning modules to track your amazing progress here!</p>
                    <a href="student_dashboard.php" class="btn btn-primary-custom text-white">
                        <i class="fas fa-play-circle me-2"></i>Start Learning Journey
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-5 text-center">
        <a href="student_dashboard.php" class="btn btn-back text-white">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
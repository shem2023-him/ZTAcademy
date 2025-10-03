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

$user_id = $_SESSION['user_id'];

// Fetch modules
$sql = "SELECT module_id, title, description FROM modules ORDER BY created_at DESC";
$result = $conn->query($sql);

// Fetch learning statistics
$stats = [];
$progress_sql = "
    SELECT 
        COUNT(DISTINCT module_id) as total_modules,
        COUNT(DISTINCT CASE WHEN status = 'completed' THEN module_id END) as completed_modules,
        COUNT(DISTINCT CASE WHEN status = 'in_progress' THEN module_id END) as in_progress_modules,
        COALESCE(SUM(time_spent_minutes), 0) as total_time_spent,
        AVG(CASE WHEN score IS NOT NULL THEN score END) as avg_score,
        MAX(last_accessed) as last_study_date
    FROM user_progress 
    WHERE user_id = ?
";
$stmt = $conn->prepare($progress_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stmt->close();

// Calculate progress percentage
$stats['completion_rate'] = $stats['total_modules'] > 0 ? 
    round(($stats['completed_modules'] / $stats['total_modules']) * 100) : 0;

// Fetch recent activity
$activity_sql = "
    SELECT m.title, up.last_accessed, up.status, up.score
    FROM user_progress up
    JOIN modules m ON up.module_id = m.module_id
    WHERE up.user_id = ?
    ORDER BY up.last_accessed DESC
    LIMIT 3
";
$stmt = $conn->prepare($activity_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$activity_result = $stmt->get_result();
$recent_activity = $activity_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate study streak (simplified version)
$streak_sql = "
    SELECT COUNT(DISTINCT DATE(last_accessed)) as streak_days
    FROM user_progress 
    WHERE user_id = ? 
    AND last_accessed >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
";
$stmt = $conn->prepare($streak_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$streak_result = $stmt->get_result();
$streak_data = $streak_result->fetch_assoc();
$stats['study_streak'] = $streak_data['streak_days'] ?? 0;
$stmt->close();

// Handle cases where no progress data exists
if (!$stats['total_modules']) {
    $stats['completion_rate'] = 0;
    $stats['completed_modules'] = 0;
    $stats['in_progress_modules'] = 0;
    $stats['total_time_spent'] = 0;
    $stats['avg_score'] = 0;
    $stats['study_streak'] = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard | ZTAcademy</title>
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
        .welcome-text {
            color: #6c757d;
            font-size: 1.1em;
        }
        
        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-top: 4px solid;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        .stat-card.completion { border-color: #28a745; }
        .stat-card.time { border-color: #17a2b8; }
        .stat-card.score { border-color: #ffc107; }
        .stat-card.streak { border-color: #dc3545; }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            margin-bottom: 15px;
        }
        .stat-icon.completion { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .stat-icon.time { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
        .stat-icon.score { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .stat-icon.streak { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
        
        .stat-value {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        /* Progress Bar */
        .progress {
            height: 8px;
            border-radius: 10px;
            margin-top: 10px;
        }
        
        /* Activity Section */
        .activity-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f3f4;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.1em;
        }
        .activity-icon.completed { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .activity-icon.in-progress { background: rgba(13, 110, 253, 0.1); color: #0d6efd; }
        .activity-icon.new { background: rgba(108, 117, 125, 0.1); color: #6c757d; }
        
        .activity-content {
            flex: 1;
        }
        .activity-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .activity-meta {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        /* Module Cards */
        .module-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            border-top: 4px solid #0d6efd;
        }
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        .module-card .card-body {
            padding: 25px;
        }
        .card-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .card-text {
            color: #6c757d;
            line-height: 1.6;
        }
        
        /* Buttons */
        .btn-module {
            background: linear-gradient(135deg, #0d6efd 0%, #224abe 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-module:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
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
            .stats-grid {
                grid-template-columns: 1fr;
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
        <a href="student_dashboard.php" class="active">
            <i class="fas fa-home"></i>Dashboard
        </a>
        <a href="my_progress.php">
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
                <h1 class="h3 mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
                <p class="welcome-text mb-0">Here's your learning progress and available modules.</p>
            </div>
            <div class="col-auto">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; font-size: 1.5em;">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Learning Statistics -->
    <div class="stats-grid">
        <div class="stat-card completion">
            <div class="stat-icon completion">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-value"><?php echo $stats['completion_rate']; ?>%</div>
            <div class="stat-label">Course Completion</div>
            <div class="progress">
                <div class="progress-bar bg-success" style="width: <?php echo $stats['completion_rate']; ?>%"></div>
            </div>
        </div>
        
        <div class="stat-card time">
            <div class="stat-icon time">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_time_spent']; ?>m</div>
            <div class="stat-label">Total Study Time</div>
            <small class="text-muted">Time spent learning</small>
        </div>
        
        <div class="stat-card score">
            <div class="stat-icon score">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-value">
                <?php echo $stats['avg_score'] ? number_format($stats['avg_score'], 1) : '0.0'; ?>
            </div>
            <div class="stat-label">Average Score</div>
            <small class="text-muted">Based on completed assessments</small>
        </div>
        
        <div class="stat-card streak">
            <div class="stat-icon streak">
                <i class="fas fa-fire"></i>
            </div>
            <div class="stat-value"><?php echo $stats['study_streak']; ?>d</div>
            <div class="stat-label">Study Streak</div>
            <small class="text-muted">Consecutive learning days</small>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="activity-card">
        <h5 class="mb-4"><i class="fas fa-history me-2"></i>Recent Activity</h5>
        <?php if (!empty($recent_activity)): ?>
            <?php foreach ($recent_activity as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon <?php echo $activity['status']; ?>">
                        <i class="fas fa-<?php echo $activity['status'] === 'completed' ? 'check-circle' : 'play-circle'; ?>"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></div>
                        <div class="activity-meta">
                            <?php 
                            echo ucfirst(str_replace('_', ' ', $activity['status']));
                            if ($activity['score']) echo " • Score: {$activity['score']}%";
                            if ($activity['last_accessed']) echo " • " . date('M j, g:i A', strtotime($activity['last_accessed']));
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-3"></i>
                <p>No recent activity. Start learning to see your progress here!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Available Modules -->
    <h4 class="mb-4"><i class="fas fa-book-open me-2"></i>Available Modules</h4>
    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($module = $result->fetch_assoc()): ?>
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card module-card">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded p-3 d-inline-flex mb-3">
                                    <i class="fas fa-lock fa-lg"></i>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($module['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($module['description']); ?></p>
                            </div>
                            <div class="mt-auto">
                                <a href="view_module.php?id=<?php echo $module['module_id']; ?>" 
                                   class="btn btn-module text-white w-100">
                                    <i class="fas fa-play-circle me-2"></i>Start Module
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4 class="text-muted">No Modules Available</h4>
                    <p class="text-muted">Check back later for new learning materials.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
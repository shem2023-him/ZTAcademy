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
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3a0ca3;
            --secondary: #7209b7;
            --success: #4cc9f0;
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
            --gradient-success: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
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
        
        .welcome-text {
            color: var(--text-light);
            font-size: 1.15em;
            max-width: 600px;
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
        
        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            border-top: 5px solid;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: inherit;
            opacity: 0.3;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card.completion { border-color: #4cc9f0; background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%); }
        .stat-card.time { border-color: #4361ee; background: linear-gradient(135deg, #ffffff 0%, #f0f4ff 100%); }
        .stat-card.score { border-color: #7209b7; background: linear-gradient(135deg, #ffffff 0%, #faf0ff 100%); }
        .stat-card.streak { border-color: #f72585; background: linear-gradient(135deg, #ffffff 0%, #fff0f5 100%); }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8em;
            margin-bottom: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon.completion { background: var(--gradient-success); color: white; }
        .stat-icon.time { background: var(--gradient-primary); color: white; }
        .stat-icon.score { background: var(--gradient-secondary); color: white; }
        .stat-icon.streak { background: var(--gradient-warning); color: white; }
        
        .stat-value {
            font-size: 2.4em;
            font-weight: 800;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--text-dark) 0%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            color: var(--text-light);
            font-weight: 600;
            font-size: 1em;
            margin-bottom: 5px;
        }
        
        /* Progress Bar */
        .progress {
            height: 10px;
            border-radius: 10px;
            margin-top: 15px;
            background: #e9ecef;
            overflow: hidden;
        }
        
        .progress-bar {
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }
        
        /* Activity Section */
        .activity-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
            border-left: 4px solid var(--primary);
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px solid #f1f3f4;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            background: rgba(67, 97, 238, 0.03);
            border-radius: 12px;
            padding-left: 15px;
            padding-right: 15px;
            transform: translateX(5px);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 18px;
            font-size: 1.3em;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .activity-icon.completed { background: var(--gradient-success); color: white; }
        .activity-icon.in_progress { background: var(--gradient-primary); color: white; }
        .activity-icon.new { background: var(--gradient-secondary); color: white; }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-dark);
            font-size: 1.1em;
        }
        
        .activity-meta {
            color: var(--text-light);
            font-size: 0.95em;
        }
        
        /* Module Cards */
        .module-card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: all 0.4s ease;
            height: 100%;
            border-top: 5px solid var(--primary);
            background: var(--card-bg);
            overflow: hidden;
        }
        
        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .module-card .card-body {
            padding: 30px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .module-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.6em;
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.3);
        }
        
        .card-title {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        
        .card-text {
            color: var(--text-light);
            line-height: 1.6;
            flex-grow: 1;
        }
        
        /* Buttons */
        .btn-module {
            background: var(--gradient-primary);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-module:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
            background: var(--gradient-secondary);
        }
        
        .btn-module:active {
            transform: translateY(-1px);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 5em;
            color: #e2e8f0;
            margin-bottom: 25px;
            opacity: 0.7;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }
            
            .dashboard-header {
                padding: 20px;
            }
            
            .stat-card {
                padding: 20px;
            }
        }
        
        /* Animation for stats */
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
        
        .stat-card, .activity-card, .module-card {
            animation: fadeInUp 0.6s ease-out;
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
                <h1 class="h3 mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h1>
                <p class="welcome-text mb-0"><b>Here's your learning progress and available modules. Keep up the great work!</b></p>
            </div>
            <div class="col-auto">
                <div class="user-avatar">
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
        <h4 class="section-header"><i class="fas fa-history me-2"></i>Recent Activity</h4>
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
                            $status_text = ucfirst(str_replace('_', ' ', $activity['status']));
                            $score_text = $activity['score'] ? " • Score: {$activity['score']}%" : "";
                            $time_text = $activity['last_accessed'] ? " • " . date('M j, g:i A', strtotime($activity['last_accessed'])) : "";
                            echo $status_text . $score_text . $time_text;
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No recent activity. Start learning to see your progress here!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Available Modules -->
    <h4 class="section-header"><i class="fas fa-book-open me-2"></i>Available Modules</h4>
    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($module = $result->fetch_assoc()): ?>
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card module-card">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-3">
                                <div class="module-icon">
                                    <i class="fas fa-lock-open"></i>
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
                    <h4 class="text-muted mt-3">No Modules Available</h4>
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
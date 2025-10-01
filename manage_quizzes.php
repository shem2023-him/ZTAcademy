<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "zta_app";
$conn = new mysqli($host, $user, $pass, $db);

// Add quiz
if (isset($_POST['add'])) {
    $module_id = $_POST['module_id'];
    $question = $_POST['question'];
    $a = $_POST['option_a'];
    $b = $_POST['option_b'];
    $c = $_POST['option_c'];
    $d = $_POST['option_d'];
    $correct = $_POST['correct_option'];

    $stmt = $conn->prepare("INSERT INTO quizzes (module_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $module_id, $question, $a, $b, $c, $d, $correct);
    $stmt->execute();
}

// Delete quiz
if (isset($_GET['delete'])) {
    $qid = intval($_GET['delete']);
    $conn->query("DELETE FROM quizzes WHERE quiz_id = $qid");
    header("Location: manage_quizzes.php");
    exit;
}

// Get modules for dropdown
$modules = $conn->query("SELECT module_id, title FROM modules");

// Get quizzes
$quizzes = $conn->query("SELECT q.quiz_id, m.title, q.question, q.correct_option FROM quizzes q JOIN modules m ON q.module_id = m.module_id ORDER BY q.quiz_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Quizzes | ZTAcademy Admin</title>
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
            border-left: 4px solid #ffc107;
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
        
        /* Add Quiz Form */
        .add-quiz-form {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-header {
            background: linear-gradient(135deg, #0d6efd 0%, #224abe 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 20px 25px;
            margin: -30px -30px 25px -30px;
        }
        .form-header h5 {
            margin: 0;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .option-input {
            position: relative;
        }
        .option-label {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: #0d6efd;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9em;
        }
        .option-input .form-control {
            padding-left: 50px;
        }
        
        /* Quizzes Table */
        .quizzes-container {
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
        
        /* Quiz Info */
        .quiz-id {
            color: #6c757d;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }
        .module-name {
            font-weight: 600;
            color: #2c3e50;
        }
        .question-text {
            color: #495057;
            line-height: 1.5;
        }
        .correct-answer {
            background: #d1e7dd;
            color: #0f5132;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85em;
        }
        
        /* Action Buttons in Table */
        .btn-action {
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.8em;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-action:hover {
            transform: translateY(-1px);
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
            .options-grid {
                grid-template-columns: 1fr;
            }
            .table-responsive {
                border-radius: 8px;
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
        <a href="manage_modules.php">
            <i class="fas fa-book"></i>Manage Modules
        </a>
        <a href="manage_quizzes.php" class="active">
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
                <h1 class="h3 mb-2">Manage Assessment Quizzes</h1>
                <p class="text-muted mb-0">Create and manage multiple-choice quizzes for student assessments.</p>
            </div>
            <div class="col-auto">
                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; font-size: 1.5em;">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row stats-cards">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo $quizzes ? $quizzes->num_rows : 0; ?></div>
                <div class="stat-label">Total Quizzes</div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="admin_dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Add Quiz Form -->
    <form method="POST" class="add-quiz-form">
        <div class="form-header">
            <h5><i class="fas fa-plus-circle me-2"></i>Create New Quiz Question</h5>
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-book me-2 text-primary"></i>Select Module
            </label>
            <select name="module_id" class="form-select" required>
                <option value="">Choose a learning module...</option>
                <?php while ($m = $modules->fetch_assoc()): ?>
                    <option value="<?= $m['module_id'] ?>"><?= htmlspecialchars($m['title']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-question-circle me-2 text-primary"></i>Question Text
            </label>
            <input type="text" name="question" class="form-control" placeholder="Enter the quiz question..." required>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-list-ol me-2 text-primary"></i>Multiple Choice Options
            </label>
            <div class="options-grid">
                <div class="option-input">
                    <div class="option-label">A</div>
                    <input type="text" name="option_a" class="form-control" placeholder="Option A" required>
                </div>
                <div class="option-input">
                    <div class="option-label">B</div>
                    <input type="text" name="option_b" class="form-control" placeholder="Option B" required>
                </div>
                <div class="option-input">
                    <div class="option-label">C</div>
                    <input type="text" name="option_c" class="form-control" placeholder="Option C" required>
                </div>
                <div class="option-input">
                    <div class="option-label">D</div>
                    <input type="text" name="option_d" class="form-control" placeholder="Option D" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-check-circle me-2 text-primary"></i>Correct Answer
            </label>
            <select name="correct_option" class="form-select" required>
                <option value="A">Option A</option>
                <option value="B">Option B</option>
                <option value="C">Option C</option>
                <option value="D">Option D</option>
            </select>
        </div>

        <div class="text-end">
            <button type="submit" name="add" class="btn btn-add">
                <i class="fas fa-plus-circle me-2"></i>Add Quiz Question
            </button>
        </div>
    </form>

    <!-- Quizzes Table -->
    <div class="quizzes-container">
        <div class="table-header">
            <h5><i class="fas fa-list me-2"></i>Existing Quiz Questions</h5>
        </div>
        
        <?php if ($quizzes && $quizzes->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Module</th>
                            <th>Question</th>
                            <th>Correct Answer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($q = $quizzes->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="quiz-id">#<?= $q['quiz_id'] ?></span>
                            </td>
                            <td>
                                <span class="module-name"><?= htmlspecialchars($q['title']) ?></span>
                            </td>
                            <td>
                                <div class="question-text"><?= htmlspecialchars($q['question']) ?></div>
                            </td>
                            <td>
                                <span class="correct-answer">
                                    <i class="fas fa-check me-1"></i><?= $q['correct_option'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="manage_quizzes.php?delete=<?= $q['quiz_id'] ?>" 
                                   class="btn btn-action btn-delete"
                                   onclick="return confirm('Are you sure you want to delete this quiz question? This action cannot be undone.');">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                <h4 class="text-muted">No Quiz Questions Found</h4>
                <p class="text-muted">Create your first quiz question using the form above.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
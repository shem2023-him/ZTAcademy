<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "zta_app";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['module_id'])) {
    die("Error: Module not specified.");
}
$module_id = intval($_GET['module_id']);
$user_id   = $_SESSION['user_id'];

// Fetch module title
$stmt = $conn->prepare("SELECT title FROM modules WHERE module_id = ?");
$stmt->bind_param("i", $module_id);
$stmt->execute();
$module_result = $stmt->get_result();
$module = $module_result->fetch_assoc();

// Fetch quiz questions with answers
$sql = "SELECT q.quiz_id, q.question, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option, r.selected_option
        FROM quizzes q
        LEFT JOIN quiz_results r 
        ON q.quiz_id = r.quiz_id AND r.user_id = ?
        WHERE q.module_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $module_id);
$stmt->execute();
$result = $stmt->get_result();

$total_questions = $result->num_rows;
$correct_answers = 0;
$feedback = [];

while ($row = $result->fetch_assoc()) {
    $user_answer = $row['selected_option'];
    $correct = $row['correct_option'];
    $is_correct = ($user_answer === $correct);

    if ($is_correct) {
        $correct_answers++;
    }

    $feedback[] = [
        'question' => $row['question'],
        'user_answer' => $user_answer,
        'correct_answer' => $correct,
        'is_correct' => $is_correct
    ];
}

$score = ($total_questions > 0) ? round(($correct_answers / $total_questions) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quiz Results - <?php echo htmlspecialchars($module['title']); ?> | ZTAcademy</title>
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
        
        /* Results Header */
        .results-header {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid <?php echo $score >= 50 ? '#198754' : '#dc3545'; ?>;
        }
        .results-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .module-name {
            color: #6c757d;
            font-size: 1.1em;
        }
        
        /* Score Summary */
        .score-summary {
            background: linear-gradient(135deg, #0d6efd 0%, #224abe 100%);
            color: white;
            border-radius: 12px;
            padding: 30px;
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
        
        /* Progress Bar */
        .score-progress {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .progress {
            height: 25px;
            border-radius: 12px;
            background: #e9ecef;
            overflow: hidden;
            margin-bottom: 15px;
        }
        .progress-bar {
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9em;
            transition: width 1s ease-in-out;
        }
        .score-text {
            text-align: center;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1em;
        }
        
        /* Performance Message */
        .performance-message {
            background: <?php echo $score >= 50 ? '#d1e7dd' : '#f8d7da'; ?>;
            border: 1px solid <?php echo $score >= 50 ? '#badbcc' : '#f5c2c7'; ?>;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            color: <?php echo $score >= 50 ? '#0f5132' : '#842029'; ?>;
            text-align: center;
        }
        .performance-message i {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        
        /* Feedback Cards */
        .feedback-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        .feedback-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        .question-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        .question-card.correct {
            border-left-color: #198754;
        }
        .question-card.incorrect {
            border-left-color: #dc3545;
        }
        .question-card .card-body {
            padding: 25px;
        }
        .question-number {
            background: #0d6efd;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .question-text {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        .answer-feedback {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .user-answer {
            background: #f8f9fa;
            border-left: 4px solid #6c757d;
        }
        .user-answer.correct {
            background: #d1e7dd;
            border-left-color: #198754;
        }
        .user-answer.incorrect {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .correct-answer {
            background: #e7f1ff;
            border-left: 4px solid #0d6efd;
        }
        .answer-label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #495057;
        }
        .answer-value {
            color: #2c3e50;
            font-weight: 500;
        }
        
        /* Action Buttons */
        .action-buttons {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .btn {
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 10px;
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
            .feedback-section {
                padding: 20px;
            }
            .question-card .card-body {
                padding: 20px;
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
        <a href="my_progress.php">
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
    <!-- Results Header -->
    <div class="results-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="results-title">Quiz Results</h1>
                <p class="module-name">
                    <i class="fas fa-clipboard-check me-2"></i>
                    <?php echo htmlspecialchars($module['title']); ?>
                </p>
            </div>
            <div class="col-auto">
                <div class="bg-<?php echo $score >= 50 ? 'success' : 'danger'; ?> text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; font-size: 1.5em;">
                    <i class="fas fa-<?php echo $score >= 50 ? 'trophy' : 'redo-alt'; ?>"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Score Summary -->
    <div class="score-summary">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_questions; ?></div>
                    <div class="stat-label">Total Questions</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $correct_answers; ?></div>
                    <div class="stat-label">Correct Answers</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $score; ?>%</div>
                    <div class="stat-label">Final Score</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="score-progress">
        <div class="progress">
            <div class="progress-bar <?php echo ($score >= 50) ? 'bg-success' : 'bg-danger'; ?>" 
                 role="progressbar" 
                 style="width: <?php echo $score; ?>%;"
                 aria-valuenow="<?php echo $score; ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                <?php echo $score; ?>%
            </div>
        </div>
        <div class="score-text">
            <?php echo "$correct_answers out of $total_questions questions correct"; ?>
        </div>
    </div>

    <!-- Performance Message -->
    <div class="performance-message">
        <i class="fas fa-<?php echo $score >= 50 ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
        <h5 class="mb-2">
            <?php echo $score >= 50 ? 'Congratulations! You passed the quiz!' : 'Keep practicing! You can do better!'; ?>
        </h5>
        <p class="mb-0">
            <?php echo $score >= 50 
                ? 'Great job demonstrating your understanding of Zero Trust concepts.' 
                : 'Review the material and try again to improve your score.'; ?>
        </p>
    </div>

    <!-- Feedback Section -->
    <div class="feedback-section">
        <h4 class="feedback-title">
            <i class="fas fa-list-alt me-2"></i>Question Feedback
        </h4>
        
        <?php foreach ($feedback as $i => $f): ?>
            <div class="card question-card <?php echo $f['is_correct'] ? 'correct' : 'incorrect'; ?>">
                <div class="card-body">
                    <div class="question-number"><?php echo $i+1; ?></div>
                    <div class="question-text"><?php echo htmlspecialchars($f['question']); ?></div>
                    
                    <!-- User Answer -->
                    <div class="answer-feedback user-answer <?php echo $f['is_correct'] ? 'correct' : 'incorrect'; ?>">
                        <div class="answer-label">
                            <i class="fas fa-user me-2"></i>Your Answer:
                        </div>
                        <div class="answer-value">
                            <?php if ($f['user_answer']): ?>
                                <span class="fw-bold"><?php echo $f['user_answer']; ?></span>
                                <?php if ($f['is_correct']): ?>
                                    <span class="text-success ms-2"><i class="fas fa-check-circle me-1"></i>Correct</span>
                                <?php else: ?>
                                    <span class="text-danger ms-2"><i class="fas fa-times-circle me-1"></i>Incorrect</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not answered</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Correct Answer -->
                    <div class="answer-feedback correct-answer">
                        <div class="answer-label">
                            <i class="fas fa-check-circle me-2 text-primary"></i>Correct Answer:
                        </div>
                        <div class="answer-value fw-bold text-primary">
                            <?php echo $f['correct_answer']; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="student_dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
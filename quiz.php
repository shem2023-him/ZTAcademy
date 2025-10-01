<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$module_id = $_GET['module_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$module_id) die("Module not specified");

// Fetch module title
$stmt = $conn->prepare("SELECT title FROM modules WHERE module_id = ?");
$stmt->bind_param("i", $module_id);
$stmt->execute();
$module = $stmt->get_result()->fetch_assoc();

// Fetch module questions
$sql = "SELECT * FROM quizzes WHERE module_id = ? ORDER BY quiz_id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $module_id);
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);

if (!$questions) die("No quizzes found for this module.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($module['title']); ?> - Take Quiz | ZTAcademy</title>
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
        
        /* Quiz Header */
        .quiz-header {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #198754;
        }
        .quiz-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .quiz-info {
            color: #6c757d;
            font-size: 1.1em;
        }
        
        /* Quiz Form */
        .quiz-form-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        /* Question Cards */
        .question-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            transition: all 0.3s ease;
            border-left: 4px solid #0d6efd;
        }
        .question-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .question-card .card-body {
            padding: 30px;
        }
        .question-number {
            background: #0d6efd;
            color: white;
            width: 40px;
            height: 40px;
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
            font-size: 1.1em;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        /* Option Styles */
        .option-item {
            padding: 15px;
            margin-bottom: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .option-item:hover {
            border-color: #0d6efd;
            background: #f8f9fa;
        }
        .option-item.selected {
            border-color: #0d6efd;
            background: #e7f1ff;
        }
        .form-check-input {
            margin-top: 0.3em;
            cursor: pointer;
        }
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .form-check-label {
            cursor: pointer;
            font-weight: 500;
            color: #495057;
            margin-left: 10px;
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
        .btn-submit {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            border: none;
            color: white;
        }
        
        /* Quiz Stats */
        .quiz-stats {
            background: linear-gradient(135deg, #0d6efd 0%, #224abe 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }
        .stat-item {
            text-align: center;
            padding: 10px;
        }
        .stat-number {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        /* Instructions */
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            color: #856404;
        }
        .instructions h5 {
            color: #856404;
            margin-bottom: 10px;
        }
        .instructions ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .instructions li {
            margin-bottom: 5px;
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
            .quiz-form-container {
                padding: 25px;
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
    <!-- Quiz Header -->
    <div class="quiz-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="quiz-title"><?php echo htmlspecialchars($module['title']); ?> - Assessment</h1>
                <p class="quiz-info">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Test your knowledge with this multiple-choice quiz
                </p>
            </div>
            <div class="col-auto">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; font-size: 1.5em;">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quiz Stats -->
    <div class="quiz-stats">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($questions); ?></div>
                    <div class="stat-label">Total Questions</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number">Multiple</div>
                    <div class="stat-label">Choice Format</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Required to Pass</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="instructions">
        <h5><i class="fas fa-info-circle me-2"></i>Quiz Instructions</h5>
        <ul>
            <li>Select one answer for each question</li>
            <li>All questions are required to submit the quiz</li>
            <li>Choose the best possible answer for each scenario</li>
            <li>You'll receive immediate feedback after submission</li>
        </ul>
    </div>

    <!-- Quiz Form -->
    <form action="submit_quiz.php" method="post">
        <div class="quiz-form-container">
            <!-- Hidden input to pass module_id -->
            <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
            
            <?php foreach ($questions as $i => $q): ?>
                <div class="card question-card">
                    <div class="card-body">
                        <div class="question-number"><?php echo $i+1; ?></div>
                        <div class="question-text">
                            <?php echo htmlspecialchars($q['question']); ?>
                        </div>
                        
                        <?php foreach (['A','B','C','D'] as $opt): ?>
                            <?php if (!empty($q['option_'.strtolower($opt)])): ?>
                                <div class="option-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="answers[<?php echo $q['quiz_id']; ?>]" 
                                               value="<?php echo $opt; ?>" 
                                               id="q<?php echo $q['quiz_id']; ?>_<?php echo $opt; ?>" required>
                                        <label class="form-check-label" for="q<?php echo $q['quiz_id']; ?>_<?php echo $opt; ?>">
                                            <?php echo $opt; ?>. <?php echo htmlspecialchars($q['option_'.strtolower($opt)]); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button type="submit" class="btn btn-submit">
                <i class="fas fa-paper-plane me-2"></i>Submit Quiz
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
    // Add interactive selection styling for options
    document.addEventListener('DOMContentLoaded', function() {
        const optionItems = document.querySelectorAll('.option-item');
        
        optionItems.forEach(item => {
            const radio = item.querySelector('input[type="radio"]');
            
            item.addEventListener('click', function(e) {
                if (e.target.type !== 'radio') {
                    radio.checked = true;
                }
                
                // Remove selected class from all options in this question
                const questionCard = this.closest('.question-card');
                questionCard.querySelectorAll('.option-item').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
            });
            
            // Update visual state when radio is checked
            radio.addEventListener('change', function() {
                if (this.checked) {
                    const questionCard = this.closest('.question-card');
                    questionCard.querySelectorAll('.option-item').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    this.closest('.option-item').classList.add('selected');
                }
            });
            
            // Initialize selected state
            if (radio.checked) {
                item.classList.add('selected');
            }
        });
    });
</script>
</body>
</html>
<?php $conn->close(); ?>
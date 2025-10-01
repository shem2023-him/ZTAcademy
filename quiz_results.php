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
  <title>Quiz Results - <?php echo htmlspecialchars($module['title']); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card shadow-lg">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0">Quiz Results: <?php echo htmlspecialchars($module['title']); ?></h3>
      </div>
      <div class="card-body">
        <!-- Summary -->
        <div class="row text-center mb-4">
          <div class="col-md-4">
            <h5>Total Questions</h5>
            <p class="fw-bold"><?php echo $total_questions; ?></p>
          </div>
          <div class="col-md-4">
            <h5>Correct Answers</h5>
            <p class="fw-bold text-success"><?php echo $correct_answers; ?></p>
          </div>
          <div class="col-md-4">
            <h5>Score</h5>
            <p class="fw-bold"><?php echo "$correct_answers / $total_questions ($score%)"; ?></p>
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress mb-4" style="height: 25px;">
          <div class="progress-bar <?php echo ($score >= 50) ? 'bg-success' : 'bg-danger'; ?>" 
               role="progressbar" 
               style="width: <?php echo $score; ?>%;">
            <?php echo $score; ?>%
          </div>
        </div>

        <!-- Feedback -->
        <h5 class="mb-3">Question Feedback</h5>
        <?php foreach ($feedback as $i => $f): ?>
          <div class="border rounded p-3 mb-3 <?php echo $f['is_correct'] ? 'bg-light' : 'bg-white'; ?>">
            <strong>Q<?php echo $i+1; ?>: <?php echo htmlspecialchars($f['question']); ?></strong><br>
            
            <span>Your answer: 
              <?php if ($f['user_answer']): ?>
                <?php if ($f['is_correct']): ?>
                  <span class="text-success fw-bold"><?php echo $f['user_answer']; ?> (Correct)</span>
                <?php else: ?>
                  <span class="text-danger fw-bold"><?php echo $f['user_answer']; ?> (Wrong)</span>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-muted">Not answered</span>
              <?php endif; ?>
            </span><br>

            <span>Correct answer: <span class="fw-bold text-primary"><?php echo $f['correct_answer']; ?></span></span>
          </div>
        <?php endforeach; ?>

        <!-- Back Button -->
        <div class="text-end">
          <a href="student_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "zta_app";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Accept either 'answer' or 'answers' (some forms used one or the other)
$postedAnswers = $_POST['answer'] ?? $_POST['answers'] ?? [];
$module_id = isset($_POST['module_id']) ? intval($_POST['module_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($module_id <= 0) {
    die("Invalid module.");
}

// Fetch all quiz questions for this module
$qStmt = $conn->prepare("SELECT quiz_id, question, correct_option FROM quizzes WHERE module_id = ?");
$qStmt->bind_param("i", $module_id);
$qStmt->execute();
$qRes = $qStmt->get_result();

$quizzes = [];
while ($q = $qRes->fetch_assoc()) {
    $quizzes[$q['quiz_id']] = [
        'question' => $q['question'],
        'correct'  => $q['correct_option']
    ];
}
$qStmt->close();

$totalQuestions = count($quizzes);
$totalCorrect = 0;
$feedback = [];

// If there are no quiz questions, show friendly message
if ($totalQuestions === 0) {
    // nothing to grade — redirect back or show message
    header("Location: view_module.php?id=" . $module_id . "&msg=no_quiz");
    exit;
}

// Insert each result (one row per question - keeps history of attempts)
foreach ($quizzes as $quiz_id => $qinfo) {
    // selected option from form, or null if not answered
    $selected = array_key_exists($quiz_id, $postedAnswers) ? $postedAnswers[$quiz_id] : null;

    $is_correct = ($selected !== null && $selected === $qinfo['correct']) ? 1 : 0;
    if ($is_correct) $totalCorrect++;

    // Save result. Handle NULL selected safely by inserting NULL explicitly when needed.
    if ($selected === null) {
        $ins = $conn->prepare("INSERT INTO quiz_results (user_id, quiz_id, selected_option, score) VALUES (?, ?, NULL, ?)");
        $ins->bind_param("iii", $user_id, $quiz_id, $is_correct);
    } else {
        $ins = $conn->prepare("INSERT INTO quiz_results (user_id, quiz_id, selected_option, score) VALUES (?, ?, ?, ?)");
        $ins->bind_param("iisi", $user_id, $quiz_id, $selected, $is_correct);
    }
    $ins->execute();
    $ins->close();

    $feedback[] = [
        'quiz_id' => $quiz_id,
        'question' => $qinfo['question'],
        'selected' => $selected,
        'correct' => $qinfo['correct'],
        'is_correct' => $is_correct
    ];
}

// Mark module as completed in progress table
$updateProgress = $conn->prepare("
    INSERT INTO progress (user_id, module_id, status, last_accessed) 
    VALUES (?, ?, 'completed', NOW())
    ON DUPLICATE KEY UPDATE status='completed', last_accessed=NOW()
");
$updateProgress->bind_param("ii", $user_id, $module_id);
$updateProgress->execute();
$updateProgress->close();

$percentage = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 2) : 0;
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Quiz Results | ZTA App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
    .result-card { border: none; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
    .result-header { background: linear-gradient(135deg,#f6c23e,#d39e00); color: white; padding: 20px; border-radius: 12px 12px 0 0; }
    .result-body { padding: 24px; background: white; }
    .result-footer { padding: 16px; background: #f1f3f5; border-top: 1px solid #dee2e6; display:flex; justify-content:space-between; }
    .correct { color: #0f5132; font-weight:600; }
    .wrong { color: #842029; font-weight:600; }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="result-card">
      <div class="result-header">
        <h3 class="mb-0">Quiz Results</h3>
      </div>
      <div class="result-body">
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="p-3 bg-light rounded">
              <strong>Total Questions</strong>
              <div class="fs-4"><?php echo $totalQuestions; ?></div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 bg-light rounded">
              <strong>Correct Answers</strong>
              <div class="fs-4"><?php echo $totalCorrect; ?></div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 bg-light rounded">
              <strong>Score</strong>
              <div class="fs-4"><?php echo $totalCorrect . " / " . $totalQuestions . " (" . $percentage . "%)"; ?></div>
            </div>
          </div>
        </div>

        <h5>Question Feedback</h5>
        <ul class="list-group">
          <?php foreach ($feedback as $f): ?>
            <li class="list-group-item">
              <div class="d-flex justify-content-between">
                <div>
                  <div class="fw-semibold">Q<?php echo $f['quiz_id']; ?>: <?php echo htmlspecialchars($f['question']); ?></div>
                  <small>Your answer: <?php echo $f['selected'] ?? '<em>Not answered</em>'; ?></small>
                  <br>
                  <small>Correct answer: <span class="fw-bold"><?php echo $f['correct']; ?></span></small>
                </div>
                <div class="text-end">
                  <?php if ($f['is_correct']): ?>
                    <span class="badge bg-success">Correct</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Wrong</span>
                  <?php endif; ?>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="result-footer">
        <div>
          <a href="view_module.php?id=<?php echo $module_id; ?>" class="btn btn-outline-secondary">⬅ Back to Module</a>
        </div>
        <div>
          <a href="student_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
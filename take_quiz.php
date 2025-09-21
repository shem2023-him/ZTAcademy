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

// Get module ID
if (!isset($_GET['module_id'])) {
    die("Module not found.");
}
$module_id = intval($_GET['module_id']);

// Fetch module title
$stmt = $conn->prepare("SELECT title FROM modules WHERE module_id = ?");
$stmt->bind_param("i", $module_id);
$stmt->execute();
$module_result = $stmt->get_result();
$module = $module_result->fetch_assoc();

// Fetch quiz questions
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE module_id = ?");
$stmt->bind_param("i", $module_id);
$stmt->execute();
$quiz_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quiz - <?php echo htmlspecialchars($module['title']); ?> | ZTA App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
    .quiz-card { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .quiz-header { background: linear-gradient(135deg, #1cc88a, #0d6e3e); color: white; padding: 20px; border-radius: 10px 10px 0 0; }
    .quiz-body { padding: 25px; background: white; }
    .quiz-actions { padding: 20px; background: #f1f3f5; border-top: 1px solid #dee2e6; display: flex; justify-content: space-between; }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="quiz-card">
      <!-- Quiz Header -->
      <div class="quiz-header">
        <h2 class="mb-0">Quiz: <?php echo htmlspecialchars($module['title']); ?></h2>
      </div>

      <!-- Quiz Form -->
      <form action="quiz_results.php" method="POST">
        <div class="quiz-body">
          <?php if ($quiz_result->num_rows > 0): ?>
            <?php while ($quiz = $quiz_result->fetch_assoc()): ?>
              <div class="mb-4">
                <h5><?php echo htmlspecialchars($quiz['question']); ?></h5>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="answer[<?php echo $quiz['quiz_id']; ?>]" value="A" required>
                  <label class="form-check-label"><?php echo htmlspecialchars($quiz['option_a']); ?></label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="answer[<?php echo $quiz['quiz_id']; ?>]" value="B">
                  <label class="form-check-label"><?php echo htmlspecialchars($quiz['option_b']); ?></label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="answer[<?php echo $quiz['quiz_id']; ?>]" value="C">
                  <label class="form-check-label"><?php echo htmlspecialchars($quiz['option_c']); ?></label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="answer[<?php echo $quiz['quiz_id']; ?>]" value="D">
                  <label class="form-check-label"><?php echo htmlspecialchars($quiz['option_d']); ?></label>
                </div>
              </div>
              <hr>
            <?php endwhile; ?>
          <?php else: ?>
            <p>No quiz available for this module yet.</p>
          <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="quiz-actions">
          <a href="view_module.php?id=<?php echo $module_id; ?>" class="btn btn-outline-secondary">⬅ Back to Module</a>
          <?php if ($quiz_result->num_rows > 0): ?>
            <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
            <button type="submit" class="btn btn-success">Submit Quiz ➡</button>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</body>
</html>

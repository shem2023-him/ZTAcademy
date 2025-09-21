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
  <title>Manage Quizzes | ZTA Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h3>Manage Quizzes</h3>
  <a href="admin_dashboard.php" class="btn btn-secondary mb-3">⬅ Back</a>

  <!-- Add Quiz -->
  <form method="POST" class="mb-4">
    <select name="module_id" class="form-select mb-2" required>
      <option value="">Select Module</option>
      <?php while ($m = $modules->fetch_assoc()): ?>
        <option value="<?= $m['module_id'] ?>"><?= htmlspecialchars($m['title']); ?></option>
      <?php endwhile; ?>
    </select>
    <input type="text" name="question" class="form-control mb-2" placeholder="Question" required>
    <input type="text" name="option_a" class="form-control mb-2" placeholder="Option A" required>
    <input type="text" name="option_b" class="form-control mb-2" placeholder="Option B" required>
    <input type="text" name="option_c" class="form-control mb-2" placeholder="Option C" required>
    <input type="text" name="option_d" class="form-control mb-2" placeholder="Option D" required>
    <select name="correct_option" class="form-select mb-2" required>
      <option value="A">Correct: A</option>
      <option value="B">Correct: B</option>
      <option value="C">Correct: C</option>
      <option value="D">Correct: D</option>
    </select>
    <button type="submit" name="add" class="btn btn-primary">Add Quiz</button>
  </form>

  <!-- List Quizzes -->
  <table class="table table-bordered">
    <thead><tr><th>ID</th><th>Module</th><th>Question</th><th>Correct</th><th>Actions</th></tr></thead>
    <tbody>
      <?php while ($q = $quizzes->fetch_assoc()): ?>
      <tr>
        <td><?= $q['quiz_id'] ?></td>
        <td><?= htmlspecialchars($q['title']) ?></td>
        <td><?= htmlspecialchars($q['question']) ?></td>
        <td><?= $q['correct_option'] ?></td>
        <td>
          <a href="manage_quizzes.php?delete=<?= $q['quiz_id'] ?>" class="btn btn-danger btn-sm">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>

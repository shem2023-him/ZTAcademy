<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$module_id = $_POST['module_id'] ?? null;
$answers = $_POST['answers'] ?? [];

if (!$module_id) {
    die("Module not specified");
}

// Fetch correct answers for this module
$stmt = $conn->prepare("SELECT quiz_id, correct_option FROM quizzes WHERE module_id = ?");
$stmt->bind_param("i", $module_id);
$stmt->execute();
$result = $stmt->get_result();

$correct_map = [];
while ($row = $result->fetch_assoc()) {
    $correct_map[$row['quiz_id']] = $row['correct_option'];
}

// Insert/update quiz results
foreach ($answers as $quiz_id => $selected_option) {
    $quiz_id = (int)$quiz_id;
    $selected_option = strtoupper($selected_option);
    $score = (isset($correct_map[$quiz_id]) && $correct_map[$quiz_id] === $selected_option) ? 1 : 0;

    // Check if an entry already exists
    $check = $conn->prepare("SELECT result_id FROM quiz_results WHERE user_id = ? AND quiz_id = ?");
    $check->bind_param("ii", $user_id, $quiz_id);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();

    if ($existing) {
        // Update existing record
        $update = $conn->prepare("UPDATE quiz_results SET selected_option = ?, score = ?, taken_at = CURRENT_TIMESTAMP WHERE result_id = ?");
        $update->bind_param("sii", $selected_option, $score, $existing['result_id']);
        $update->execute();
    } else {
        // Insert new record
        $insert = $conn->prepare("INSERT INTO quiz_results (user_id, quiz_id, selected_option, score, taken_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
        $insert->bind_param("iisi", $user_id, $quiz_id, $selected_option, $score);
        $insert->execute();
    }
}

// Redirect to results page
header("Location: quiz_results.php?module_id=" . $module_id);
exit();
?>

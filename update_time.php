<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "zta_app";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    exit;
}

$user_id = $_SESSION['user_id'];
$module_id = $_POST['module_id'] ?? 0;
$time_spent = $_POST['time_spent'] ?? 1;

if ($module_id > 0) {
    $time_sql = "
        INSERT INTO user_progress (user_id, module_id, status, time_spent_minutes, last_accessed) 
        VALUES (?, ?, 'in_progress', ?, NOW())
        ON DUPLICATE KEY UPDATE 
            time_spent_minutes = time_spent_minutes + ?,
            last_accessed = NOW(),
            status = IF(status != 'completed', 'in_progress', status)
    ";

    $stmt = $conn->prepare($time_sql);
    $stmt->bind_param("iiii", $user_id, $module_id, $time_spent, $time_spent);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
?>
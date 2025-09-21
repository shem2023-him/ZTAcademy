<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Fetch all glossary terms
$sql = "SELECT * FROM glossary ORDER BY term ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Glossary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">📘 Cybersecurity & Zero Trust Glossary</h2>
    <a href="student_dashboard.php" class="btn btn-secondary mb-3">⬅ Back to Dashboard</a>

    <input type="text" id="search" class="form-control mb-3" placeholder="Search terms...">

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Term</th>
                <th>Definition</th>
            </tr>
        </thead>
        <tbody id="glossaryTable">
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><b><?php echo htmlspecialchars($row['term']); ?></b></td>
                <td><?php echo htmlspecialchars($row['definition']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function(){
    $("#search").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#glossaryTable tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
</body>
</html>

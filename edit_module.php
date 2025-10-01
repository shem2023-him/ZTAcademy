<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Check if module ID is passed
if (!isset($_GET['id'])) {
    die("Module ID is missing.");
}

$module_id = intval($_GET['id']);
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $content = $conn->real_escape_string($_POST['content']);

    $update_sql = "UPDATE modules SET title='$title', description='$description', content='$content' WHERE module_id=$module_id";

    if ($conn->query($update_sql)) {
        $message = "<div class='alert alert-success'>Module updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating module: " . $conn->error . "</div>";
    }
}

// Fetch existing module data
$sql = "SELECT * FROM modules WHERE module_id=$module_id";
$result = $conn->query($sql);

if ($result->num_rows !== 1) {
    die("Module not found.");
}

$module = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Module | ZTAcademy Admin</title>
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
        .btn-save {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            border: none;
            color: white;
        }
        
        /* Edit Form */
        .edit-form-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            display: block;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .form-text {
            color: #6c757d;
            font-size: 0.85em;
            margin-top: 5px;
        }
        
        /* Alert Styling */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            border-left: 4px solid #198754;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        /* Form Groups */
        .form-group {
            margin-bottom: 25px;
        }
        .form-group:last-child {
            margin-bottom: 0;
        }
        
        /* Content Textarea */
        .content-textarea {
            font-family: 'Courier New', monospace;
            font-size: 0.95em;
            line-height: 1.5;
        }
        
        /* Character Count */
        .char-count {
            text-align: right;
            color: #6c757d;
            font-size: 0.85em;
            margin-top: 5px;
        }
        
        /* Module Preview */
        .preview-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 10px;
            border-left: 4px solid #0d6efd;
        }
        .preview-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
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
            .edit-form-container {
                padding: 25px;
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
        <a href="manage_modules.php" class="active">
            <i class="fas fa-book"></i>Manage Modules
        </a>
        <a href="manage_quizzes.php">
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
                <h1 class="h3 mb-2">Edit Learning Module</h1>
                <p class="text-muted mb-0">Update the module content and information for students.</p>
            </div>
            <div class="col-auto">
                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; font-size: 1.5em;">
                    <i class="fas fa-edit"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="manage_modules.php" class="btn btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Modules
        </a>
    </div>

    <!-- Messages -->
    <?php echo $message; ?>

    <!-- Edit Form -->
    <form method="post">
        <div class="edit-form-container">
            <!-- Module ID Display -->
            <div class="form-group">
                <span class="badge bg-primary fs-6">
                    <i class="fas fa-hashtag me-1"></i>Module ID: <?php echo $module_id; ?>
                </span>
            </div>

            <!-- Title Field -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-heading me-2 text-primary"></i>Module Title
                </label>
                <input type="text" name="title" class="form-control" 
                       value="<?php echo htmlspecialchars($module['title']); ?>" 
                       placeholder="Enter module title..." required>
                <div class="form-text">A clear, descriptive title for the learning module.</div>
            </div>

            <!-- Description Field -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-align-left me-2 text-primary"></i>Description
                </label>
                <textarea name="description" class="form-control" rows="4" 
                          placeholder="Briefly describe what students will learn..." required><?php echo htmlspecialchars($module['description']); ?></textarea>
                <div class="form-text">A concise overview of the module's learning objectives.</div>
            </div>

            <!-- Content Field -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-file-alt me-2 text-primary"></i>Learning Content
                </label>
                <textarea name="content" class="form-control content-textarea" rows="15" 
                          placeholder="Enter the main learning content here. You can use HTML formatting..." required><?php echo htmlspecialchars($module['content']); ?></textarea>
                <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    You can use HTML tags for formatting. This content will be displayed to students.
                </div>
            </div>

            <!-- Save Button -->
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-save btn-lg">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
    // Add character count for description
    document.addEventListener('DOMContentLoaded', function() {
        const descriptionField = document.querySelector('textarea[name="description"]');
        const contentField = document.querySelector('textarea[name="content"]');
        
        // Create character count elements
        const descCount = document.createElement('div');
        descCount.className = 'char-count';
        descriptionField.parentNode.appendChild(descCount);
        
        const contentCount = document.createElement('div');
        contentCount.className = 'char-count';
        contentField.parentNode.appendChild(contentCount);
        
        function updateCharCounts() {
            descCount.textContent = `${descriptionField.value.length} characters`;
            contentCount.textContent = `${contentField.value.length} characters`;
        }
        
        // Initial update
        updateCharCounts();
        
        // Update on input
        descriptionField.addEventListener('input', updateCharCounts);
        contentField.addEventListener('input', updateCharCounts);
    });
</script>
</body>
</html>
<?php $conn->close(); ?>
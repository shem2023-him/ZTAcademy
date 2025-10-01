<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'db_connect.php';

// Handle form submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $content = trim($_POST['content']);

    if ($title !== "" && $description !== "" && $content !== "") {
        $stmt = $conn->prepare("INSERT INTO modules (title, description, content) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $content);

        if ($stmt->execute()) {
            header("Location: manage_modules.php?success=1");
            exit();
        } else {
            $message = "❌ Error adding module: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "⚠️ All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add New Module | ZTAcademy Admin</title>
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
            border-left: 4px solid #198754;
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
        .btn-outline-back {
            border: 2px solid #6c757d;
            color: #6c757d;
            background: transparent;
        }
        .btn-outline-back:hover {
            background: #6c757d;
            color: white;
        }
        
        /* Create Form */
        .create-form-container {
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
            border-left: 4px solid;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
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
            font-family: 'Segoe UI', Tahoma, sans-serif;
            font-size: 1em;
            line-height: 1.6;
        }
        
        /* Character Count */
        .char-count {
            text-align: right;
            color: #6c757d;
            font-size: 0.85em;
            margin-top: 5px;
        }
        
        /* Form Header */
        .form-header {
            background: linear-gradient(135deg, #0d6efd 0%, #224abe 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 20px 25px;
            margin: -40px -40px 30px -40px;
        }
        .form-header h4 {
            margin: 0;
            font-weight: 600;
        }
        
        /* Tips Section */
        .tips-section {
            background: #e7f1ff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            border-left: 4px solid #0d6efd;
        }
        .tips-title {
            font-weight: 600;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        .tips-list {
            margin: 0;
            padding-left: 20px;
            color: #495057;
        }
        .tips-list li {
            margin-bottom: 8px;
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
            .create-form-container {
                padding: 25px;
            }
            .form-header {
                margin: -25px -25px 20px -25px;
                padding: 15px 20px;
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
                <h1 class="h3 mb-2">Create New Learning Module</h1>
                <p class="text-muted mb-0">Add a new educational module to the ZTAcademy curriculum.</p>
            </div>
            <div class="col-auto">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; font-size: 1.5em;">
                    <i class="fas fa-plus"></i>
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
    <?php if ($message): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Create Form -->
    <form method="POST" action="">
        <div class="create-form-container">
            <!-- Form Header -->
            <div class="form-header">
                <h4><i class="fas fa-book me-2"></i>Module Information</h4>
            </div>

            <!-- Title Field -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-heading me-2 text-primary"></i>Module Title
                </label>
                <input type="text" name="title" class="form-control" 
                       placeholder="Enter a clear, descriptive title for the module..." 
                       required>
                <div class="form-text">Choose a title that clearly describes the module's content and learning objectives.</div>
            </div>

            <!-- Description Field -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-align-left me-2 text-primary"></i>Description
                </label>
                <textarea name="description" class="form-control" rows="3" 
                          placeholder="Provide a brief overview of what students will learn in this module..." 
                          required></textarea>
                <div class="form-text">A concise summary that helps students understand the module's purpose and value.</div>
            </div>

            <!-- Content Field -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-file-alt me-2 text-primary"></i>Learning Content
                </label>
                <textarea name="content" class="form-control content-textarea" rows="12" 
                          placeholder="Enter the main educational content here. You can use HTML for formatting, images, and structured content..." 
                          required></textarea>
                <div class="form-text">
                    <i class="fas fa-lightbulb me-1"></i>
                    Use HTML tags for rich formatting. This content will be displayed to students in the learning interface.
                </div>
            </div>

            <!-- Tips Section -->
            <div class="tips-section">
                <div class="tips-title">
                    <i class="fas fa-lightbulb me-2"></i>Content Creation Tips
                </div>
                <ul class="tips-list">
                    <li>Start with clear learning objectives</li>
                    <li>Break content into logical sections with headings</li>
                    <li>Use examples and real-world scenarios</li>
                    <li>Include code snippets for technical concepts</li>
                    <li>Add images or diagrams to illustrate complex ideas</li>
                </ul>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-4 border-top">
                <a href="manage_modules.php" class="btn btn-outline-back">
                    <i class="fas fa-arrow-left me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-save btn-lg">
                    <i class="fas fa-save me-2"></i>Create Module
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
    // Add character count and form validation
    document.addEventListener('DOMContentLoaded', function() {
        const titleField = document.querySelector('input[name="title"]');
        const descriptionField = document.querySelector('textarea[name="description"]');
        const contentField = document.querySelector('textarea[name="content"]');
        
        // Create character count elements
        const titleCount = document.createElement('div');
        titleCount.className = 'char-count';
        titleField.parentNode.appendChild(titleCount);
        
        const descCount = document.createElement('div');
        descCount.className = 'char-count';
        descriptionField.parentNode.appendChild(descCount);
        
        const contentCount = document.createElement('div');
        contentCount.className = 'char-count';
        contentField.parentNode.appendChild(contentCount);
        
        function updateCharCounts() {
            titleCount.textContent = `${titleField.value.length} characters`;
            descCount.textContent = `${descriptionField.value.length} characters`;
            contentCount.textContent = `${contentField.value.length} characters`;
        }
        
        // Initial update
        updateCharCounts();
        
        // Update on input
        titleField.addEventListener('input', updateCharCounts);
        descriptionField.addEventListener('input', updateCharCounts);
        contentField.addEventListener('input', updateCharCounts);
        
        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            if (titleField.value.trim() === '' || 
                descriptionField.value.trim() === '' || 
                contentField.value.trim() === '') {
                e.preventDefault();
                alert('Please fill in all required fields before submitting.');
            }
        });
    });
</script>
</body>
</html>
<?php $conn->close(); ?>
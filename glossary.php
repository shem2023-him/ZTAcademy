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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Glossary | ZTAcademy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        
        /* Header */
        .dashboard-header {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #0d6efd;
        }
        
        /* Search Section */
        .search-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .search-box {
            position: relative;
        }
        .search-box .form-control {
            border-radius: 50px;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e9ecef;
            font-size: 1.1em;
            transition: all 0.3s ease;
        }
        .search-box .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .search-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.2em;
        }
        
        /* Glossary Cards */
        .glossary-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .glossary-card {
            background: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid #0d6efd;
            height: 100%;
        }
        .glossary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        .glossary-card .card-body {
            padding: 25px;
        }
        .term {
            color: #0d6efd;
            font-weight: 700;
            font-size: 1.2em;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        .definition {
            color: #495057;
            line-height: 1.6;
            margin: 0;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .empty-state i {
            font-size: 4em;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        /* Counter Badge */
        .term-count {
            background: linear-gradient(135deg, #0d6efd 0%, #224abe 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
        }
        
        /* Buttons */
        .btn-back {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* No Results State */
        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            display: none;
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
            .glossary-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .glossary-container {
                grid-template-columns: 1fr;
            }
        }
        
        /* Animation for search */
        .glossary-card {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
        <a href="glossary.php" class="active">
            <i class="fas fa-book"></i>Glossary
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
                <h1 class="h3 mb-2">Cybersecurity & Zero Trust Glossary 📚</h1>
                <p class="text-muted mb-0"><b>Explore essential terms and definitions for mastering Zero Trust Architecture.</b></p>
            </div>
            <div class="col-auto">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; font-size: 1.5em;">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="search-box">
                    <input type="text" id="search" class="form-control" placeholder="Search for terms or definitions...">
                    <div class="search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="term-count">
                    <i class="fas fa-list me-1"></i>
                    <?php echo $result->num_rows; ?> Terms
                </span>
            </div>
        </div>
    </div>

    <!-- No Results Message -->
    <div class="no-results" id="noResults">
        <i class="fas fa-search fa-3x mb-3"></i>
        <h4 class="text-muted">No matching terms found</h4>
        <p class="text-muted">Try searching with different keywords</p>
    </div>

    <!-- Glossary Cards -->
    <div class="glossary-container" id="glossaryContainer">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($term = $result->fetch_assoc()): ?>
                <div class="glossary-card" data-term="<?php echo strtolower(htmlspecialchars($term['term'])); ?>" 
                     data-definition="<?php echo strtolower(htmlspecialchars($term['definition'])); ?>">
                    <div class="card-body">
                        <div class="term">
                            <i class="fas fa-shield-alt me-2 text-primary"></i>
                            <?php echo htmlspecialchars($term['term']); ?>
                        </div>
                        <p class="definition">
                            <?php echo htmlspecialchars($term['definition']); ?>
                        </p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h4 class="text-muted">No Glossary Terms Available</h4>
                <p class="text-muted">Check back later for updated terminology.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="student_dashboard.php" class="btn btn-secondary btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<script>
$(document).ready(function(){
    // Search functionality
    $("#search").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        var visibleCards = 0;
        
        $(".glossary-card").each(function() {
            var term = $(this).data('term');
            var definition = $(this).data('definition');
            var searchText = term + ' ' + definition;
            
            if (searchText.indexOf(value) > -1) {
                $(this).show();
                visibleCards++;
            } else {
                $(this).hide();
            }
        });
        
        // Show/hide no results message
        if (visibleCards === 0 && value !== '') {
            $("#noResults").show();
        } else {
            $("#noResults").hide();
        }
    });
    
    // Focus on search input
    $("#search").focus();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
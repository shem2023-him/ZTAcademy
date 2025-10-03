<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZTA Resources | ZTAcademy</title>
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
        
        /* Resources Container */
        .resources-container {
            background: white;
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .resources-header {
            background: linear-gradient(135deg, #0d6efd 0%, #224abe 100%);
            color: white;
            padding: 25px 30px;
            border-bottom: none;
        }
        .resources-header h2 {
            margin: 0;
            font-weight: 700;
        }
        
        /* Resources Body */
        .resources-body {
            padding: 40px;
        }
        
        /* Section Styling */
        .resource-section {
            margin-bottom: 40px;
        }
        .resource-section:last-child {
            margin-bottom: 0;
        }
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        .section-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5em;
        }
        .infographics-icon {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }
        .videos-icon {
            background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
            color: white;
        }
        .reading-icon {
            background: linear-gradient(135deg, #45b7d1 0%, #96c93d 100%);
            color: white;
        }
        .section-title {
            color: #2c3e50;
            font-weight: 700;
            margin: 0;
        }
        
        /* Image Gallery */
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .resource-image {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .resource-image:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        .resource-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .resource-image:hover img {
            transform: scale(1.05);
        }
        .image-caption {
            padding: 15px;
            background: #f8f9fa;
            text-align: center;
            font-weight: 600;
            color: #495057;
        }
        
        /* Video Gallery */
        .video-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
        }
        .video-item {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .video-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        .video-caption {
            padding: 15px;
            background: #f8f9fa;
            text-align: center;
            font-weight: 600;
            color: #495057;
        }
        
        /* Reading List */
        .reading-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .reading-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #0d6efd;
            transition: all 0.3s ease;
        }
        .reading-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        .reading-item:last-child {
            margin-bottom: 0;
        }
        .reading-link {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
            transition: color 0.3s ease;
        }
        .reading-link:hover {
            color: #0d6efd;
        }
        .reading-source {
            color: #6c757d;
            font-size: 0.9em;
        }
        .external-link {
            color: #0d6efd;
            margin-left: 5px;
            font-size: 0.8em;
        }

        /* Diagram Content */
        .diagram-content {
            height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 12px 12px 0 0;
        }
        
        /* Action Buttons */
        .action-buttons {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
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
            .resources-body {
                padding: 25px;
            }
            .image-gallery,
            .video-gallery {
                grid-template-columns: 1fr;
            }
            .section-header {
                flex-direction: column;
                text-align: center;
            }
            .section-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
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
        <a href="resources.php" class="active">
            <i class="fas fa-folder-open"></i>Resources
        </a>
        <a href="glossary.php">
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
                <h1 class="h3 mb-2">Zero Trust Architecture Resources</h1>
                <p class="text-muted mb-0">Explore additional learning materials, videos, and reference documents.</p>
            </div>
            <div class="col-auto">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; font-size: 1.5em;">
                    <i class="fas fa-folder-open"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Resources Container -->
    <div class="resources-container">
        <div class="resources-header">
            <h2><i class="fas fa-folder-open me-2"></i>Learning Resources Library</h2>
        </div>
        
        <div class="resources-body">
            <!-- Enhanced Infographics Section -->
            <div class="resource-section">
                <div class="section-header">
                    <div class="section-icon infographics-icon">
                        <i class="fas fa-diagram-project"></i>
                    </div>
                    <h3 class="section-title">ZTA Architecture & Framework Diagrams</h3>
                </div>
                
                <div class="image-gallery">
                    <!-- Zero Trust Principles -->
                    <div class="resource-image">
                        <div class="diagram-content bg-primary bg-opacity-10 p-4 text-center">
                            <i class="fas fa-ban fa-2x text-danger mb-3"></i>
                            <h6 class="text-primary">Never Trust, Always Verify</h6>
                            <p class="small text-muted mb-0">Core Zero Trust Principle</p>
                            <div class="mt-2">
                                <span class="badge bg-primary me-1">Verify Explicitly</span>
                                <span class="badge bg-success me-1">Least Privilege</span>
                                <span class="badge bg-warning">Assume Breach</span>
                            </div>
                        </div>
                        <div class="image-caption">
                            <i class="fas fa-bullseye me-2"></i>Core ZTA Principles
                        </div>
                    </div>
                    
                    <!-- ZTA Components -->
                    <div class="resource-image">
                        <div class="diagram-content bg-success bg-opacity-10 p-4 text-center">
                            <i class="fas fa-puzzle-piece fa-2x text-success mb-3"></i>
                            <h6 class="text-success">ZTA Key Components</h6>
                            <div class="row small text-muted">
                                <div class="col-6">Identity</div>
                                <div class="col-6">Devices</div>
                                <div class="col-6">Networks</div>
                                <div class="col-6">Data</div>
                            </div>
                        </div>
                        <div class="image-caption">
                            <i class="fas fa-cubes me-2"></i>Architecture Components
                        </div>
                    </div>
                    
                    <!-- Implementation Flow -->
                    <div class="resource-image">
                        <div class="diagram-content bg-info bg-opacity-10 p-4 text-center">
                            <i class="fas fa-project-diagram fa-2x text-info mb-3"></i>
                            <h6 class="text-info">Implementation Flow</h6>
                            <div class="small text-muted">
                                <div>1. Identify</div>
                                <div>2. Protect</div>
                                <div>3. Detect</div>
                                <div>4. Respond</div>
                            </div>
                        </div>
                        <div class="image-caption">
                            <i class="fas fa-forward me-2"></i>Implementation Steps
                        </div>
                    </div>
                    
                    <!-- MFA Process -->
                    <div class="resource-image">
                        <div class="diagram-content bg-warning bg-opacity-10 p-4 text-center">
                            <i class="fas fa-fingerprint fa-2x text-warning mb-3"></i>
                            <h6 class="text-warning">MFA Authentication</h6>
                            <p class="small text-muted mb-0">Multi-Factor Authentication Flow</p>
                            <div class="mt-2">
                                <span class="badge bg-primary me-1">Password</span>
                                <span class="badge bg-success me-1">Device</span>
                                <span class="badge bg-warning">Biometric</span>
                            </div>
                        </div>
                        <div class="image-caption">
                            <i class="fas fa-user-shield me-2"></i>Authentication Flow
                        </div>
                    </div>
                </div>

                <!-- External Resources Links -->
                <div class="mt-4 p-4 bg-light rounded">
                    <h6 class="mb-3"><i class="fas fa-external-link-alt me-2"></i>Official ZTA Diagram Sources</h6>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <a href="https://csrc.nist.gov/publications/detail/sp/800-207/final" 
                               target="_blank" class="text-decoration-none">
                                <div class="p-3 bg-white rounded border">
                                    <i class="fas fa-university text-primary me-2"></i>
                                    <strong>NIST SP 800-207</strong>
                                    <small class="d-block text-muted">Official ZTA Standard</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="https://www.cisa.gov/zero-trust-maturity-model" 
                               target="_blank" class="text-decoration-none">
                                <div class="p-3 bg-white rounded border">
                                    <i class="fas fa-shield-alt text-success me-2"></i>
                                    <strong>CISA ZT Maturity</strong>
                                    <small class="d-block text-muted">Implementation Guide</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="https://learn.microsoft.com/en-us/security/zero-trust/" 
                               target="_blank" class="text-decoration-none">
                                <div class="p-3 bg-white rounded border">
                                    <i class="fab fa-microsoft text-primary me-2"></i>
                                    <strong>Microsoft ZT</strong>
                                    <small class="d-block text-muted">Framework & Diagrams</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Videos Section -->
            <div class="resource-section">
                <div class="section-header">
                    <div class="section-icon videos-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3 class="section-title">Video Tutorials & Explanations</h3>
                </div>
                
                <div class="video-gallery">
                    <!-- Microsoft Zero Trust -->
                    <div class="video-item">
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.youtube.com/embed/2VqS_6xJL8s" 
                                    title="What is Zero Trust Architecture? | Microsoft Security"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-caption">What is Zero Trust Architecture? | Microsoft</div>
                    </div>
                    
                    <!-- NIST Zero Trust -->
                    <div class="video-item">
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.youtube.com/embed/7p6zkMyyxCk" 
                                    title="NIST Zero Trust Architecture Overview"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-caption">NIST Zero Trust Architecture Overview</div>
                    </div>
                    
                    <!-- Zero Trust Implementation -->
                    <div class="video-item">
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.youtube.com/embed/YR_KQ0pB-0c" 
                                    title="Implementing Zero Trust Architecture"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-caption">Implementing Zero Trust Architecture</div>
                    </div>
                    
                    <!-- Cloud Security -->
                    <div class="video-item">
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.youtube.com/embed/3-Um0X5uZrY" 
                                    title="Zero Trust in Cloud Security"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-caption">Zero Trust in Cloud Security</div>
                    </div>
                </div>
            </div>

            <!-- Reading Section -->
            <div class="resource-section">
                <div class="section-header">
                    <div class="section-icon reading-icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <h3 class="section-title">Further Reading & References</h3>
                </div>
                
                <ul class="reading-list">
                    <li class="reading-item">
                        <a href="https://csrc.nist.gov/publications/detail/sp/800-207/final" 
                           target="_blank" class="reading-link">
                            NIST SP 800-207: Zero Trust Architecture
                            <i class="fas fa-external-link-alt external-link"></i>
                        </a>
                        <div class="reading-source">National Institute of Standards and Technology</div>
                    </li>
                    <li class="reading-item">
                        <a href="https://learn.microsoft.com/en-us/security/zero-trust/" 
                           target="_blank" class="reading-link">
                            Microsoft Zero Trust Implementation Guide
                            <i class="fas fa-external-link-alt external-link"></i>
                        </a>
                        <div class="reading-source">Microsoft Security</div>
                    </li>
                    <li class="reading-item">
                        <a href="https://www.paloaltonetworks.com/zero-trust" 
                           target="_blank" class="reading-link">
                            Palo Alto Networks Zero Trust Hub
                            <i class="fas fa-external-link-alt external-link"></i>
                        </a>
                        <div class="reading-source">Palo Alto Networks</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="student_dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
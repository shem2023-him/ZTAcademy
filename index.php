<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'student') {
        header("Location: student_dashboard.php");
        exit;
    }
}

// Database configuration
$host     = "localhost";
$dbUser   = "root";
$dbPass   = "";
$dbName   = "zta_app";

// Database connection
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = "";

// ==================== SIGNUP PROCESSING ====================
if (isset($_POST['signup'])) {
    $username = trim($_POST['signup_username'] ?? '');
    $email    = trim($_POST['signup_email'] ?? '');
    $pwd_raw  = $_POST['signup_password'] ?? '';

    // Validate input
    if (empty($username) || empty($email) || empty($pwd_raw)) {
        $message = "Please complete all signup fields.";
    } else {
        // Check for existing user
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $message = "Username or email already taken.";
        } else {
            // Create new user
            $hash = password_hash($pwd_raw, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'student')");
            $insert->bind_param("sss", $username, $email, $hash);
            
            if ($insert->execute()) {
                $message = "Signup successful — please sign in.";
            } else {
                $message = "Signup error: " . $insert->error;
            }
            $insert->close();
        }
        $check->close();
    }
}

// ==================== LOGIN PROCESSING ====================
if (isset($_POST['login'])) {
    $login_id = trim($_POST['login_username'] ?? '');
    $password = $_POST['login_password'] ?? '';

    // Validate input
    if (empty($login_id) || empty($password)) {
        $message = "Enter username (or email) and password.";
    } else {
        // Verify user credentials
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, role FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $login_id, $login_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id']  = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                    exit;
                } else {
                    header("Location: student_dashboard.php");
                    exit;
                }
            } else {
                $message = "Invalid password.";
            }
        } else {
            $message = "User not found.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZTAcademy — Master Zero Trust Architecture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Variables */
        :root {
            --primary: #1a56db;
            --primary-dark: #1e429f;
            --secondary: #10b981;
            --dark: #1f2937;
            --light: #f9fafb;
            --gray: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }
        
        /* Global Styles */
        body { 
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: var(--light); 
            line-height: 1.6;
            color: var(--dark);
        }
        
        /* Navigation */
        .navbar { 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 15px 0;
            transition: var(--transition);
        }
        
        .navbar-brand { 
            font-weight: 700; 
            font-size: 1.6rem; 
            color: var(--primary) !important;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 8px;
            font-size: 1.8rem;
        }
        
        .nav-link {
            font-weight: 500;
            margin: 0 10px;
            position: relative;
            transition: var(--transition);
        }
        
        .nav-link:hover {
            color: var(--primary) !important;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 160px 0 100px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,208C1248,224,1344,192,1392,176L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: bottom;
            opacity: 0.1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .hero p {
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto 2rem;
            opacity: 0.9;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 1.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Forms */
        .form-control {
            border-radius: 8px;
            border: 1px solid var(--border);
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.1);
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(26, 86, 219, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--secondary) 0%, #059669 100%);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }
        
        .btn-light {
            background: white;
            color: var(--primary);
        }
        
        .btn-light:hover {
            background: var(--light);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        /* Alert */
        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: var(--shadow);
        }
        
        /* Features Section */
        .features {
            background: white;
            padding: 80px 0;
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            background: rgba(26, 86, 219, 0.1);
            color: var(--primary);
            font-size: 1.8rem;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px 20px;
            border-radius: 12px;
            transition: var(--transition);
            height: 100%;
        }
        
        .feature-card:hover {
            background: var(--light);
            transform: translateY(-5px);
        }
        
        /* Footer */
        footer {
            background: var(--dark);
            color: #d1d5db;
            padding: 60px 0 30px;
            margin-top: 80px;
        }
        
        footer a { 
            color: #f9fafb; 
            text-decoration: none; 
            transition: var(--transition);
        }
        
        footer a:hover { 
            color: var(--primary); 
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .footer-links a {
            margin: 0 15px;
            font-weight: 500;
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .social-icons a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 8px;
            transition: var(--transition);
        }
        
        .social-icons a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        /* Section spacing */
        section {
            padding: 80px 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero {
                padding: 140px 0 80px;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            section {
                padding: 60px 0;
            }
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-shield-alt"></i>
            <strong>ZTAcademy</strong>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="#about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#features">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact">Contact</a>
                </li>
                <li class="nav-item ms-2">
                    <a class="btn btn-primary" href="#signup">Get Started</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="display-4 fw-bold">
                Master Zero Trust <span class="text-warning">Architecture</span>
            </h1>
            <p class="lead">
                Build expertise in modern cybersecurity with comprehensive, hands-on learning designed by industry experts.
            </p>
            <a href="#signup" class="btn btn-light btn-lg px-4 py-2 fw-semibold">
                Start Learning Today <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features" id="features">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="fw-bold mb-3">Why Choose ZTAcademy?</h2>
                <p class="text-muted fs-5">Our platform offers everything you need to master Zero Trust Architecture</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h4>Hands-on Labs</h4>
                    <p class="text-muted">Practice with real-world scenarios in our secure sandbox environment.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h4>Expert Instructors</h4>
                    <p class="text-muted">Learn from industry professionals with years of cybersecurity experience.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h4>Industry Certification</h4>
                    <p class="text-muted">Earn recognized certificates to advance your cybersecurity career.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="container mt-5 pt-5">
    <!-- Alert Message -->
    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Authentication Forms -->
    <div class="row g-4 justify-content-center">
        <!-- Sign In -->
        <div class="col-lg-5 col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h4 class="mb-0 text-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Username or Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-user text-muted"></i>
                                </span>
                                <input type="text" name="login_username" class="form-control border-start-0" placeholder="Enter username or email" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="login_password" class="form-control border-start-0" placeholder="Enter password" required>
                            </div>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sign Up -->
        <div class="col-lg-5 col-md-6" id="signup">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h4 class="mb-0 text-success">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-user text-muted"></i>
                                </span>
                                <input type="text" name="signup_username" class="form-control border-start-0" placeholder="Choose a username" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email" name="signup_email" class="form-control border-start-0" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="signup_password" class="form-control border-start-0" placeholder="Create a password" required>
                            </div>
                        </div>
                        <button type="submit" name="signup" class="btn btn-success w-100 py-2 fw-semibold">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <section class="mt-5" id="about">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h3 class="fw-bold mb-4">About ZTAcademy</h3>
                <p class="fs-5 text-muted">
                    ZTAcademy helps students and professionals build expertise in Zero Trust Architecture 
                    with structured learning modules, hands-on labs, and comprehensive assessments designed 
                    by industry experts. Our mission is to bridge the cybersecurity skills gap by providing 
                    accessible, high-quality education in Zero Trust principles and implementation.
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="mb-5" id="contact">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h3 class="fw-bold mb-4">Contact Us</h3>
                <p class="fs-5">
                    Have questions? Reach out to our team at
                    <a href="mailto:support@ztacademy.com" class="text-decoration-none fw-semibold">
                        sddungu@kabarak.ac.ke
                    </a>
                </p>
            </div>
        </div>
    </section>
</div>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="social-icons">
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-github"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
        <div class="footer-links">
            <a href="#about">About</a>
            <a href="#features">Features</a>
            <a href="#contact">Contact</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
        <div class="text-center mt-4">
            <p class="mb-2">&copy; <?php echo date("Y"); ?> ZTAcademy. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
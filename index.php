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
    <style>
        /* Global Styles */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f8f9fa; 
            line-height: 1.6;
        }
        
        /* Navigation */
        .navbar { 
            background: #ffffff; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand { 
            font-weight: 700; 
            font-size: 1.5rem; 
            color: #0d6efd !important;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #0d6efd 0%, #224abe 100%);
            color: #ffffff;
            padding: 120px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.25);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .hero h1 {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 1rem;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Forms */
        .form-control {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #224abe 100%);
            border: none;
        }
        
        /* Alert */
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        /* Footer */
        footer {
            background: #212529;
            color: #adb5bd;
            padding: 40px 0 20px;
            margin-top: 80px;
        }
        footer a { 
            color: #f8f9fa; 
            text-decoration: none; 
            transition: color 0.2s ease;
        }
        footer a:hover { 
            color: #0d6efd; 
            text-decoration: none;
        }
        
        /* Section spacing */
        section {
            padding: 60px 0;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
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
                    <a class="nav-link" href="#contact">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary ms-2" href="#signup">Get Started</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1 class="display-4 fw-bold">
            Master Zero Trust <span class="text-warning">Architecture</span>
        </h1>
        <p class="lead fs-4">
            Build expertise in modern cybersecurity with comprehensive, hands-on learning designed by industry experts.
        </p>
        <a href="#signup" class="btn btn-light btn-lg mt-3 px-4 py-2 fw-semibold">
            Start Learning Today ➜
        </a>
    </div>
</section>

<!-- Main Content -->
<div class="container mt-5 pt-5">
    <!-- Alert Message -->
    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Authentication Forms -->
    <div class="row g-4 justify-content-center">
        <!-- Sign In -->
        <div class="col-lg-5 col-md-6">
            <div class="card p-4 h-100">
                <h4 class="mb-4 text-primary">Sign In</h4>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username or Email</label>
                        <input type="text" name="login_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="login_password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-semibold">
                        Sign In
                    </button>
                </form>
            </div>
        </div>

        <!-- Sign Up -->
        <div class="col-lg-5 col-md-6" id="signup">
            <div class="card p-4 h-100">
                <h4 class="mb-4 text-success">Sign Up</h4>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="signup_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="signup_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="signup_password" class="form-control" required>
                    </div>
                    <button type="submit" name="signup" class="btn btn-success w-100 py-2 fw-semibold">
                        Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <section class="mt-5" id="about">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h3 class="mb-4">About ZTAcademy</h3>
                <p class="fs-5 text-muted">
                    ZTAcademy helps students and professionals build expertise in Zero Trust Architecture 
                    with structured learning modules, hands-on labs, and comprehensive assessments designed 
                    by industry experts.
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="mb-5" id="contact">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h3 class="mb-4">Contact Us</h3>
                <p class="fs-5">
                    Email: 
                    <a href="mailto:support@ztacademy.com" class="text-decoration-none">
                        sddungu@kabarak.ac.ke
                    </a>
                </p>
            </div>
        </div>
    </section>
</div>

<!-- Footer -->
<footer>
    <div class="container text-center">
        <p class="mb-2">&copy; <?php echo date("Y"); ?> ZTAcademy. All rights reserved.</p>
        <p class="mb-0">
            <a href="#about" class="mx-2">About</a> | 
            <a href="#contact" class="mx-2">Contact</a>
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
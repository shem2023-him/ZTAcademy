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

$host = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "zta_app";

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

$message = "";

/* ------------------ SIGNUP ------------------ */
if (isset($_POST['signup'])) {
    $username = trim($_POST['signup_username'] ?? '');
    $email    = trim($_POST['signup_email'] ?? '');
    $pwd_raw  = $_POST['signup_password'] ?? '';

    if ($username === '' || $email === '' || $pwd_raw === '') {
        $message = "Please complete all signup fields.";
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $message = "Username or email already taken.";
        } else {
            $hash = password_hash($pwd_raw, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'student')");
            $ins->bind_param("sss", $username, $email, $hash);
            if ($ins->execute()) {
                $message = "Signup successful — please sign in.";
            } else {
                $message = "Signup error: " . $ins->error;
            }
            $ins->close();
        }
        $check->close();
    }
}

/* ------------------ LOGIN ------------------ */
if (isset($_POST['login'])) {
    $login_id = trim($_POST['login_username'] ?? '');
    $pwd      = $_POST['login_password'] ?? '';

    if ($login_id === '' || $pwd === '') {
        $message = "Enter username (or email) and password.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, role FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $login_id, $login_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (password_verify($pwd, $row['password_hash'])) {
                $_SESSION['user_id']  = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role']     = $row['role'];

                if ($row['role'] === 'admin') {
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
  <title>ZTAcademy — Master Zero Trust</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f8f9fa; }
    /* Hero */
    .hero {
      background: linear-gradient(135deg, #0d6efd, #224abe);
      color: white;
      padding: 100px 20px;
      text-align: center;
      position: relative;
    }
    .hero::after {
      content: '';
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,0.25); /* subtle overlay */
    }
    .hero > .content {
      position: relative;
      z-index: 1;
    }
    /* Navbar */
    .navbar { background: #fff; }
    .navbar-brand { font-weight: bold; font-size: 1.5rem; }
    /* Footer */
    footer {
      background: #212529;
      color: #adb5bd;
      padding: 40px 0 20px;
      margin-top: 60px;
    }
    footer a { color: #f8f9fa; text-decoration: none; }
    footer a:hover { text-decoration: underline; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg shadow-sm fixed-top">
  <div class="container">
    <a class="navbar-brand" href="#">ZTAcademy</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
        <li class="nav-item"><a class="btn btn-primary ms-2" href="#signup">Get Started</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<section class="hero">
  <div class="content">
    <h1 class="display-4 fw-bold">Master Zero Trust <span class="text-warning">Architecture</span></h1>
    <p class="lead">Build expertise in modern cybersecurity with comprehensive, hands-on learning designed by industry experts.</p>
    <a href="#signup" class="btn btn-light btn-lg mt-3">Start Learning Today ➜</a>
  </div>
</section>

<div class="container mt-5">

  <?php if ($message): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Sign In -->
    <div class="col-md-6">
      <div class="card p-4">
        <h4 class="mb-3">Sign In</h4>
        <form method="POST" action="">
          <div class="mb-3">
            <label class="form-label">Username or Email</label>
            <input type="text" name="login_username" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="login_password" class="form-control" required>
          </div>
          <button type="submit" name="login" class="btn btn-primary w-100">Sign In</button>
        </form>
      </div>
    </div>

    <!-- Sign Up -->
    <div class="col-md-6" id="signup">
      <div class="card p-4">
        <h4 class="mb-3">Sign Up</h4>
        <form method="POST" action="">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="signup_username" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="signup_email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="signup_password" class="form-control" required>
          </div>
          <button type="submit" name="signup" class="btn btn-success w-100">Create Account</button>
        </form>
      </div>
    </div>
  </div>

  <!-- About -->
  <section class="mt-5 mb-5" id="about">
    <h3>About ZTAcademy</h3>
    <p>ZTAcademy helps students and professionals build expertise in Zero Trust Architecture with structured learning modules and quizzes.</p>
  </section>

  <!-- Contact -->
  <section class="mb-5" id="contact">
    <h3>Contact</h3>
    <p>Email: <a href="mailto:support@ztacademy.com">sddungu@kabarak.ac.ke</a></p>
  </section>
</div>

<!-- Footer -->
<footer>
  <div class="container text-center">
    <p>&copy; <?php echo date("Y"); ?> ZTAcademy. All rights reserved.</p>
    <p>
      <a href="#about">About</a> | 
      <a href="#contact">Contact</a>
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

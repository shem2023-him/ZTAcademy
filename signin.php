<?php
// --- Temporary test handler ---
// When you submit, this will just echo the form input
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    echo "<p style='color:green; text-align:center;'>Welcome, $email (password hidden)</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In - ZTA Academy</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="navbar">
    <div class="logo">🔒 ZTA Academy</div>
    <nav>
      <a href="modules.php">Modules</a>
      <a href="#">About</a>
      <a href="#">Contact</a>
    </nav>
    <div class="auth-buttons">
      <a href="signin.php" class="btn-outline">Sign In</a>
      <a href="signup.php" class="btn-primary">Get Started</a>
    </div>
  </header>

  <section class="form-container">
    <h2>Sign In</h2>
    <form method="POST" action="">
      <label>Email</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <button type="submit" class="btn-primary">Sign In</button>
    </form>
    <p class="small-text">Don’t have an account? <a href="signup.php">Sign up here</a></p>
  </section>
</body>
</html>

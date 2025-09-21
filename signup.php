<?php
// --- Temporary form handler ---
// Later we will replace this with DB insert logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    echo "<p style='color:green; text-align:center;'>Account created for $name ($email)</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - ZTA Academy</title>
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
    <h2>Create an Account</h2>
    <form method="POST" action="">
      <label>Full Name</label>
      <input type="text" name="name" required>

      <label>Email</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <label>Confirm Password</label>
      <input type="password" name="confirm_password" required>

      <button type="submit" class="btn-primary">Sign Up</button>
    </form>
    <p class="small-text">Already have an account? <a href="signin.php">Sign in here</a></p>
  </section>
</body>
</html>

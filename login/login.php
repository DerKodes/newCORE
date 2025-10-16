<?php
session_start();

// Hardcoded employee accounts (email => password)
$employees = [
  "employee1@company.com" => "password123",
  "employee2@company.com" => "mypassword",
  "manager@company.com"   => "admin123"
];

$Email = $password = "";
$errorMsg = "";

$maxAttempts = 4;
$lockoutTime = 60; // seconds

// Get client IP
$clientIP = $_SERVER['REMOTE_ADDR'];

// Initialize tracking arrays in session
if (!isset($_SESSION['login_attempts'])) {
  $_SESSION['login_attempts'] = [];
  $_SESSION['last_attempt_time'] = [];
}

// Helper function to get attempt key (email + IP)
function attemptKey($email, $ip)
{
  return md5($email . '|' . $ip);
}

// Check if locked out
$remaining = 0;
if (!empty($_POST['Email'])) {
  $key = attemptKey($_POST['Email'], $clientIP);

  if (!isset($_SESSION['login_attempts'][$key])) $_SESSION['login_attempts'][$key] = 0;
  if (!isset($_SESSION['last_attempt_time'][$key])) $_SESSION['last_attempt_time'][$key] = 0;

  if ($_SESSION['login_attempts'][$key] >= $maxAttempts) {
    $elapsed = time() - $_SESSION['last_attempt_time'][$key];
    if ($elapsed < $lockoutTime) {
      $remaining = $lockoutTime - $elapsed;
      $errorMsg = "⛔ Too many failed attempts. Please wait {$remaining} seconds.";
    } else {
      $_SESSION['login_attempts'][$key] = 0; // reset after cooldown
    }
  }
}

// Process login if not locked out
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($errorMsg)) {
  $Email = trim($_POST["Email"]);
  $password = trim($_POST["password"]);

  if (empty($Email) || empty($password)) {
    $errorMsg = "Please enter both email and password.";
  } else {
    $key = attemptKey($Email, $clientIP);

    if (array_key_exists($Email, $employees) && $employees[$Email] === $password) {
      // Successful login: reset attempts
      $_SESSION['login_attempts'][$key] = 0;
      $_SESSION['last_attempt_time'][$key] = 0;

      session_regenerate_id(true);
      $_SESSION['Email'] = $Email;
      $_SESSION['username'] = explode("@", $Email)[0];
      $_SESSION['login_success'] = true;

      header("Location: ../public/dashboard.php");
      exit();
    } else {
      // Failed login: increment attempts
      $_SESSION['login_attempts'][$key]++;
      $_SESSION['last_attempt_time'][$key] = time();
      $errorMsg = "Invalid email or password.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/login.css">
  <title>Login - SLATE System</title>
</head>

<body>
  <div class="main-container">
    <div class="login-container">
      <div class="welcome-panel">
        <h1>FREIGHT MANAGEMENT SYSTEM</h1>
      </div>

      <div class="login-panel">
        <div class="login-box">
          <img src="../assets/slate.png" alt="SLATE Logo">
          <h2>SLATE Login</h2>

          <?php if (!empty($errorMsg)): ?>
            <div class="error-alert"><?= $errorMsg ?></div>
          <?php endif; ?>

          <form action="login.php" method="POST">
            <input type="text" name="Email" placeholder="Email" value="<?= htmlspecialchars($Email) ?>">
            <input type="password" name="password" placeholder="Password">
            <button type="submit">Log In</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <footer>
    &copy; <span id="currentYear"></span> SLATE Freight Management System. All rights reserved.
  </footer>

  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.getElementById('currentYear').textContent = new Date().getFullYear();

    // Keep your SweetAlert login/logout toasts intact
    <?php if (isset($_SESSION['logout_success']) && $_SESSION['logout_success']): ?>
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'You have been logged out 👋',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
      <?php unset($_SESSION['logout_success']); ?>
    <?php endif; ?>
  </script>

  <?php if (!empty($errorMsg) && $remaining > 0): ?>
    <script>
      let remaining = <?= $remaining ?>;
      const alertBox = document.querySelector(".error-alert");

      const countdown = setInterval(() => {
        if (remaining <= 0) {
          clearInterval(countdown);
          location.reload(); // allow login again
        } else {
          if (alertBox) {
            alertBox.textContent = "⛔ Too many failed attempts. Please wait " + remaining + " seconds.";
          }
          remaining--;
        }
      }, 1000);
    </script>
  <?php endif; ?>
</body>

</html>
<?php
session_start();

$correctPassword = "myvaultpassword"; // Change this to something secure and hashed later

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputPassword = $_POST['password'];
    if ($inputPassword === $correctPassword) {
        $_SESSION['authenticated'] = true;
    } else {
        echo "<script>alert('Wrong password');window.location='index.html';</script>";
        exit;
    }
}

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: index.html");
    exit;
}

$secretsFile = 'vault.json';
$secrets = file_exists($secretsFile) ? json_decode(file_get_contents($secretsFile), true) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>🔐 KeyRing Vault</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="card">
    <h2>🧾 Stored TOTP Secrets</h2>
    <?php foreach ($secrets as $label => $secret): ?>
      <div class="totp-entry" data-secret="<?= htmlspecialchars($secret) ?>">
        <strong><?= htmlspecialchars($label) ?></strong>
        <div class="code">Loading...</div>
        <div class="timer"></div>
      </div>
    <?php endforeach; ?>
    <hr>
    <form method="POST" action="save_secret.php">
      <input type="text" name="label" placeholder="Label (e.g. Gmail)" required>
      <input type="text" name="secret" placeholder="TOTP Secret" required>
      <button type="submit">➕ Add Secret</button>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/otplib@11.0.1/otplib-browser.min.js"></script>
  <script>
    document.querySelectorAll('.totp-entry').forEach(function(entry) {
      const secret = entry.getAttribute('data-secret');
      const codeDiv = entry.querySelector('.code');
      const timerDiv = entry.querySelector('.timer');
      function updateCode() {
        try {
          const code = window.otplib.authenticator.generate(secret);
          codeDiv.textContent = code;
        } catch (e) {
          codeDiv.textContent = 'Invalid secret';
        }
        const epoch = Math.floor(Date.now() / 1000);
        const rem = 30 - (epoch % 30);
        timerDiv.textContent = rem + 's';
      }
      updateCode();
      setInterval(updateCode, 1000);
    });
  </script>
</body>
</html>

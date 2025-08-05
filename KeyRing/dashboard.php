<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}
if (!isset($_SESSION['LAST_ACTIVITY'])) $_SESSION['LAST_ACTIVITY'] = time();
if (time() - $_SESSION['LAST_ACTIVITY'] > 600) { // 10 min
    session_unset(); session_destroy();
    header('Location: index.html?timeout=1');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

$username = $_SESSION['user'];
$vaultFile = "vaults/{$username}.json";
$vault = file_exists($vaultFile) ? json_decode(file_get_contents($vaultFile), true) : [];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>KeyRing Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .totp-list {
      margin-top: 2em;
      display: flex;
      flex-direction: column;
      gap: 1.5em;
    }
    .totp-entry {
      background: #243b55;
      border-radius: 8px;
      padding: 1.2em 1em;
      box-shadow: 0 2px 10px #0006;
      display: flex;
      flex-direction: column;
      gap: 0.5em;
      margin-bottom: 1em;
    }
    .totp-entry:hover {
      box-shadow: 0 4px 20px #00c6ff55;
    }
    .label {
      font-size: 1.1em;
      font-weight: bold;
      color: #00c6ff;
      text-align: left;
      margin-bottom: 0.2em;
    }
    .code-timer-row {
      display: flex;
      align-items: center;
      gap: 1em;
    }
    .code {
      font-family: 'Consolas', monospace;
      font-size: 2em;
      letter-spacing: 0.15em;
      background: #16213e;
      color: #fff;
      padding: 0.2em 0.7em;
      border-radius: 6px;
      min-width: 120px;
      text-align: center;
      user-select: all;
      transition: background 0.2s;
    }
    .timer {
      font-size: 1em;
      color: #ffb347;
      min-width: 2.5em;
      text-align: right;
    }
    .logout-btn {
      margin-top: 2em;
      background: #ff4b2b;
      color: #fff;
      border: none;
      border-radius: 5px;
      padding: 0.8em 1.5em;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.2s;
    }
    .logout-btn:hover {
      background: #ff1c00;
    }
    .add-form input[type="text"] {
      margin-bottom: 0.5em;
    }
    .add-form button {
      margin-top: 0.5em;
    }
    .qr-overlay-bg {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0; top: 0; width: 100vw; height: 100vh;
      background: rgba(0,0,0,0.4);
      align-items: center;
      justify-content: center;
    }
    .qr-overlay-bg.active {
      display: flex;
    }
    .qr-popup {
      background: #fff;
      padding: 18px 18px 10px 18px;
      border-radius: 12px;
      box-shadow: 0 4px 32px #0008;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-width: 220px;
    }
    .qr-popup .close-qr-btn {
      margin-top: 10px;
      background: #ff4b2b;
      color: #fff;
      border: none;
      border-radius: 5px;
      padding: 0.5em 1.2em;
      font-weight: bold;
      cursor: pointer;
    }
    .totp-left {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 0.3em;
    }
    .totp-right {
      display: flex;
      align-items: center;
      gap: 0.5em;
    }
    .show-qr-btn {
      background: #00c6ff;
      color: #16213e;
      border: none;
      border-radius: 6px;
      padding: 0.5em 1.2em;
      font-weight: bold;
      cursor: pointer;
      font-size: 1em;
      transition: background 0.2s;
    }
    .show-qr-btn:hover {
      background: #00a6d6;
    }
    /* Add to your style block */
    .progress-bar {
      height: 4px;
      background: linear-gradient(90deg,#00c6ff,#ffb347);
      border-radius: 2px;
      margin-top: 0.3em;
      transition: width 0.2s;
    }
    body.light-mode {
      background: #f0f0f0;
      color: #222;
    }
    body.light-mode .card {
      background: #fff;
      color: #222;
    }
    body.light-mode .totp-entry {
      background: #e0e0e0;
    }
    body.light-mode .code {
      background: #fff;
      color: #222;
    }
    #settings-btn {
      position: absolute;
      top: 1.5em;
      right: 1.5em;
      background: #243b55;
      color: #00c6ff;
      border: none;
      border-radius: 50%;
      width: 44px;
      height: 44px;
      font-size: 1.5em;
      cursor: pointer;
      box-shadow: 0 2px 8px #0004;
      transition: background 0.2s, color 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    #settings-btn:hover {
      background: #00c6ff;
      color: #243b55;
    }
    #settings-modal {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0; top: 0; width: 100vw; height: 100vh;
      background: rgba(0,0,0,0.4);
      align-items: center;
      justify-content: center;
    }
    #settings-modal.active {
      display: flex;
    }
    #settings-modal .modal-content {
      background: #243b55;
      padding: 2em 2.5em 2em 2.5em;
      border-radius: 12px;
      box-shadow: 0 4px 32px #0008;
      min-width: 320px;
      color: #fff;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      gap: 1em;
    }
    #settings-modal h3 {
      margin-top: 0;
      margin-bottom: 1em;
      text-align: center;
      color: #00c6ff;
    }
    #settings-modal input[type="password"] {
      margin-bottom: 0.7em;
      padding: 0.7em;
      border-radius: 5px;
      border: none;
      font-size: 1em;
    }
    #settings-modal button {
      margin-top: 0.5em;
      padding: 0.7em;
      border-radius: 5px;
      border: none;
      font-weight: bold;
      font-size: 1em;
      cursor: pointer;
      background: #00c6ff;
      color: #16213e;
      transition: background 0.2s, color 0.2s;
    }
    #settings-modal button:hover {
      background: #00a6d6;
      color: #fff;
    }
    #close-settings {
      background: #ff4b2b;
      color: #fff;
      margin-top: 1em;
    }
    #close-settings:hover {
      background: #ff1c00;
    }
    #qr-reader {
      position: relative;
    }
    #qr-square-overlay {
      display: none;
      position: absolute;
      top: 50%;
      left: 50%;
      width: 140px;
      height: 140px;
      transform: translate(-50%, -50%);
      border: 3px solid #00c6ff;
      border-radius: 12px;
      box-sizing: border-box;
      pointer-events: none;
      z-index: 10;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2 style="margin-bottom:0.5em;">Welcome, <?php echo htmlspecialchars($username); ?></h2>
    <button id="scan-qr-btn" type="button" style="margin-bottom:1em;">Scan QR to Add</button>
    <div id="qr-reader" style="display:none;position:relative;margin-bottom:1em;">
      <div id="qr-square-overlay"
           style="display:none;position:absolute;top:50%;left:50%;width:140px;height:140px;transform:translate(-50%,-50%);
                  border:3px solid #00c6ff;border-radius:12px;box-sizing:border-box;pointer-events:none;z-index:2;">
      </div>
    </div>
    <form class="add-form" action="save.php" method="post">
      <input type="text" name="label" placeholder="Label (e.g. Google)" required>
      <input type="text" name="secret" placeholder="TOTP Secret (Base32)" required>
      <button type="submit">Add</button>
    </form>
    <div class="totp-list">
    <?php if (count($vault) === 0): ?>
      <p style="color:#aaa;">No TOTP secrets yet.</p>
    <?php else: ?>
      <?php foreach ($vault as $i => $entry): ?>
        <div class="totp-entry" data-secret="<?php echo htmlspecialchars($entry['secret']); ?>" data-index="<?php echo $i; ?>">
          <div class="label"><?php echo htmlspecialchars($entry['label']); ?></div>
          <div class="code-timer-row">
            <div class="code">------</div>
            <div class="timer"></div>
          </div>
          <div class="progress-bar"></div>
          <button class="edit-btn" type="button">✏️</button>
          <button class="delete-btn" type="button">🗑️</button>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
    </div>
    <form action="logout.php" method="post">
      <button class="logout-btn" type="submit">Logout</button>
    </form>
    <button id="settings-btn" title="Settings">⚙️</button>
  </div>
  <div class="qr-overlay-bg" id="qr-overlay">
    <div class="qr-popup">
      <div class="qr-code"></div>
      <button class="close-qr-btn" type="button">Close</button>
    </div>
  </div>
  <div id="settings-modal">
    <div class="modal-content">
      <h3>Settings</h3>
      <form id="pw-change-form">
        <input type="password" name="oldpw" placeholder="Old password" required>
        <input type="password" name="newpw" placeholder="New password" required>
        <button type="submit">Change Password</button>
      </form>
      <button id="toggle-dark" type="button">Toggle Dark/Light Mode</button>
      <button id="close-settings" type="button">Close</button>
    </div>
  </div>
  <div id="notification" style="position:fixed;top:1em;right:1em;z-index:3000;display:none;padding:1em 2em;border-radius:8px;font-weight:bold;"></div>
  <script src="https://cdn.jsdelivr.net/npm/otplib@11.0.1/otplib-browser.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="https://unpkg.com/html5-qrcode"></script>
  <script>
  function showNotification(msg, success=true) {
    const n = document.getElementById('notification');
    n.textContent = msg;
    n.style.background = success ? '#00c6ff' : '#ff4b2b';
    n.style.color = '#fff';
    n.style.display = 'block';
    setTimeout(()=>{n.style.display='none';},2000);
  }

  document.querySelectorAll('.totp-entry').forEach(function(entry) {
    const secret = entry.getAttribute('data-secret');
    const label = entry.querySelector('.label').textContent;
    const codeDiv = entry.querySelector('.code');
    const timerDiv = entry.querySelector('.timer');
    const progressBar = entry.querySelector('.progress-bar');
    function updateCode() {
      try {
        const code = window.otplib.authenticator.generate(secret);
        codeDiv.textContent = code;
      } catch (e) {
        codeDiv.textContent = 'Invalid';
      }
      const epoch = Math.floor(Date.now() / 1000);
      const rem = 30 - (epoch % 30);
      timerDiv.textContent = rem + 's';
      timerDiv.style.color = rem < 6 ? '#ff4b2b' : '#ffb347';
      if(progressBar) progressBar.style.width = ((30-rem)/30*100)+'%';
    }
    updateCode();
    setInterval(updateCode, 1000);

    // Copy to clipboard
    codeDiv.style.cursor = "pointer";
    codeDiv.title = "Click to copy";
    codeDiv.addEventListener('click', function() {
      if (codeDiv.textContent && codeDiv.textContent !== 'Invalid' && codeDiv.textContent !== '------') {
        navigator.clipboard.writeText(codeDiv.textContent);
        codeDiv.style.background = "#00c6ff";
        codeDiv.style.color = "#16213e";
        setTimeout(() => {
          codeDiv.style.background = "#16213e";
          codeDiv.style.color = "#fff";
        }, 500);
      }
    });

    // QR code popup
    const showQrBtn = entry.querySelector('.show-qr-btn');
    if (showQrBtn) {
      showQrBtn.addEventListener('click', function(e) {
        // Generate otpauth:// URI
        const uri = `otpauth://totp/${encodeURIComponent(label)}?secret=${secret}&issuer=KeyRing`;
        const qrCodeDiv = document.querySelector('.qr-code');
        qrCodeDiv.innerHTML = '';
        new QRCode(qrCodeDiv, { text: uri, width: 200, height: 200 });
        const qrOverlay = document.getElementById('qr-overlay');
        qrOverlay.classList.add('active');
      });
    }
  });

// QR Code Scanner
const scanBtn = document.getElementById('scan-qr-btn');
const qrReader = document.getElementById('qr-reader');
const overlay = document.getElementById('qr-square-overlay');

scanBtn.addEventListener('click', function() {
  qrReader.style.display = 'block';
  scanBtn.style.display = 'none';

  // Start QR scanner
  const html5Qr = new Html5Qrcode("qr-reader");
  html5Qr.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 200 },
    qrCodeMessage => {
      if (qrCodeMessage.startsWith('otpauth://totp/')) {
        const url = new URL(qrCodeMessage);
        const label = decodeURIComponent(url.pathname.slice(1));
        const params = new URLSearchParams(url.search);
        const secret = params.get('secret');
        document.querySelector('input[name="label"]').value = label;
        document.querySelector('input[name="secret"]').value = secret;
        html5Qr.stop();
        qrReader.style.display = 'none';
        scanBtn.style.display = 'inline-block';
        overlay.style.display = 'none';
      }
    },
    errorMessage => {}
  ).then(() => {
    // Move overlay to last child so it sits above the video
    qrReader.appendChild(overlay);
    overlay.style.display = 'block';
  });
});

  // Close QR overlay
  const qrOverlay = document.getElementById('qr-overlay');
  const closeQrBtn = qrOverlay.querySelector('.close-qr-btn');
  closeQrBtn.addEventListener('click', function() {
    qrOverlay.classList.remove('active');
    const qrCodeDiv = document.querySelector('.qr-code');
    qrCodeDiv.innerHTML = '';
  });
  qrOverlay.addEventListener('click', function(e) {
    if (e.target === qrOverlay) {
      qrOverlay.classList.remove('active');
      const qrCodeDiv = document.querySelector('.qr-code');
      qrCodeDiv.innerHTML = '';
    }
  });

  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      if (confirm('Delete this TOTP entry?')) {
        const idx = btn.closest('.totp-entry').dataset.index;
        fetch('delete.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'index=' + encodeURIComponent(idx)
        })
        .then(r => r.json())
        .then(data => {
          showNotification(data.success ? 'Deleted!' : 'Error deleting', data.success);
          if (data.success) location.reload();
        });
      }
    });
  });

  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const entry = btn.closest('.totp-entry');
      const idx = entry.dataset.index;
      const label = entry.querySelector('.label').textContent;
      const secret = entry.dataset.secret;
      const newLabel = prompt('Edit label:', label);
      const newSecret = prompt('Edit secret:', secret);
      if (newLabel && newSecret) {
        fetch('edit.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'index=' + encodeURIComponent(idx) + '&label=' + encodeURIComponent(newLabel) + '&secret=' + encodeURIComponent(newSecret)
        })
        .then (r => r.json())
        .then(data => {
          showNotification(data.success ? 'Updated!' : 'Error updating', data.success);
          if (data.success) location.reload();
        });
      }
    });
  });

  const settingsBtn = document.getElementById('settings-btn');
  const settingsModal = document.getElementById('settings-modal');
  const closeSettings = document.getElementById('close-settings');
  settingsBtn.onclick = () => settingsModal.classList.add('active');
  closeSettings.onclick = () => settingsModal.classList.remove('active');
  document.getElementById('pw-change-form').onsubmit = function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('change_password.php', {method:'POST', body:fd})
      .then(r=>r.json()).then(data=>{
        showNotification(data.success ? 'Password changed!' : 'Wrong password', data.success);
        if(data.success) settingsModal.classList.remove('active');
      });
  };
  document.getElementById('toggle-dark').onclick = function() {
    document.body.classList.toggle('light-mode');
    localStorage.setItem('theme', document.body.classList.contains('light-mode') ? 'light' : 'dark');
  };
  if(localStorage.getItem('theme')==='light') document.body.classList.add('light-mode');
  </script>
</body>
</html>

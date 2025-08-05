<?php
session_start();

$usersFile = 'users.json';
$vaultDir = 'vaults';

if (!file_exists($usersFile)) file_put_contents($usersFile, '{}');
if (!file_exists($vaultDir)) mkdir($vaultDir);

$data = json_decode(file_get_contents($usersFile), true);
$action = $_POST['action'];

$username = $_POST['username'];
$password = $_POST['password'];

if ($action === 'register') {
    if (isset($data[$username])) {
        die('Benutzer existiert bereits. <a href="register.html">Zurück</a>');
    }
    $data[$username] = password_hash($password, PASSWORD_BCRYPT);
    file_put_contents($usersFile, json_encode($data, JSON_PRETTY_PRINT));
    file_put_contents("$vaultDir/$username.json", '{}');
    $_SESSION['user'] = $username;
    header("Location: dashboard.php");
    exit;
}

if ($action === 'login') {
    if (!isset($data[$username]) || !password_verify($password, $data[$username])) {
        die('Login fehlgeschlagen. <a href="index.html">Zurück</a>');
    }
    $_SESSION['user'] = $username;
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $usersFile = 'users.json';
    $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
    if (!isset($users[$username]) || !password_verify($password, $users[$username])) {
        die('Invalid username or password.');
    }
    $_SESSION['user'] = $username;
    header('Location: dashboard.php');
    exit;
}
?>

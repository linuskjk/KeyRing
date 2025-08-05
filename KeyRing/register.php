<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (!$username || !$password) {
        die('Username and password required.');
    }
    $usersFile = 'users.json';
    $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
    if (isset($users[$username])) {
        die('Username already exists.');
    }
    $users[$username] = password_hash($password, PASSWORD_DEFAULT);
    file_put_contents($usersFile, json_encode($users));
    mkdir("vaults");
    file_put_contents("vaults/{$username}.json", json_encode([]));
    $_SESSION['user'] = $username;
    header('Location: dashboard.php');
    exit;
}
?>
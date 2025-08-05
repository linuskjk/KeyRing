<?php
session_start();
if (!isset($_SESSION['user'])) { echo json_encode(['success'=>false]); exit; }
$username = $_SESSION['user'];
$oldpw = $_POST['oldpw'];
$newpw = $_POST['newpw'];
$usersFile = 'users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
if (!isset($users[$username]) || !password_verify($oldpw, $users[$username])) {
    echo json_encode(['success'=>false]);
    exit;
}
$users[$username] = password_hash($newpw, PASSWORD_DEFAULT);
file_put_contents($usersFile, json_encode($users));
echo json_encode(['success'=>true]);
?>
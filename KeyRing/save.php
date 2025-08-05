<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $label = trim($_POST['label']);
    $secret = strtoupper(trim($_POST['secret']));
    if (!$label || !$secret) {
        die('Label and secret required.');
    }
    $username = $_SESSION['user'];
    $vaultFile = "vaults/{$username}.json";
    $vault = file_exists($vaultFile) ? json_decode(file_get_contents($vaultFile), true) : [];
    $vault[] = ['label' => $label, 'secret' => $secret];
    file_put_contents($vaultFile, json_encode($vault));
    header('Location: dashboard.php');
    exit;
}
?>

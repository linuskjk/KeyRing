<?php
session_start();
if (!isset($_SESSION['user'])) { echo json_encode(['success'=>false]); exit; }
$idx = intval($_POST['index']);
$label = trim($_POST['label']);
$secret = strtoupper(trim($_POST['secret']));
$username = $_SESSION['user'];
$vaultFile = "vaults/{$username}.json";
$vault = file_exists($vaultFile) ? json_decode(file_get_contents($vaultFile), true) : [];
if (isset($vault[$idx])) {
    $vault[$idx] = ['label'=>$label, 'secret'=>$secret];
    file_put_contents($vaultFile, json_encode($vault));
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false]);
}
?>
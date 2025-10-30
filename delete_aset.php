<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userID']) || $_SESSION['levelID'] != 1001) {
    header("Location: login.php");
    exit;
}
// SESSION TIMEOUT (15 minutes)
$timeout_duration = 900; // 900 seconds = 15 minutes

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    echo "<script>alert('Session expired due to inactivity. Please login again.');window.location.href='login.php';</script>";
    exit;
}
$_SESSION['last_activity'] = time();

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM aset WHERE asetID = :id");
    $stmt->execute([':id' => $id]);
    echo "<script>alert('Aset deleted successfully');window.location.href='admin_dashboard.php';</script>";
} else {
    header("Location: admin_dashboard.php");
}
?>

<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$levelID = $_SESSION['levelID'];
$deptID = $_SESSION['deptID'];

// Prevent admin from using this page
if ($levelID == 1) {
    header("Location: admin-dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asetID = $_POST['asetID'];
    $namaPengguna = $_POST['namaPengguna'];
    $jawatan = $_POST['jawatan'];

    // Update only if aset belongs to urusetia’s department
    $sql = "UPDATE aset 
            SET namaPengguna = ?, jawatan = ?, updatedBy = ? 
            WHERE asetID = ? AND deptID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssis", $namaPengguna, $jawatan, $userID, $asetID, $deptID);

    if ($stmt->execute()) {
        echo "<script>alert('Aset updated successfully!'); window.location='aset-list.php';</script>";
    } else {
        echo "<script>alert('Failed to update aset!'); history.back();</script>";
    }
}
?>

<?php
include 'db_connect.php';
session_start();

// Only admin allowed
if (!isset($_SESSION['levelID']) || $_SESSION['levelID'] != 1) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asetID = $_POST['asetID'];
    $location = $_POST['location'];
    $bahagian = $_POST['bahagian'];
    $namaPengguna = $_POST['namaPengguna'];
    $jawatan = $_POST['jawatan'];
    $modelID = $_POST['modelID'];
    $SN_Komputer = $_POST['SN_Komputer'];
    $SN_Monitor = $_POST['SN_Monitor'];
    $ipAddress = $_POST['ipAddress'];

    $sql = "UPDATE aset 
            SET location = ?, `Bahagian/Cawangan/Seksyen` = ?, namaPengguna = ?, jawatan = ?, 
                modelID = ?, SN_Komputer = ?, SN_Monitor = ?, ipAddress = ?
            WHERE asetID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $location, $bahagian, $namaPengguna, $jawatan, $modelID, $SN_Komputer, $SN_Monitor, $ipAddress, $asetID);

    if ($stmt->execute()) {
        echo "<script>alert('Aset updated successfully!'); window.location='admin-dashboard.php';</script>";
    } else {
        echo "<script>alert('Error updating aset!'); history.back();</script>";
    }
}
?>

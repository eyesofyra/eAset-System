<?php
session_start();
include 'db_connect.php';

// Check login
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$levelID = $_SESSION['levelID'];
$deptID = $_SESSION['deptID'];

// Only urusetia (not admin) can access this page
if ($levelID == 1) {
    header("Location: admin-dashboard.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "<script>alert('No Aset selected!'); window.location='aset-list.php';</script>";
    exit();
}

$asetID = $_GET['id'];

// Fetch aset details (restricted to their own department)
$sql = "SELECT a.*, m.modelName, d.deptName 
        FROM aset a
        LEFT JOIN model m ON a.modelID = m.modelID
        LEFT JOIN department d ON a.deptID = d.deptID
        WHERE a.asetID = '$asetID' AND a.deptID = '$deptID'";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<script>alert('You are not allowed to edit this aset.'); window.location='aset-list.php';</script>";
    exit();
}

$aset = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Aset (Urusetia)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0d1b2a;
            color: white;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            background-color: #1b263b;
            border: none;
            border-radius: 10px;
        }
        .btn-primary {
            background-color: #1e40af;
            border: none;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        label {
            font-weight: 600;
        }
        input[readonly] {
            background-color: #e9ecef;
            color: #333;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card p-4">
        <h3 class="text-center mb-4">Edit Aset (Urusetia)</h3>

        <form action="update-aset-urusetia.php" method="POST">
            <input type="hidden" name="asetID" value="<?= $aset['asetID'] ?>">

            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($aset['deptName']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($aset['location']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Bahagian/Cawangan/Seksyen</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($aset['Bahagian/Cawangan/Seksyen']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Pengguna</label>
                <input type="text" class="form-control" name="namaPengguna" value="<?= htmlspecialchars($aset['namaPengguna']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Jawatan</label>
                <input type="text" class="form-control" name="jawatan" value="<?= htmlspecialchars($aset['jawatan']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Model</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($aset['modelName']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">S/N Komputer</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($aset['SN_Komputer']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">S/N Monitor</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($aset['SN_Monitor']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">IP Address</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($aset['ipAddress']) ?>" readonly>
            </div>

            <div class="d-flex justify-content-between">
                <a href="aset-list.php" class="btn btn-secondary">← Back</a>
                <button type="submit" class="btn btn-primary">Update Aset</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>

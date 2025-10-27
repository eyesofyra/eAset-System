<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$levelID = $_SESSION['levelID'];
$deptID = $_SESSION['deptID'];
$isAdmin = ($levelID == 1);

if (!isset($_GET['id'])) {
    header("Location: aset-list.php");
    exit();
}

$asetID = $_GET['id'];

// Fetch asset info
$sql = "SELECT * FROM Aset WHERE asetID = '$asetID'";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    die("Asset not found!");
}
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $namaPengguna = $_POST['namaPengguna'];
    $jawatan = $_POST['jawatan'];

    if ($isAdmin) {
        $location = $_POST['location'];
        $bahagian = $_POST['bahagian_cawangan_seksyen'];
        $modelID = $_POST['modelID'];
        $SNKomputer = $_POST['SN_Komputer'];
        $SNMonitor = $_POST['SN_Monitor'];
        $ipAddress = $_POST['ipAddress'];
        $deptID = $_POST['deptID'];

        $update = "UPDATE Aset SET 
                    location='$location',
                    bahagian_cawangan_seksyen='$bahagian',
                    namaPengguna='$namaPengguna',
                    jawatan='$jawatan',
                    modelID='$modelID',
                    SN_Komputer='$SNKomputer',
                    SN_Monitor='$SNMonitor',
                    ipAddress='$ipAddress',
                    deptID='$deptID',
                    updatedBy='$userID'
                   WHERE asetID='$asetID'";
    } else {
        // Urusetia: only update namaPengguna and jawatan for their own dept
        $update = "UPDATE Aset SET 
                    namaPengguna='$namaPengguna',
                    jawatan='$jawatan',
                    updatedBy='$userID'
                   WHERE asetID='$asetID' AND deptID='$deptID'";
    }

    if ($conn->query($update)) {
        echo "<script>alert('Record updated successfully'); window.location='aset-list.php';</script>";
    } else {
        echo "<script>alert('Update failed: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Asset - ICT Asset System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark-blue text-white">

<nav class="navbar navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">ICT Asset System</a>
    <a href="aset-list.php" class="btn btn-light">Back</a>
  </div>
</nav>

<div class="container mt-4">
  <h3>Edit Asset Information</h3>

  <form method="POST" class="bg-white text-dark p-4 rounded shadow">
    <?php if ($isAdmin): ?>
      <div class="mb-3">
        <label>Location</label>
        <input type="text" name="location" class="form-control" value="<?= $row['location'] ?>">
      </div>
      <div class="mb-3">
        <label>Bahagian/Cawangan/Seksyen</label>
        <input type="text" name="bahagian_cawangan_seksyen" class="form-control" value="<?= $row['bahagian_cawangan_seksyen'] ?>">
      </div>
    <?php endif; ?>

    <div class="mb-3">
      <label>Nama Pengguna</label>
      <input type="text" name="namaPengguna" class="form-control" value="<?= $row['namaPengguna'] ?>">
    </div>

    <div class="mb-3">
      <label>Jawatan</label>
      <input type="text" name="jawatan" class="form-control" value="<?= $row['jawatan'] ?>">
    </div>

    <?php if ($isAdmin): ?>
      <div class="mb-3">
        <label>Model ID</label>
        <input type="text" name="modelID" class="form-control" value="<?= $row['modelID'] ?>">
      </div>
      <div class="mb-3">
        <label>S/N Komputer</label>
        <input type="text" name="SN_Komputer" class="form-control" value="<?= $row['SN_Komputer'] ?>">
      </div>
      <div class="mb-3">
        <label>S/N Monitor</label>
        <input type="text" name="SN_Monitor" class="form-control" value="<?= $row['SN_Monitor'] ?>">
      </div>
      <div class="mb-3">
        <label>IP Address</label>
        <input type="text" name="ipAddress" class="form-control" value="<?= $row['ipAddress'] ?>">
      </div>
      <div class="mb-3">
        <label>Department ID</label>
        <input type="text" name="deptID" class="form-control" value="<?= $row['deptID'] ?>">
      </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
  </form>
</div>

</body>
</html>

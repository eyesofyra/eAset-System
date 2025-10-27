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

// --- Admin filter selection ---
$selectedDeptID = isset($_GET['deptID']) ? $_GET['deptID'] : null;
$deptNameTitle = "";

// --- Get department name for title ---
if ($isAdmin && !empty($selectedDeptID)) {
    $deptQuery = $conn->query("SELECT deptName FROM Department WHERE deptID = '$selectedDeptID'");
    if ($deptQuery && $deptQuery->num_rows > 0) {
        $deptRow = $deptQuery->fetch_assoc();
        $deptNameTitle = $deptRow['deptName'];
    }
}

// --- SQL query for aset data ---
if ($isAdmin) {
    if (!empty($selectedDeptID)) {
        $sql = "SELECT a.*, d.deptName, m.modelName, u.userName AS updatedByName
                FROM Aset a
                LEFT JOIN Department d ON a.deptID = d.deptID
                LEFT JOIN Model m ON a.modelID = m.modelID
                LEFT JOIN Users u ON a.updatedBy = u.userID
                WHERE a.deptID = '$selectedDeptID'
                ORDER BY a.asetID DESC";
    } else {
        $sql = "SELECT a.*, d.deptName, m.modelName, u.userName AS updatedByName
                FROM Aset a
                LEFT JOIN Department d ON a.deptID = d.deptID
                LEFT JOIN Model m ON a.modelID = m.modelID
                LEFT JOIN Users u ON a.updatedBy = u.userID
                ORDER BY a.asetID DESC";
    }
} else {
    $sql = "SELECT a.*, d.deptName, m.modelName, u.userName AS updatedByName
            FROM Aset a
            LEFT JOIN Department d ON a.deptID = d.deptID
            LEFT JOIN Model m ON a.modelID = m.modelID
            LEFT JOIN Users u ON a.updatedBy = u.userID
            WHERE a.deptID = '$deptID'
            ORDER BY a.asetID DESC";
}

$result = $conn->query($sql);

// --- Get list of departments for admin dropdown ---
$deptList = [];
if ($isAdmin) {
    $deptListQuery = $conn->query("SELECT deptID, deptName FROM Department ORDER BY deptName ASC");
    while ($d = $deptListQuery->fetch_assoc()) {
        $deptList[] = $d;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Asset List - ICT Asset System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background-color: #0b1a33;
  color: white;
}
.table thead th {
  background-color: #1e90ff;
  color: white;
}
.card {
  background-color: #13294b;
  border: none;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(255,255,255,0.1);
}
.btn-primary {
  background-color: #1e90ff;
  border: none;
}
.btn-primary:hover {
  background-color: #63b3ed;
}
.form-select {
  background-color: #f8f9fa;
  color: #000;
  border-radius: 8px;
}
</style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">ICT Asset System</a>
    <a href="logout.php" class="btn btn-light">Logout</a>
  </div>
</nav>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3>ICT Asset List</h3>
      <?php if ($isAdmin && !empty($deptNameTitle)) { ?>
        <h5 class="text-info">Viewing assets for: <?= htmlspecialchars($deptNameTitle) ?></h5>
      <?php } elseif ($isAdmin && empty($selectedDeptID)) { ?>
        <h5 class="text-info">Viewing assets for: <span class="text-light">All Departments</span></h5>
      <?php } ?>
    </div>
  </div>

  <?php if ($isAdmin) { ?>
  <form method="GET" class="mb-3">
    <div class="row g-2 align-items-center">
      <div class="col-md-4">
        <label for="deptID" class="form-label text-light">Select Department:</label>
        <select class="form-select" id="deptID" name="deptID" onchange="this.form.submit()">
          <option value="">-- All Departments --</option>
          <?php foreach ($deptList as $dept) { ?>
            <option value="<?= $dept['deptID'] ?>" <?= ($selectedDeptID == $dept['deptID']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($dept['deptName']) ?>
            </option>
          <?php } ?>
        </select>
      </div>
    </div>
  </form>
  <?php } ?>

  <div class="card p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover align-middle bg-white text-dark">
        <thead class="text-center">
          <tr>
            <th>ID</th>
            <th>Location</th>
            <th>Bahagian/Cawangan/Seksyen</th>
            <th>Nama Pengguna</th>
            <th>Jawatan</th>
            <th>Model</th>
            <th>S/N Komputer</th>
            <th>S/N Monitor</th>
            <th>IP Address</th>
            <th>Department</th>
            <th>Updated By</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0) { 
              while($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?= $row['asetID'] ?></td>
            <td><?= htmlspecialchars($row['location']) ?></td>
            <td><?= htmlspecialchars($row['bahagian_cawangan_seksyen']) ?></td>
            <td><?= htmlspecialchars($row['namaPengguna']) ?></td>
            <td><?= htmlspecialchars($row['jawatan']) ?></td>
            <td><?= htmlspecialchars($row['modelName']) ?></td>
            <td><?= htmlspecialchars($row['SN_Komputer']) ?></td>
            <td><?= htmlspecialchars($row['SN_Monitor']) ?></td>
            <td><?= htmlspecialchars($row['ipAddress']) ?></td>
            <td><?= htmlspecialchars($row['deptName']) ?></td>
            <td><?= htmlspecialchars($row['updatedBy']) ?></td>
            <td class="text-center">
              <a href="aset-edit.php?id=<?= $row['asetID'] ?>" class="btn btn-sm btn-primary">Edit</a>
            </td>
          </tr>
          <?php } } else { ?>
          <tr><td colspan="12" class="text-center text-muted">No asset records found.</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>

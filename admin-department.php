<?php
session_start();
include('db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$levelID = $_SESSION['levelID'];
$isAdmin = ($levelID == 1);

// Only Admins can access
if (!$isAdmin) {
    header("Location: dashboard.php");
    exit();
}

// Fetch all departments
$sql = "SELECT * FROM Department ORDER BY deptName ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Select Department - ICT Asset System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background-color: #0b1a33;
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
</style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">ICT Asset System</a>
    <a href="logout.php" class="btn btn-light">Logout</a>
  </div>
</nav>

<div class="container mt-5">
  <div class="card p-4">
    <h3 class="text-center mb-4">Select Department to Manage Assets</h3>

    <form method="GET" action="aset-list.php">
      <div class="mb-3">
        <label for="deptID" class="form-label">Department</label>
        <select name="deptID" id="deptID" class="form-select" required>
          <option value="">-- Select Department --</option>
          <?php while ($row = $result->fetch_assoc()) { ?>
            <option value="<?= $row['deptID'] ?>"><?= htmlspecialchars($row['deptName']) ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="text-center">
        <button type="submit" class="btn btn-primary px-4">View Assets</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>

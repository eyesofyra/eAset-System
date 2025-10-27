<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}
include('db_connect.php');

$userID = $_SESSION['userID'];
$levelID = $_SESSION['levelID'];
$deptID = $_SESSION['deptID'];

$isAdmin = ($levelID == 1);
?>

body.bg-dark-blue {
  background-color: #0B1E3C;
}

.card {
  border-radius: 12px;
  background-color: #ffffff;
}

.btn-primary {
  background-color: #1E40AF;
  border: none;
}

.btn-primary:hover {
  background-color: #003366;
}

.text-primary {
  color: #1E40AF !important;
}

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - ICT Asset System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark-blue text-white">

<nav class="navbar navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">ICT Asset System</a>
    <a href="logout.php" class="btn btn-light">Logout</a>
  </div>
</nav>

<div class="container mt-5">
  <h2>Welcome <?= $isAdmin ? 'Admin' : 'Urusetia' ?>!</h2>
  <p class="text-white-50">You can manage ICT assets here.</p>

  <a href="aset-list.php" class="btn btn-light mt-3">View Asset List</a>
</div>

</body>
</html>

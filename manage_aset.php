<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$levelID = $_SESSION['levelID'];
$deptID = $_SESSION['deptID'];

if ($levelID == 1) {
    // Admin: can see all
    $query = "SELECT * FROM Aset";
} else {
    // Urusetia: only their department
    $query = "SELECT * FROM Aset WHERE deptID='$deptID'";
}
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Aset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0b1a33; color: white; }
        .table { color: white; }
        .table thead { background-color: #1f4ca1; }
        .btn-edit { background-color: #1f4ca1; color: white; }
        .btn-edit:hover { background-color: #163d82; }
    </style>
</head>
<script>
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', e => {
    if(!confirm('Edit this asset record?')) e.preventDefault();
  });
});
</script>

<body>
<div class="container mt-4">
    <h3>ICT Asset Management</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Location</th>
                <th>Bahagian/Cawangan/Seksyen</th>
                <th>Nama Pengguna</th>
                <th>Jawatan</th>
                <th>SN Komputer</th>
                <th>SN Monitor</th>
                <th>IP Address</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['location'] ?></td>
                <td><?= $row['bahagian_cawangan_seksyen'] ?></td>
                <td><?= $row['namaPengguna'] ?></td>
                <td><?= $row['jawatan'] ?></td>
                <td><?= $row['SN_Komputer'] ?></td>
                <td><?= $row['SN_Monitor'] ?></td>
                <td><?= $row['ipAddress'] ?></td>
                <td>
                    <a href="edit_aset.php?id=<?= $row['asetID'] ?>" class="btn btn-edit btn-sm">Edit</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>

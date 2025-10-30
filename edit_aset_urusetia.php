<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userID']) || $_SESSION['levelID'] != 2001) {
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
if (!$id) {
    header("Location: urusetia_dashboard.php");
    exit;
}

// Ensure the aset belongs to this urusetia's department
$stmt = $pdo->prepare("SELECT * FROM aset WHERE asetID = :id AND deptID = :deptID");
$stmt->execute([':id' => $id, ':deptID' => $_SESSION['deptID']]);
$aset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aset) {
    die("Unauthorized or record not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE aset SET 
        namaPengguna = :nama, 
        jawatan = :jawatan,
        updatedBy = :updatedBy
        WHERE asetID = :id AND deptID = :deptID");

    $stmt->execute([
        ':nama' => $_POST['namaPengguna'],
        ':jawatan' => $_POST['jawatan'],
        ':updatedBy' => $_SESSION['userID'],
        ':id' => $id,
        ':deptID' => $_SESSION['deptID']
    ]);

    echo "<script>alert('Data updated successfully');window.location.href='urusetia_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Aset (Urusetia)</title>
</head>
<body>
    <h2>Edit Aset (ID: <?= $aset['asetID']; ?>)</h2>
    <form method="POST">
        <label>Nama Pengguna:</label><br>
        <input type="text" name="namaPengguna" value="<?= htmlspecialchars($aset['namaPengguna']); ?>"><br><br>

        <label>Jawatan:</label><br>
        <input type="text" name="jawatan" value="<?= htmlspecialchars($aset['jawatan']); ?>"><br><br>

        <button type="submit">Update</button>
        <a href="urusetia_dashboard.php">Cancel</a>
    </form>
</body>
</html>

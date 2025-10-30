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

// Fetch models and departments for dropdowns
$models = $pdo->query("SELECT * FROM model ORDER BY modelName")->fetchAll(PDO::FETCH_ASSOC);
$departments = $pdo->query("SELECT * FROM department ORDER BY deptName")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location = $_POST['location'];
    $bahagian = $_POST['bahagian_cawangan_seksyen'];
    $namaPengguna = $_POST['namaPengguna'];
    $jawatan = $_POST['jawatan'];
    $SN_Komputer = $_POST['SN_Komputer'];
    $SN_Monitor = $_POST['SN_Monitor'];
    $ipAddress = $_POST['ipAddress'];
    $modelID = $_POST['modelID'];
    $deptID = $_POST['deptID'];
    $updatedBy = $_SESSION['userID'];

    $stmt = $pdo->prepare("INSERT INTO aset (location, bahagian_cawangan_seksyen, namaPengguna, jawatan, SN_Komputer, SN_Monitor, ipAddress, modelID, deptID, updatedBy)
                           VALUES (:location, :bahagian, :nama, :jawatan, :snk, :snm, :ip, :modelID, :deptID, :updatedBy)");
    $stmt->execute([
        ':location' => $location,
        ':bahagian' => $bahagian,
        ':nama' => $namaPengguna,
        ':jawatan' => $jawatan,
        ':snk' => $SN_Komputer,
        ':snm' => $SN_Monitor,
        ':ip' => $ipAddress,
        ':modelID' => $modelID,
        ':deptID' => $deptID,
        ':updatedBy' => $updatedBy
    ]);

    echo "<script>alert('New Aset Added Successfully');window.location.href='admin_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Aset</title>
</head>
<body>
    <h2>Add New Aset</h2>
    <form method="POST">
        <label>Lokasi:</label><br><input type="text" name="location" required><br><br>
        <label>Bahagian/Cawangan/Seksyen:</label><br><input type="text" name="bahagian_cawangan_seksyen" required><br><br>
        <label>Nama Pengguna:</label><br><input type="text" name="namaPengguna"><br><br>
        <label>Jawatan:</label><br><input type="text" name="jawatan"><br><br>
        <label>SN Komputer:</label><br><input type="text" name="SN_Komputer"><br><br>
        <label>SN Monitor:</label><br><input type="text" name="SN_Monitor"><br><br>
        <label>IP Address:</label><br><input type="text" name="ipAddress"><br><br>

        <label>Model:</label><br>
        <select name="modelID" required>
            <option value="">-- Select Model --</option>
            <?php foreach ($models as $m): ?>
                <option value="<?= $m['modelID']; ?>"><?= htmlspecialchars($m['modelName']); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Department:</label><br>
        <select name="deptID" required>
            <option value="">-- Select Department --</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= $d['deptID']; ?>"><?= htmlspecialchars($d['deptName']); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Save</button>
        <a href="admin_dashboard.php">Cancel</a>
    </form>
</body>
</html>

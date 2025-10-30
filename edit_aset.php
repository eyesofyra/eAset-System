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

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: admin_dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM aset WHERE asetID = :id");
$stmt->execute([':id' => $id]);
$aset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aset) {
    die("Aset not found.");
}

$models = $pdo->query("SELECT * FROM model ORDER BY modelName")->fetchAll(PDO::FETCH_ASSOC);
$departments = $pdo->query("SELECT * FROM department ORDER BY deptName")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE aset SET 
        location=:loc, 
        bahagian_cawangan_seksyen=:bahagian,
        namaPengguna=:nama,
        jawatan=:jawatan,
        SN_Komputer=:snk,
        SN_Monitor=:snm,
        ipAddress=:ip,
        modelID=:modelID,
        deptID=:deptID,
        updatedBy=:updatedBy
        WHERE asetID=:id");

    $stmt->execute([
        ':loc' => $_POST['location'],
        ':bahagian' => $_POST['bahagian_cawangan_seksyen'],
        ':nama' => $_POST['namaPengguna'],
        ':jawatan' => $_POST['jawatan'],
        ':snk' => $_POST['SN_Komputer'],
        ':snm' => $_POST['SN_Monitor'],
        ':ip' => $_POST['ipAddress'],
        ':modelID' => $_POST['modelID'],
        ':deptID' => $_POST['deptID'],
        ':updatedBy' => $_SESSION['userID'],
        ':id' => $id
    ]);

    echo "<script>alert('Aset updated successfully');window.location.href='admin_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Aset</title>
</head>
<body>
    <h2>Edit Aset (ID: <?= $aset['asetID']; ?>)</h2>
    <form method="POST">
        <label>Lokasi:</label><br><input type="text" name="location" value="<?= htmlspecialchars($aset['location']); ?>"><br><br>
        <label>Bahagian/Cawangan/Seksyen:</label><br><input type="text" name="bahagian_cawangan_seksyen" value="<?= htmlspecialchars($aset['bahagian_cawangan_seksyen']); ?>"><br><br>
        <label>Nama Pengguna:</label><br><input type="text" name="namaPengguna" value="<?= htmlspecialchars($aset['namaPengguna']); ?>"><br><br>
        <label>Jawatan:</label><br><input type="text" name="jawatan" value="<?= htmlspecialchars($aset['jawatan']); ?>"><br><br>
        <label>SN Komputer:</label><br><input type="text" name="SN_Komputer" value="<?= htmlspecialchars($aset['SN_Komputer']); ?>"><br><br>
        <label>SN Monitor:</label><br><input type="text" name="SN_Monitor" value="<?= htmlspecialchars($aset['SN_Monitor']); ?>"><br><br>
        <label>IP Address:</label><br><input type="text" name="ipAddress" value="<?= htmlspecialchars($aset['ipAddress']); ?>"><br><br>

        <label>Model:</label><br>
        <select name="modelID">
            <?php foreach ($models as $m): ?>
                <option value="<?= $m['modelID']; ?>" <?= ($m['modelID'] == $aset['modelID']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($m['modelName']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Department:</label><br>
        <select name="deptID">
            <?php foreach ($departments as $d): ?>
                <option value="<?= $d['deptID']; ?>" <?= ($d['deptID'] == $aset['deptID']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($d['deptName']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Update</button>
        <a href="admin_dashboard.php">Cancel</a>
    </form>
</body>
</html>

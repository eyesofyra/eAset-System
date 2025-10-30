<?php
session_start();
include 'db_connect.php';

// Restrict to Urusetia only
if (!isset($_SESSION['userID']) || $_SESSION['levelID'] != 2001) {
    header("Location: login.php");
    exit;
}

// Session timeout (15 minutes)
$timeout_duration = 900; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    echo "<script>alert('Session expired due to inactivity. Please login again.');window.location.href='login.php';</script>";
    exit;
}
$_SESSION['last_activity'] = time();

$deptID = $_SESSION['deptID'];

// ✅ Get department name for display
$stmtDept = $pdo->prepare("SELECT deptName FROM department WHERE deptID = :deptID");
$stmtDept->bindParam(':deptID', $deptID);
$stmtDept->execute();
$dept = $stmtDept->fetch(PDO::FETCH_ASSOC);
$deptName = $dept ? $dept['deptName'] : 'Unknown Department';

// ✅ Handle search (optional)
$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $pdo->prepare("
        SELECT aset.*, model.modelName, department.deptName
        FROM aset
        LEFT JOIN model ON aset.modelID = model.modelID
        LEFT JOIN department ON aset.deptID = department.deptID
        WHERE aset.deptID = :deptID 
          AND (aset.namaPengguna LIKE :search OR aset.ipAddress LIKE :search)
        ORDER BY aset.asetID ASC
    ");
    $stmt->execute([':deptID' => $deptID, ':search' => "%$search%"]);
} else {
    $stmt = $pdo->prepare("
        SELECT aset.*, model.modelName, department.deptName
        FROM aset
        LEFT JOIN model ON aset.modelID = model.modelID
        LEFT JOIN department ON aset.deptID = department.deptID
        WHERE aset.deptID = :deptID
        ORDER BY aset.asetID ASC
    ");
    $stmt->execute([':deptID' => $deptID]);
}

$asets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Urusetia Dashboard - ICT Aset</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        input[type="text"] { padding: 5px; width: 220px; }
        button { padding: 5px 10px; }
    </style>
</head>
<body>
    <h2>Welcome, Urusetia <?= htmlspecialchars($_SESSION['userName']); ?></h2>
    <a href="change_password.php">Change Password</a> | 
    <a href="logout.php">Logout / Switch Account</a>
    <br><br>

    <!-- ✅ Show department name only -->
    <h3><?= htmlspecialchars($deptName); ?></h3>

    <!-- 🔍 Search Bar -->
    <form method="GET" action="urusetia_dashboard.php">
        <input type="text" name="search" placeholder="Search by User or IP..." value="<?= htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <?php if ($search): ?>
            <a href="urusetia_dashboard.php" style="margin-left:10px;">Clear</a>
        <?php endif; ?>
    </form>

    <table border="1" cellpadding="8">
        <tr>
            <th>Bil</th>
            <th>Lokasi</th>
            <th>Bahagian/Cawangan/Seksyen</th>
            <th>Nama Pengguna</th>
            <th>Jawatan</th>
            <th>Model</th>
            <th>SN Komputer</th>
            <th>SN Monitor</th>
            <th>IP Address</th>
            <th>Department</th>
            <th>Action</th>
        </tr>

        <?php $no = 1; // 👈 Paste this line right before the foreach loop ?>
        <?php foreach ($asets as $row): ?>
            <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['location']); ?></td>
            <td><?= htmlspecialchars($row['bahagian_cawangan_seksyen']); ?></td>
            <td><?= htmlspecialchars($row['namaPengguna']); ?></td>
            <td><?= htmlspecialchars($row['jawatan']); ?></td>
            <td><?= htmlspecialchars($row['modelName']); ?></td>
            <td><?= htmlspecialchars($row['SN_Komputer']); ?></td>
            <td><?= htmlspecialchars($row['SN_Monitor']); ?></td>
            <td><?= htmlspecialchars($row['ipAddress']); ?></td>
            <td><?= htmlspecialchars($row['deptName']); ?></td>
            <td><a href="edit_aset_urusetia.php?id=<?= $row['asetID']; ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
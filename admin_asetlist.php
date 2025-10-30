<?php
session_start();
include 'db_connect.php';

// Restrict to Admin only
if (!isset($_SESSION['userID']) || $_SESSION['levelID'] != 1001) {
    header("Location: login.php");
    exit;
}

// Session timeout
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    echo "<script>alert('Session expired. Please login again.');window.location.href='login.php';</script>";
    exit;
}
$_SESSION['last_activity'] = time();

// Get selected department
$deptID = $_GET['deptID'] ?? null;
if (!$deptID) {
    header("Location: admin_dashboard.php");
    exit;
}

// Get department name
$stmtDept = $pdo->prepare("SELECT deptName FROM department WHERE deptID = :deptID");
$stmtDept->execute([':deptID' => $deptID]);
$dept = $stmtDept->fetch(PDO::FETCH_ASSOC);
$deptName = $dept ? $dept['deptName'] : "Unknown Department";

// Handle search
$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $pdo->prepare("
        SELECT aset.*, model.modelName, department.deptName
        FROM aset
        LEFT JOIN model ON aset.modelID = model.modelID
        LEFT JOIN department ON aset.deptID = department.deptID
        WHERE aset.deptID = :deptID AND 
        (aset.namaPengguna LIKE :search OR aset.ipAddress LIKE :search)
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
    <title>Aset List - <?= htmlspecialchars($deptName); ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .top-links a { margin-right: 10px; }
    </style>
</head>
<body>
    <h2>📋 Aset List – <?= htmlspecialchars($deptName); ?> (<?= htmlspecialchars($deptID); ?>)</h2>
    <div class="top-links">
        <a href="admin_dashboard.php">⬅️ Back to Departments</a> |
        <a href="add_aset.php">➕ Add New Aset</a> |
        <a href="logout.php">Logout</a>
    </div>

    <!-- Search Bar -->
    <form method="GET" action="admin_asetlist.php">
        <input type="hidden" name="deptID" value="<?= htmlspecialchars($deptID); ?>">
        <input type="text" name="search" placeholder="Search by User or IP..." value="<?= htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <a href="admin_asetlist.php?deptID=<?= htmlspecialchars($deptID); ?>">Clear</a>
    </form>

    <table>
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
            <th>Actions</th>
        </tr>
        <?php $no = 1; ?>
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
            <td>
                <a href="edit_aset.php?id=<?= $row['asetID']; ?>">Edit</a> |
                <a href="delete_aset.php?id=<?= $row['asetID']; ?>" onclick="return confirm('Delete this record?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

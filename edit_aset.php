<?php
session_start();
include 'db_connect.php';

// Restrict to Admin only
if (!isset($_SESSION['userID']) || $_SESSION['levelID'] != 1001) {
    header("Location: login.php");
    exit;
}

// Session timeout (15 minutes)
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    echo "<script>alert('Session expired. Please login again.');window.location.href='login.php';</script>";
    exit;
}
$_SESSION['last_activity'] = time();

// Get Aset ID
$asetID = $_GET['id'] ?? null;
if (!$asetID) {
    header("Location: admin_dashboard.php");
    exit;
}

// Fetch aset record with department & cawangan
$stmt = $pdo->prepare("
    SELECT aset.*, 
           department.deptName,
           cawangan.cawanganName,
           seksyen.seksyenName
    FROM aset
    LEFT JOIN department ON aset.deptID = department.deptID
    LEFT JOIN cawangan ON aset.cawanganID = cawangan.cawanganID
    LEFT JOIN seksyen ON aset.seksyenID = seksyen.seksyenID
    WHERE asetID = :id
");
$stmt->execute([':id' => $asetID]);
$aset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aset) {
    echo "<script>alert('Record not found.');window.location.href='admin_dashboard.php';</script>";
    exit;
}

$deptID = $aset['deptID'];
$cawanganID = $aset['cawanganID'];

// Fetch all cawangan for this department
$stmtCaw = $pdo->prepare("SELECT cawanganID, cawanganName FROM cawangan WHERE deptID = :deptID ORDER BY cawanganName ASC");
$stmtCaw->execute([':deptID' => $deptID]);
$cawangans = $stmtCaw->fetchAll(PDO::FETCH_ASSOC);

// Fetch all models
$stmtModel = $pdo->query("SELECT modelID, modelName FROM model ORDER BY modelName ASC");
$models = $stmtModel->fetchAll(PDO::FETCH_ASSOC);

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $update = $pdo->prepare("
        UPDATE aset SET
            cawanganID = :cawanganID,
            seksyenID = :seksyenID,
            location = :location,
            namaPengguna = :namaPengguna,
            jawatan = :jawatan,
            modelID = :modelID,
            SN_Komputer = :SN_Komputer,
            SN_Monitor = :SN_Monitor,
            ipAddress = :ipAddress,
            updatedBy = :updatedBy,
            updatedAt = NOW()
        WHERE asetID = :asetID
    ");

    $update->execute([
        ':cawanganID' => $_POST['cawanganID'] ?: null,
        ':seksyenID' => $_POST['seksyenID'] ?: null,
        ':location' => $_POST['location'],
        ':namaPengguna' => $_POST['namaPengguna'],
        ':jawatan' => $_POST['jawatan'],
        ':modelID' => $_POST['modelID'],
        ':SN_Komputer' => $_POST['SN_Komputer'],
        ':SN_Monitor' => $_POST['SN_Monitor'],
        ':ipAddress' => $_POST['ipAddress'],
        ':updatedBy' => $_SESSION['userID'],
        ':asetID' => $asetID
    ]);

    echo "<script>alert('✅ Aset updated successfully!');window.location.href='admin_asetlist.php?deptID=$deptID';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Aset – <?= htmlspecialchars($aset['deptName']); ?></title>
<style>
body { font-family: Arial, sans-serif; padding: 30px; background-color: #f7f9fc; }
form { max-width: 600px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
label { display: block; margin-top: 10px; font-weight: bold; color: #004aad; }
input, select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
button { margin-top: 20px; padding: 10px 18px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
button:hover { background-color: #0056b3; }
a { text-decoration: none; color: #007bff; }
a:hover { text-decoration: underline; }
</style>
</head>
<body>

<h2>Edit Aset – <?= htmlspecialchars($aset['deptName']); ?></h2>

<form method="POST">
    <label>Bahagian:</label>
    <input type="text" value="<?= htmlspecialchars($aset['deptName']); ?>" disabled>

    <label>Cawangan:</label>
    <select name="cawanganID" required>
        <option value="">-- Select Cawangan --</option>
        <?php foreach ($cawangans as $c): ?>
            <option value="<?= htmlspecialchars($c['cawanganID']); ?>" <?= ($aset['cawanganID'] == $c['cawanganID']) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($c['cawanganName']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Seksyen:</label>
    <select name="seksyenID" required>
        <option value="">-- Select Seksyen --</option>
        <!-- Options populated dynamically by JS -->
    </select>

    <label>Location:</label>
    <input type="text" name="location" value="<?= htmlspecialchars($aset['location']); ?>" required>

    <label>Nama Pengguna:</label>
    <input type="text" name="namaPengguna" value="<?= htmlspecialchars($aset['namaPengguna']); ?>" required>

    <label>Jawatan:</label>
    <input type="text" name="jawatan" value="<?= htmlspecialchars($aset['jawatan']); ?>" required>

    <label>Model:</label>
    <select name="modelID" required>
        <option value="">-- Select Model --</option>
        <?php foreach ($models as $m): ?>
            <option value="<?= htmlspecialchars($m['modelID']); ?>" <?= ($aset['modelID'] == $m['modelID']) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($m['modelName']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>SN Komputer:</label>
    <input type="text" name="SN_Komputer" value="<?= htmlspecialchars($aset['SN_Komputer']); ?>" required>

    <label>SN Monitor:</label>
    <input type="text" name="SN_Monitor" value="<?= htmlspecialchars($aset['SN_Monitor']); ?>" required>

    <label>IP Address:</label>
    <input type="text" name="ipAddress" value="<?= htmlspecialchars($aset['ipAddress']); ?>" required>

    <button type="submit" onclick="return confirm('Save changes to this asset?')">Update</button>
    <a href="admin_asetlist.php?deptID=<?= htmlspecialchars($deptID); ?>">Cancel</a>
</form>

<script>
// Populate Seksyen based on Cawangan
const cawanganSelect = document.querySelector('select[name="cawanganID"]');
const seksyenSelect = document.querySelector('select[name="seksyenID"]');
const preSelectedSeksyen = <?= json_encode($aset['seksyenID']); ?>;

function loadSeksyen(cawanganID) {
    seksyenSelect.innerHTML = '<option value="">-- Select Seksyen --</option>';
    if (!cawanganID) return;

    fetch('get_seksyen.php?cawanganID=' + cawanganID)
        .then(res => res.json())
        .then(data => {
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.seksyenID;
                opt.textContent = s.seksyenName;
                if (preSelectedSeksyen && preSelectedSeksyen == s.seksyenID) {
                    opt.selected = true;
                }
                seksyenSelect.appendChild(opt);
            });
        });
}

// Initial load
if (cawanganSelect.value) {
    loadSeksyen(cawanganSelect.value);
}

// On change
cawanganSelect.addEventListener('change', function() {
    loadSeksyen(this.value);
});
</script>

</body>
</html>

<?php
session_start();
include 'db_connect.php';

// Restrict to Admin only
if (!isset($_SESSION['userID']) || $_SESSION['levelID'] != 1001) {
    header("Location: login.php");
    exit;
}

$userID = $_GET['id'] ?? null;
if (!$userID) {
    header("Location: manage_users.php");
    exit;
}

// Fetch user info
$stmt = $pdo->prepare("
    SELECT u.userID, u.userName, u.userIC, u.levelID, d.deptID, d.deptName, c.cawanganID, c.cawanganName
    FROM users u
    LEFT JOIN department d ON u.deptID = d.deptID
    LEFT JOIN cawangan c ON u.cawanganID = c.cawanganID
    WHERE u.userID = :userID
");
$stmt->execute([':userID' => $userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<script>alert('User not found.');window.location.href='manage_users.php';</script>";
    exit;
}

// Fetch departments for dropdown
$departments = $pdo->query("SELECT deptID, deptName FROM department ORDER BY deptName ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch cawangan for the user's department
$cawangans = [];
if ($user['deptID']) {
    $stmtCaw = $pdo->prepare("SELECT cawanganID, cawanganName FROM cawangan WHERE deptID = :deptID ORDER BY cawanganName ASC");
    $stmtCaw->execute([':deptID' => $user['deptID']]);
    $cawangans = $stmtCaw->fetchAll(PDO::FETCH_ASSOC);
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmtUpdate = $pdo->prepare("
        UPDATE users SET 
            userName = :userName,
            deptID = :deptID,
            cawanganID = :cawanganID,
            levelID = :levelID
        WHERE userID = :userID
    ");
    $stmtUpdate->execute([
        ':userName' => $_POST['userName'],
        ':deptID' => $_POST['deptID'] ?: null,
        ':cawanganID' => $_POST['cawanganID'] ?: null,
        ':levelID' => $_POST['levelID'],
        ':userID' => $userID
    ]);

    echo "<script>alert('✅ User updated successfully!');window.location.href='manage_users.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; }
label { display:block; margin-top:10px; font-weight:bold; }
input, select { width:100%; padding:6px; margin-top:3px; }
button { margin-top:15px; padding:8px 16px; }
</style>

<script>
function loadCawangan() {
    const deptID = document.getElementById('deptID').value;
    const cawSelect = document.getElementById('cawanganID');

    cawSelect.innerHTML = '<option>Loading...</option>';

    if (!deptID) {
        cawSelect.innerHTML = '<option value="">-- Select Department First --</option>';
        return;
    }

    fetch('get_cawangan.php?deptID=' + deptID)
        .then(res => res.json())
        .then(data => {
            cawSelect.innerHTML = '<option value="">-- Select Cawangan --</option>';
            data.forEach(c => {
                let sel = c.cawanganID == '<?= $user['cawanganID'] ?>' ? 'selected' : '';
                cawSelect.innerHTML += `<option value="${c.cawanganID}" ${sel}>${c.cawanganName}</option>`;
            });
        });
}
</script>
</head>
<body>

<h2>Edit User – <?= htmlspecialchars($user['userName']); ?></h2>
<form method="POST">
    <label>Full Name:</label>
    <input type="text" name="userName" value="<?= htmlspecialchars($user['userName']); ?>" required>

    <label>Role:</label>
    <select name="levelID" required>
        <option value="1001" <?= $user['levelID']==1001?'selected':''; ?>>Admin</option>
        <option value="2001" <?= $user['levelID']==2001?'selected':''; ?>>Urusetia</option>
    </select>

    <label>Department:</label>
    <select name="deptID" id="deptID" onchange="loadCawangan()">
        <option value="">-- Select Department --</option>
        <?php foreach ($departments as $d): ?>
            <option value="<?= $d['deptID']; ?>" <?= ($user['deptID']==$d['deptID'])?'selected':''; ?>>
                <?= htmlspecialchars($d['deptName']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Cawangan:</label>
    <select name="cawanganID" id="cawanganID">
        <option value="">-- Select Cawangan --</option>
        <?php foreach ($cawangans as $c): ?>
            <option value="<?= $c['cawanganID']; ?>" <?= ($c['cawanganID']==$user['cawanganID'])?'selected':''; ?>>
                <?= htmlspecialchars($c['cawanganName']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Update User</button>
    <a href="manage_users.php">Cancel</a>
</form>

<script>
// Load cawangan dynamically on page load if a department is selected
document.addEventListener('DOMContentLoaded', () => {
    if(document.getElementById('deptID').value) {
        loadCawangan();
    }
});
</script>

</body>
</html>

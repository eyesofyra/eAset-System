<?php
session_start();
include 'db_connect.php';

// Only Urusetia can access
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

// Get Aset ID
$asetID = $_GET['id'] ?? null;
if (!$asetID) {
    header("Location: urusetia_dashboard.php");
    exit;
}

// Fetch aset belonging to this Urusetia's department
$stmt = $pdo->prepare("
    SELECT aset.*, 
           department.deptName,
           cawangan.cawanganName,
           seksyen.seksyenName
    FROM aset
    LEFT JOIN department ON aset.deptID = department.deptID
    LEFT JOIN cawangan ON aset.cawanganID = cawangan.cawanganID
    LEFT JOIN seksyen ON aset.seksyenID = seksyen.seksyenID
    WHERE asetID = :id AND aset.deptID = :deptID
");
$stmt->execute([
    ':id' => $asetID,
    ':deptID' => $_SESSION['deptID']
]);
$aset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aset) {
    echo "<script>alert('Unauthorized access or record not found.');window.location.href='urusetia_dashboard.php';</script>";
    exit;
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST['namaPengguna']);
    $jawatan = trim($_POST['jawatan']);

    if ($nama === "" || $jawatan === "") {
        echo "<script>alert('Both fields are required.');window.history.back();</script>";
        exit;
    }

    $update = $pdo->prepare("
        UPDATE aset 
        SET namaPengguna = :nama,
            jawatan = :jawatan,
            updatedBy = :updatedBy,
            updatedAt = NOW()
        WHERE asetID = :id AND deptID = :deptID
    ");
    $update->execute([
        ':nama' => $nama,
        ':jawatan' => $jawatan,
        ':updatedBy' => $_SESSION['userID'],
        ':id' => $asetID,
        ':deptID' => $_SESSION['deptID']
    ]);

    echo "<script>alert('Data updated successfully.');window.location.href='urusetia_dashboard.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Aset (Urusetia)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9fafb;
            padding: 40px;
        }
        h2 {
            color: #333;
        }
        form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        input[readonly] {
            background-color: #eee;
            color: #555;
        }
        button {
            margin-top: 15px;
            background: #007BFF;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        a {
            margin-left: 10px;
            color: #555;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>Edit Aset – <?= htmlspecialchars($aset['deptName']); ?></h2>
<form method="POST">
    <label>Bahagian:</label>
    <input type="text" value="<?= htmlspecialchars($aset['deptName']); ?>" readonly>

    <label>Cawangan:</label>
    <input type="text" value="<?= htmlspecialchars($aset['cawanganName'] ?? '-'); ?>" readonly>

    <label>Seksyen:</label>
    <input type="text" value="<?= htmlspecialchars($aset['seksyenName'] ?? '-'); ?>" readonly>

    <label>Nama Pengguna:</label>
    <input type="text" name="namaPengguna" value="<?= htmlspecialchars($aset['namaPengguna']); ?>" required>

    <label>Jawatan:</label>
    <input type="text" name="jawatan" value="<?= htmlspecialchars($aset['jawatan']); ?>" required>

    <button type="submit" onclick="return confirm('Save changes?')">Update</button>
    <a href="urusetia_dashboard.php">Cancel</a>
</form>

</body>
</html>

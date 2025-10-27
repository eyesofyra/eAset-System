<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['levelID']) || $_SESSION['levelID'] != 1) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "<script>alert('No Aset selected!'); window.location='admin-dashboard.php';</script>";
    exit();
}

$asetID = $_GET['id'];

// Fetch current aset data
$sql = "SELECT a.*, m.modelName, d.deptName 
        FROM aset a
        LEFT JOIN model m ON a.modelID = m.modelID
        LEFT JOIN department d ON a.deptID = d.deptID
        WHERE a.asetID = '$asetID'";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<script>alert('Aset not found!'); window.location='admin-dashboard.php';</script>";
    exit();
}

$aset = $result->fetch_assoc();

// Fetch all models (for dropdown)
$modelQuery = $conn->query("SELECT * FROM model ORDER BY modelName ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Aset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0d1b2a;
            color: white;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            background-color: #1b263b;
            border: none;
            border-radius: 10px;
        }
        .btn-primary {
            background-color: #1e40af;
            border: none;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        label {
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card p-4">
        <h3 class="text-center mb-4">Edit Aset Information</h3>

        <form action="update-aset.php" method="POST">
            <input type="hidden" name="asetID" value="<?= $aset['asetID'] ?>">

            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($aset['deptName']) ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($aset['location']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Bahagian/Cawangan/Seksyen</label>
                <input type="text" class="form-control" name="bahagian" value="<?= htmlspecialchars($aset['Bahagian/Cawangan/Seksyen']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Pengguna</label>
                <input type="text" class="form-control" name="namaPengguna" value="<?= htmlspecialchars($aset['namaPengguna']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Jawatan</label>
                <input type="text" class="form-control" name="jawatan" value="<?= htmlspecialchars($aset['jawatan']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Model</label>
                <select class="form-select" name="modelID" required>
                    <option value="">-- Select Model --</option>
                    <?php while ($m = $modelQuery->fetch_assoc()) { ?>
                        <option value="<?= $m['modelID'] ?>" <?= ($m['modelID'] == $aset['modelID']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['modelName']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">S/N Komputer</label>
                <input type="text" class="form-control" name="SN_Komputer" value="<?= htmlspecialchars($aset['SN_Komputer']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">S/N Monitor</label>
                <input type="text" class="form-control" name="SN_Monitor" value="<?= htmlspecialchars($aset['SN_Monitor']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">IP Address</label>
                <input type="text" class="form-control" name="ipAddress" value="<?= htmlspecialchars($aset['ipAddress']) ?>" required>
            </div>

            <div class="d-flex justify-content-between">
                <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
                <button type="submit" class="btn btn-primary">Update Aset</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>

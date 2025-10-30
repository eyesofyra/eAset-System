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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - ICT Aset</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
        .tab {
            background-color: #007BFF;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            transition: 0.2s;
        }
        .tab:hover { background-color: #0056b3; }
        .section-title { margin-top: 25px; font-weight: bold; color: #333; }
        .top-menu { margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Welcome, Admin <?= htmlspecialchars($_SESSION['userName']); ?></h2>
    <div class="top-menu">
        <a href="change_password.php">Change Password</a> |
        <a href="logout.php">Logout</a> |
        <a href="add_aset.php">➕ Add New Aset</a>
        <a href="manage_users.php" class="button">👥 Manage Users</a>
    </div>
    <hr>

    <h3>Select a Department to View Aset List</h3>

    <div class="section-title">1️⃣ BAHAGIAN PENGUATKUASAAN</div>
    <div class="tabs">
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('WPK'); ?>">PENGUATKUASAAN</a>
    </div>

    <div class="section-title">2️⃣ BAHAGIAN PEMATUHAN</div>
    <div class="tabs">
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('W20(P)'); ?>">PEMATUHAN</a>
    </div>

    <div class="section-title">3️⃣ BAHAGIAN CUKAI DALAM NEGERI</div>
    <div class="tabs">
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('W24(C)'); ?>">CUKAI DALAM NEGERI</a>
    </div>

    <div class="section-title">4️⃣ BAHAGIAN PERKHIDMATAN TEKNIK</div>
    <div class="tabs">
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('WPE'); ?>">PERKHIDMATAN TEKNIK</a>
    </div>

    <div class="section-title">5️⃣ BAHAGIAN PERKASTAMAN</div>
    <div class="tabs">
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('W20(I)'); ?>">CAWANGAN IMPORT & ZON BEBAS</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('W21'); ?>">CAWANGAN PEMERIKSAAN PENUMPANG 1</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('W12'); ?>">CAWANGAN PEMERIKSAAN PENUMPANG 2</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('W20(E)'); ?>">CAWANGAN EKSPORT</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('W25'); ?>">PUSAT MEL & KURIER</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('WQ6'); ?>">CAWANGAN PERINDUSTRIAN</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('W99'); ?>">KEDAI BEBAS CUKAI 1</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('W3R'); ?>">KEDAI BEBAS CUKAI 2</a>
    </div>

    <div class="section-title">6️⃣ BAHAGIAN KHIDMAT PENGURUSAN SUMBER MANUSIA</div>
    <div class="tabs">
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('WSS'); ?>">CAWANGAN SUMBER MANUSIA</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('WAS'); ?>">CAWANGAN ASET</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('WCT'); ?>">CAWANGAN TEKNOLOGI MAKLUMAT</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('WKE'); ?>">CAWANGAN KEWANGAN</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('WPT'); ?>">CAWANGAN PENTADBIRAN AM</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('WLA'); ?>">CAWANGAN LATIHAN</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('WPN'); ?>">CAWANGAN PEROLEHAN</a>
        <a class="tab" href="admin_asetlist.php?deptID=<?= urlencode('WKO'); ?>">CAWANGAN KORPORAT</a>
    </div>
</body>
</html>

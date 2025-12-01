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
    echo "<script>alert('Session expired. Please login again.');window.location.href='login.php';</script>";
    exit;
}
$_SESSION['last_activity'] = time();

$deptID = $_SESSION['deptID'];
$cawanganID = $_SESSION['cawanganID'];

// Fetch Department & Cawangan names
$stmtNames = $pdo->prepare("
    SELECT d.deptName, c.cawanganName
    FROM department d
    LEFT JOIN cawangan c ON c.cawanganID = :cawanganID
    WHERE d.deptID = :deptID
");
$stmtNames->execute([':deptID' => $deptID, ':cawanganID' => $cawanganID]);
$info = $stmtNames->fetch(PDO::FETCH_ASSOC);
$deptName = $info['deptName'] ?? 'Unknown Department';
$cawanganName = $info['cawanganName'] ?? 'Unknown Cawangan';

// Fetch only assets belonging to their department and cawangan
$stmt = $pdo->prepare("
    SELECT aset.*, 
           department.deptName, 
           cawangan.cawanganName, 
           seksyen.seksyenName, 
           model.modelName
    FROM aset
    LEFT JOIN department ON aset.deptID = department.deptID
    LEFT JOIN cawangan ON aset.cawanganID = cawangan.cawanganID
    LEFT JOIN seksyen ON aset.seksyenID = seksyen.seksyenID
    LEFT JOIN model ON aset.modelID = model.modelID
    WHERE aset.deptID = :deptID AND aset.cawanganID = :cawanganID
    ORDER BY aset.asetID ASC
");
$stmt->execute([':deptID' => $deptID, ':cawanganID' => $cawanganID]);
$asets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Urusetia Dashboard - ICT Aset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --deep-navy: #0a1a2f;
            --gold: #d4af37;
            --navy-light: #11243d;
            --sky-blue: #e6f2ff;
        }

        body { 
            background: var(--sky-blue);
            background: linear-gradient(135deg, #e6f2ff 0%, #cce5ff 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        /* NAVBAR */
        .navbar {
            background: var(--deep-navy) !important;
            padding: 0.5rem 1rem;
            position: relative;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand, .nav-link { 
            color: var(--gold) !important; 
            font-weight: 600; 
        }
        .nav-link:hover { 
            color: #fff !important; 
        }
        
        /* LOGO STYLING - Centered and Larger */
        .navbar-logo {
            height: 50px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            transition: transform 0.3s ease;
        }
        
        .navbar-logo:hover {
            transform: translateX(-50%) scale(1.05);
        }
        
        /* NAVBAR FLEX LAYOUT */
        .navbar-content {
            display: flex;
            align-items: center;
            width: 100%;
            position: relative;
        }
        .navbar-brand-section {
            display: flex;
            align-items: center;
        }
        .navbar-nav-section {
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        /* PAGE CONTENT */
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            border-left: 5px solid var(--gold);
        }
        
        h2 { 
            color: var(--deep-navy); 
            margin-bottom: 5px;
        }
        
        .user-info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--gold);
        }
        
        .user-info-label {
            color: var(--deep-navy);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-info-value {
            color: #6c757d;
            font-size: 1.1rem;
        }

        /* TABLE STYLING */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        th {
            background-color: var(--deep-navy);
            color: var(--gold);
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--gold);
        }
        
        td {
            padding: 10px 15px;
            border-bottom: 1px solid #eaeaea;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        tr:hover {
            background-color: #f0f4f8;
        }
        
        .action-links a {
            color: var(--deep-navy);
            text-decoration: none;
            font-weight: 500;
        }
        
        .action-links a:hover {
            color: var(--gold);
            text-decoration: underline;
        }

        /* BUTTON STYLING */
        .btn-primary {
            background-color: var(--deep-navy);
            border-color: var(--deep-navy);
            color: white;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 5px;
        }
        
        .btn-primary:hover {
            background-color: var(--navy-light);
            border-color: var(--navy-light);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--deep-navy);
            color: var(--deep-navy);
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 5px;
        }
        
        .btn-outline:hover {
            background-color: var(--deep-navy);
            color: white;
        }

        .footer { 
            color: #555; 
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
            border-top: 4px solid var(--gold);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--deep-navy);
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar-logo {
                height: 40px;
                position: static;
                transform: none;
                margin: 0 auto;
                display: block;
            }
            
            .navbar-brand {
                display: none;
            }
            
            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <div class="navbar-content">
                <div class="navbar-brand-section">
                    <a class="navbar-brand" href="urusetia_dashboard.php">ICT Aset - Urusetia</a>
                </div>
                
                <!-- Centered Logo -->
                <img src="logoKastam.jpg" alt="Kastam Easet Logo" class="navbar-logo">
                
                <div class="navbar-nav-section">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item"><a class="nav-link" href="change_password.php">Change Password</a></li>
                            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Page Header -->
        <div class="page-header">
            <h2>Welcome, Urusetia <?= htmlspecialchars($_SESSION['userName']); ?></h2>
            <p class="text-muted">Manage assets for your assigned department and cawangan</p>
            
            <!-- User Information -->
            <div class="user-info-card">
                <div class="row">
                    <div class="col-md-6">
                        <div class="user-info-label">Department:</div>
                        <div class="user-info-value"><?= htmlspecialchars($deptName); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="user-info-label">Cawangan:</div>
                        <div class="user-info-value"><?= htmlspecialchars($cawanganName); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="change_password.php" class="btn btn-outline">Change Password</a>
                <a href="logout.php" class="btn btn-primary">Logout / Switch Account</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= count($asets); ?></div>
                    <div class="stats-label">Total Assets</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= htmlspecialchars($deptName); ?></div>
                    <div class="stats-label">Assigned Department</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= htmlspecialchars($cawanganName); ?></div>
                    <div class="stats-label">Assigned Cawangan</div>
                </div>
            </div>
        </div>

        <!-- Asset List -->
        <div class="table-container">
            <div style="padding: 20px; background: var(--navy-light); color: white;">
                <h4 style="margin: 0; color: var(--gold);">Asset List for <?= htmlspecialchars($cawanganName); ?></h4>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>Lokasi</th>
                        <th>Bahagian</th>
                        <th>Cawangan</th>
                        <th>Seksyen</th>
                        <th>Nama Pengguna</th>
                        <th>Jawatan</th>
                        <th>Model</th>
                        <th>SN Komputer</th>
                        <th>SN Monitor</th>
                        <th>IP Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($asets)): ?>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 30px; color: #6c757d;">
                            No assets found for this Cawangan.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $no = 1; foreach ($asets as $row): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['location']); ?></td>
                        <td><?= htmlspecialchars($row['deptName']); ?></td>
                        <td><?= htmlspecialchars($row['cawanganName']); ?></td>
                        <td><?= htmlspecialchars($row['seksyenName'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($row['namaPengguna']); ?></td>
                        <td><?= htmlspecialchars($row['jawatan']); ?></td>
                        <td><?= htmlspecialchars($row['modelName']); ?></td>
                        <td><?= htmlspecialchars($row['SN_Komputer']); ?></td>
                        <td><?= htmlspecialchars($row['SN_Monitor']); ?></td>
                        <td><?= htmlspecialchars($row['ipAddress']); ?></td>
                        <td class="action-links">
                            <a href="edit_aset_urusetia.php?id=<?= $row['asetID']; ?>">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-5 footer">
            ICT Aset Management System © <?= date("Y"); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

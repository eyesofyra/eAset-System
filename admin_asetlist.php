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

// Get selected department
$deptID = $_GET['deptID'] ?? null;
if (!$deptID) {
    header("Location: admin_dashboard.php");
    exit;
}

// Fetch department info
$stmtDept = $pdo->prepare("SELECT deptName FROM department WHERE deptID = :deptID");
$stmtDept->execute([':deptID' => $deptID]);
$dept = $stmtDept->fetch(PDO::FETCH_ASSOC);
$deptName = $dept['deptName'] ?? "Unknown Department";

// Fetch all cawangan under this department
$stmtCaw = $pdo->prepare("SELECT cawanganID, cawanganName FROM cawangan WHERE deptID = :deptID ORDER BY cawanganName ASC");
$stmtCaw->execute([':deptID' => $deptID]);
$cawangans = $stmtCaw->fetchAll(PDO::FETCH_ASSOC);

// Search & filter
$search = $_GET['search'] ?? '';
$cawanganFilter = $_GET['cawanganID'] ?? '';
$updatedByFilter = $_GET['updatedBy'] ?? '';

// Fetch list of all users (for filter)
$stmtUsers = $pdo->query("SELECT DISTINCT userID, userName FROM users ORDER BY userName ASC");
$usersList = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Build asset query
$query = "
    SELECT aset.*, 
           department.deptName, 
           cawangan.cawanganName, 
           seksyen.seksyenName, 
           model.modelName, 
           users.userName AS updatedByName
    FROM aset
    LEFT JOIN department ON aset.deptID = department.deptID
    LEFT JOIN cawangan ON aset.cawanganID = cawangan.cawanganID
    LEFT JOIN seksyen ON aset.seksyenID = seksyen.seksyenID
    LEFT JOIN model ON aset.modelID = model.modelID
    LEFT JOIN users ON aset.updatedBy = users.userID
    WHERE aset.deptID = :deptID
";

$params = [':deptID' => $deptID];

// Apply filters
if ($cawanganFilter === 'none') {
    // Show assets assigned to a CawanganID but the cawangan is missing in DB
    $query .= " AND aset.cawanganID IS NOT NULL AND cawangan.cawanganID IS NULL";
} elseif (!empty($cawanganFilter)) {
    $query .= " AND aset.cawanganID = :cawanganID";
    $params[':cawanganID'] = $cawanganFilter;
}

if (!empty($search)) {
    $query .= " AND (aset.namaPengguna LIKE :search OR aset.ipAddress LIKE :search OR seksyen.seksyenName LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($updatedByFilter)) {
    $query .= " AND aset.updatedBy = :updatedBy";
    $params[':updatedBy'] = $updatedByFilter;
}

$query .= " ORDER BY cawangan.cawanganName ASC, seksyen.seksyenName ASC, aset.asetID ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$asets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aset List – <?= htmlspecialchars($deptName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --deep-navy: #0a1a2f;
            --gold: #d4af37;
            --navy-light: #11243d;
            --navy-lighter: #1a3357;
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
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 15px;
        }
        
        .breadcrumb a {
            color: var(--gold);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* FILTER SECTION */
        .filters-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .filter-label {
            color: var(--deep-navy);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 10px 12px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        }
        
        .btn-primary {
            background-color: var(--deep-navy);
            border-color: var(--deep-navy);
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
        }
        
        .btn-primary:hover {
            background-color: var(--navy-light);
            border-color: var(--navy-light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--deep-navy);
            color: var(--deep-navy);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
        }
        
        .btn-outline:hover {
            background-color: var(--deep-navy);
            color: white;
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
            margin-right: 10px;
            font-weight: 500;
        }
        
        .action-links a:hover {
            color: var(--gold);
            text-decoration: underline;
        }

        /* CAWANGAN HEADER */
        .cawangan-header {
            background-color: var(--navy-lighter);
            color: white;
            font-weight: bold;
            padding: 15px 20px;
            margin-top: 30px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cawangan-header a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        
        .cawangan-header a:hover {
            text-decoration: underline;
        }

        .footer { 
            color: #555; 
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.1);
            text-align: center;
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
            
            .cawangan-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <div class="navbar-content">
                <div class="navbar-brand-section">
                    <a class="navbar-brand" href="admin_dashboard.php">ICT Aset - Admin</a>
                </div>
                
                <!-- Centered Logo -->
                <img src="logoKastam.jpg" alt="Kastam Easet Logo" class="navbar-logo">
                
                <div class="navbar-nav-section">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
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
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Departments</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($deptName); ?></li>
                </ol>
            </nav>
            <h2>📋 Aset List – <?= htmlspecialchars($deptName); ?></h2>
            <p class="text-muted">Manage assets for this department</p>
            
            <div class="mt-3">
                <a href="admin_dashboard.php" class="btn btn-outline">⬅️ Back to Departments</a>
                <a href="add_aset.php?deptID=<?= htmlspecialchars($deptID); ?>" class="btn btn-primary">➕ Add New Aset</a>
            </div>
        </div>

        <!-- Filter section -->
        <div class="filters-card">
            <h5 class="filter-label">Filter Assets</h5>
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="deptID" value="<?= htmlspecialchars($deptID); ?>">
                
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" placeholder="User, IP, or section..." value="<?= htmlspecialchars($search); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Cawangan</label>
                    <select class="form-select" name="cawanganID">
                        <option value="">-- All Cawangan --</option>
                        <?php foreach ($cawangans as $c): ?>
                            <option value="<?= htmlspecialchars($c['cawanganID']); ?>" <?= ($cawanganFilter == $c['cawanganID']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($c['cawanganName']); ?>
                            </option>
                        <?php endforeach; ?>
                        <?php
                        // Only show "None" if there are assets with missing cawangan
                        $stmtNone = $pdo->prepare("SELECT COUNT(*) FROM aset WHERE deptID = :deptID AND cawanganID IS NOT NULL AND cawanganID NOT IN (SELECT cawanganID FROM cawangan)");
                        $stmtNone->execute([':deptID' => $deptID]);
                        if ($stmtNone->fetchColumn() > 0):
                        ?>
                            <option value="none" <?= ($cawanganFilter === 'none') ? 'selected' : ''; ?>>None</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Updated By</label>
                    <select class="form-select" name="updatedBy">
                        <option value="">-- All Users --</option>
                        <?php foreach ($usersList as $user): ?>
                            <option value="<?= htmlspecialchars($user['userID']); ?>" <?= ($updatedByFilter == $user['userID']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($user['userName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    <a href="admin_asetlist.php?deptID=<?= htmlspecialchars($deptID); ?>" class="btn btn-outline mt-2 w-100">Clear Filters</a>
                </div>
            </form>
        </div>

        <!-- Assets List -->
        <?php
        $currentCawangan = '';
        $no = 1;
        foreach ($asets as $row):
            if ($row['cawanganName'] !== $currentCawangan):
                if ($currentCawangan !== '') echo "</table></div>";
                $currentCawangan = $row['cawanganName'] ?? 'None';
                $addLink = "add_aset.php?deptID=" . htmlspecialchars($deptID) . "&cawanganID=" . htmlspecialchars($row['cawanganID']);
        ?>
            <div class="table-container">
                <div class="cawangan-header">
                    <span><?= htmlspecialchars($currentCawangan); ?></span>
                    <a href="<?= $addLink; ?>">➕ Add Aset to this Cawangan</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Bil</th>
                            <th>Seksyen</th>
                            <th>Location</th>
                            <th>Nama Pengguna</th>
                            <th>Jawatan</th>
                            <th>Model</th>
                            <th>SN Komputer</th>
                            <th>SN Monitor</th>
                            <th>IP Address</th>
                            <th>Updated By</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
        <?php endif; ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['seksyenName'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['location']); ?></td>
                            <td><?= htmlspecialchars($row['namaPengguna']); ?></td>
                            <td><?= htmlspecialchars($row['jawatan']); ?></td>
                            <td><?= htmlspecialchars($row['modelName']); ?></td>
                            <td><?= htmlspecialchars($row['SN_Komputer']); ?></td>
                            <td><?= htmlspecialchars($row['SN_Monitor']); ?></td>
                            <td><?= htmlspecialchars($row['ipAddress']); ?></td>
                            <td><?= htmlspecialchars($row['updatedByName'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['updatedAt'] ?? '-'); ?></td>
                            <td class="action-links">
                                <a href="edit_aset.php?id=<?= $row['asetID']; ?>">Edit</a> |
                                <a href="delete_aset.php?id=<?= $row['asetID']; ?>" onclick="return confirm('Delete this record?')">Delete</a>
                            </td>
                        </tr>
        <?php endforeach; ?>

        <?php if (!empty($asets)) echo "</tbody></table></div>"; ?>
        
        <?php if (empty($asets)): ?>
            <div class="text-center py-5">
                <h4 class="text-muted">No assets found</h4>
                <p class="text-muted">Try adjusting your filters or add new assets</p>
                <a href="add_aset.php?deptID=<?= htmlspecialchars($deptID); ?>" class="btn btn-primary">➕ Add New Aset</a>
            </div>
        <?php endif; ?>

        <div class="text-center mt-5 footer">
            ICT Aset Management System © <?= date('Y'); ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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

// Get deptID and optional cawanganID from URL
$deptID = $_GET['deptID'] ?? null;
$cawanganID = $_GET['cawanganID'] ?? null;

if (!$deptID) {
    echo "<script>alert('Invalid access.'); window.location.href='admin_dashboard.php';</script>";
    exit;
}

// Fetch department info
$stmt = $pdo->prepare("SELECT deptName FROM department WHERE deptID = :deptID");
$stmt->execute([':deptID' => $deptID]);
$dept = $stmt->fetch(PDO::FETCH_ASSOC);
$deptName = $dept['deptName'] ?? 'Unknown Department';

// Fetch all cawangan for dropdown
$stmtCaw = $pdo->prepare("SELECT cawanganID, cawanganName FROM cawangan WHERE deptID = :deptID ORDER BY cawanganName ASC");
$stmtCaw->execute([':deptID' => $deptID]);
$cawangans = $stmtCaw->fetchAll(PDO::FETCH_ASSOC);

// Fetch all models
$stmtModel = $pdo->query("SELECT modelID, modelName FROM model ORDER BY modelName ASC");
$models = $stmtModel->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $insert = $pdo->prepare("
        INSERT INTO aset (deptID, cawanganID, seksyenID, location, namaPengguna, jawatan, modelID, SN_Komputer, SN_Monitor, ipAddress, updatedBy, updatedAt)
        VALUES (:deptID, :cawanganID, :seksyenID, :location, :namaPengguna, :jawatan, :modelID, :SN_Komputer, :SN_Monitor, :ipAddress, :updatedBy, NOW())
    ");
    $insert->execute([
        ':deptID' => $deptID,
        ':cawanganID' => $_POST['cawanganID'] ?: null,
        ':seksyenID' => $_POST['seksyenID'] ?: null,
        ':location' => $_POST['location'],
        ':namaPengguna' => $_POST['namaPengguna'],
        ':jawatan' => $_POST['jawatan'],
        ':modelID' => $_POST['modelID'],
        ':SN_Komputer' => $_POST['SN_Komputer'],
        ':SN_Monitor' => $_POST['SN_Monitor'],
        ':ipAddress' => $_POST['ipAddress'],
        ':updatedBy' => $_SESSION['userID']
    ]);
    echo "<script>alert('✅ New asset added successfully!');window.location.href='admin_asetlist.php?deptID=$deptID';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Aset – <?= htmlspecialchars($deptName); ?></title>
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

    /* FORM STYLING */
    .form-container {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .form-label {
        color: var(--deep-navy);
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 14px;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--gold);
        box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
    }
    
    .form-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin: 20px 0;
        border-left: 4px solid var(--gold);
    }
    
    .form-section-title {
        color: var(--deep-navy);
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 1.1rem;
    }

    /* BUTTON STYLING */
    .btn-primary {
        background-color: var(--deep-navy);
        border-color: var(--deep-navy);
        color: white;
        font-weight: 600;
        padding: 10px 25px;
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
        padding: 10px 25px;
        border-radius: 8px;
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
        
        .form-container {
            padding: 20px;
        }
        
        .page-header {
            padding: 20px;
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
                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="admin_asetlist.php?deptID=<?= htmlspecialchars($deptID); ?>"><?= htmlspecialchars($deptName); ?></a></li>
                    <li class="breadcrumb-item active">Add New Aset</li>
                </ol>
            </nav>
            <h2>➕ Add New Aset</h2>
            <p class="text-muted">Department: <?= htmlspecialchars($deptName); ?></p>
            
            <div class="mt-3">
                <a href="admin_asetlist.php?deptID=<?= htmlspecialchars($deptID); ?>" class="btn btn-outline">⬅️ Back to Aset List</a>
            </div>
        </div>

        <!-- Add Aset Form -->
        <div class="form-container">
            <form method="POST">
                <!-- Department & Location Section -->
                <div class="form-section">
                    <div class="form-section-title">Department & Location</div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bahagian:</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($deptName); ?>" disabled>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cawangan:</label>
                            <select class="form-select" name="cawanganID" required>
                                <option value="">-- Select Cawangan --</option>
                                <?php foreach ($cawangans as $c): ?>
                                    <option value="<?= htmlspecialchars($c['cawanganID']); ?>" <?= ($cawanganID == $c['cawanganID']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($c['cawanganName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Seksyen:</label>
                            <select class="form-select" name="seksyenID" required>
                                <option value="">-- Select Seksyen --</option>
                                <!-- Options will be dynamically populated by JS -->
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location:</label>
                            <input type="text" class="form-control" name="location" required placeholder="Enter location">
                        </div>
                    </div>
                </div>

                <!-- User Information Section -->
                <div class="form-section">
                    <div class="form-section-title">User Information</div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Pengguna:</label>
                            <input type="text" class="form-control" name="namaPengguna" required placeholder="Enter user name">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jawatan:</label>
                            <input type="text" class="form-control" name="jawatan" required placeholder="Enter position">
                        </div>
                    </div>
                </div>

                <!-- Asset Details Section -->
                <div class="form-section">
                    <div class="form-section-title">Asset Details</div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Model:</label>
                            <select class="form-select" name="modelID" required>
                                <option value="">-- Select Model --</option>
                                <?php foreach ($models as $m): ?>
                                    <option value="<?= htmlspecialchars($m['modelID']); ?>"><?= htmlspecialchars($m['modelName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">IP Address:</label>
                            <input type="text" class="form-control" name="ipAddress" required placeholder="Enter IP address">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SN Komputer:</label>
                            <input type="text" class="form-control" name="SN_Komputer" required placeholder="Enter computer serial number">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SN Monitor:</label>
                            <input type="text" class="form-control" name="SN_Monitor" required placeholder="Enter monitor serial number">
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="admin_asetlist.php?deptID=<?= htmlspecialchars($deptID); ?>" class="btn btn-outline me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Add this new asset?')">Add Aset</button>
                </div>
            </form>
        </div>

        <div class="text-center mt-5 footer">
            ICT Aset Management System © <?= date('Y'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Dynamically load Seksyen based on Cawangan selection
    const cawanganSelect = document.querySelector('select[name="cawanganID"]');
    const seksyenSelect = document.querySelector('select[name="seksyenID"]');

    function loadSeksyen(cawanganID) {
        seksyenSelect.innerHTML = '<option value="">-- Select Seksyen --</option>';
        seksyenSelect.disabled = true;
        
        if (!cawanganID) {
            seksyenSelect.disabled = false;
            return;
        }

        fetch('get_seksyen.php?cawanganID=' + cawanganID)
            .then(res => res.json())
            .then(data => {
                data.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.seksyenID;
                    opt.textContent = s.seksyenName;
                    seksyenSelect.appendChild(opt);
                });
                seksyenSelect.disabled = false;
            })
            .catch(error => {
                seksyenSelect.innerHTML = '<option value="">Error loading seksyen</option>';
                seksyenSelect.disabled = false;
            });
    }

    // Initial load if Cawangan preselected (from URL)
    if (cawanganSelect.value) {
        loadSeksyen(cawanganSelect.value);
    }

    cawanganSelect.addEventListener('change', function() {
        loadSeksyen(this.value);
    });
    </script>
</body>
</html>

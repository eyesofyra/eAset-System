<!-- Bootstrap Bubble Design with Deep Navy & Gold Theme -->
<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['userID']) || $_SESSION['levelID'] != 1001) {
    header("Location: login.php");
    exit;
}
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset(); session_destroy();
    echo "<script>alert('Session expired. Please login again.');window.location.href='login.php';</script>";
    exit;
}
$_SESSION['last_activity'] = time();
$stmt = $pdo->query("SELECT deptID, deptName FROM department ORDER BY deptName ASC");
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - ICT Aset</title>
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
        min-height: 100vh;
    }

    /* NAVBAR */
    .navbar {
        background: var(--deep-navy) !important;
        padding: 0.5rem 1rem;
        position: relative;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .navbar-brand, .nav-link { color: var(--gold) !important; font-weight:600; }
    .nav-link:hover { color:#fff !important; }
    
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

    /* BUBBLE DESIGN */
    .dept-bubble {
        background: var(--deep-navy);
        color: var(--gold);
        border: 3px solid var(--gold);
        border-radius: 50%;
        width: 170px;
        height: 170px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        transition: all 0.25s ease-in-out;
        margin: auto;
        position: relative;
        overflow: hidden;
    }
    
    .dept-bubble:hover {
        transform: scale(1.08);
        box-shadow: 0 6px 18px rgba(0,0,0,0.3);
        background: var(--navy-light);
    }
    
    .dept-bubble::before {
        content: '';
        position: absolute;
        top: -10px;
        left: -10px;
        right: -10px;
        bottom: -10px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .dept-bubble:hover::before {
        opacity: 1;
    }

    h2, h4 { color: var(--deep-navy); }
    
    .welcome-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        border-left: 5px solid var(--gold);
    }
    
    .footer { 
        color: #555; 
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid rgba(0,0,0,0.1);
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
        
        .dept-bubble {
            width: 140px;
            height: 140px;
            font-size: 14px;
        }
    }
    
    @media (max-width: 576px) {
        .dept-bubble {
            width: 120px;
            height: 120px;
            font-size: 13px;
        }
    }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <div class="navbar-content">
      <div class="navbar-brand-section">
        <a class="navbar-brand" href="#">ICT Aset - Admin</a>
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
    <!-- Welcome Section -->
    <div class="welcome-section">
        <h2 class="mb-3">Welcome, Admin <?= htmlspecialchars($_SESSION['userName']); ?></h2>
        <h4 class="text-secondary mb-4">Select a Department (Bahagian)</h4>
        <p class="text-muted">Total Departments: <?= count($departments); ?></p>
    </div>

    <!-- Department Bubbles -->
    <div class="row g-4">
        <?php foreach ($departments as $dept): ?>
            <?php
            $stmtCaw = $pdo->prepare("SELECT cawanganID FROM cawangan WHERE deptID = :deptID LIMIT 1");
            $stmtCaw->execute([':deptID' => $dept['deptID']]);
            $hasCawangan = $stmtCaw->fetch(PDO::FETCH_ASSOC);
            $link = "admin_asetlist.php?deptID=" . htmlspecialchars($dept['deptID']);
            ?>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="dept-bubble" onclick="location.href='<?= $link; ?>'">
                    <?= htmlspecialchars($dept['deptName']); ?>
                    <?php if (!$hasCawangan): ?>
                        <br><small style="font-weight: normal; color:#fff; font-size: 12px;">(No Cawangan)</small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-5 footer">ICT Aset Management System © <?= date('Y'); ?></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

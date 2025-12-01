<?php
session_start();
include 'db_connect.php';

// Ensure user logged in
if (!isset($_SESSION['userID'])) {
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

$userID = $_SESSION['userID'];
$userName = $_SESSION['userName'];
$levelID = $_SESSION['levelID'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('⚠️ New passwords do not match.');window.history.back();</script>";
        exit;
    }

    // Fetch existing password
    $stmt = $pdo->prepare("SELECT userPassword FROM users WHERE userID = :userID");
    $stmt->bindParam(':userID', $userID);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($oldPassword, $user['userPassword'])) {
        // Hash and update new password
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET userPassword = :newPass WHERE userID = :userID");
        $update->bindParam(':newPass', $hashed);
        $update->bindParam(':userID', $userID);
        $update->execute();

        echo "<script>alert('✅ Password updated successfully! Please log in again.');window.location.href='logout.php';</script>";
    } else {
        echo "<script>alert('❌ Old password incorrect.');window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password - ICT Aset</title>
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

        /* FORM STYLING */
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .form-label {
            color: var(--deep-navy);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 10px 12px;
        }
        
        .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        }
        
        .user-info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
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
        
        /* Password Requirements */
        .password-rules {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 5px;
            padding-left: 5px;
        }
        
        .password-feedback {
            font-size: 0.875rem;
            margin-top: 5px;
            padding-left: 5px;
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            background: #e9ecef;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .strength-weak { background: #dc3545; }
        .strength-fair { background: #fd7e14; }
        .strength-good { background: #ffc107; }
        .strength-strong { background: #28a745; }

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
                margin: 0 15px;
            }
        }
    </style>
    <script>
        function validatePassword() {
            const password = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmPassword').value;
            const feedback = document.getElementById('passwordFeedback');
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');
            
            // Password strength calculation
            let strength = 0;
            let feedbackText = '';
            
            if (password.length >= 8) strength += 25;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
            if (password.match(/\d/)) strength += 25;
            if (password.match(/[^a-zA-Z\d]/)) strength += 25;
            
            // Update strength bar
            strengthBar.style.width = strength + '%';
            
            // Update strength classes and text
            if (strength <= 25) {
                strengthBar.className = 'password-strength-bar strength-weak';
                strengthText.textContent = 'Weak';
                strengthText.className = 'password-feedback text-danger';
            } else if (strength <= 50) {
                strengthBar.className = 'password-strength-bar strength-fair';
                strengthText.textContent = 'Fair';
                strengthText.className = 'password-feedback text-warning';
            } else if (strength <= 75) {
                strengthBar.className = 'password-strength-bar strength-good';
                strengthText.textContent = 'Good';
                strengthText.className = 'password-feedback text-info';
            } else {
                strengthBar.className = 'password-strength-bar strength-strong';
                strengthText.textContent = 'Strong';
                strengthText.className = 'password-feedback text-success';
            }
            
            // Check password match
            if (password !== confirm && confirm !== '') {
                feedback.textContent = 'Passwords do not match!';
                feedback.className = 'password-feedback text-danger';
            } else {
                feedback.textContent = '';
            }
        }
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <div class="navbar-content">
                <div class="navbar-brand-section">
                    <a class="navbar-brand" href="<?= $levelID == 1001 ? 'admin_dashboard.php' : 'urusetia_dashboard.php'; ?>">ICT Aset</a>
                </div>
                
                <!-- Centered Logo -->
                <img src="logoKastam.jpg" alt="Kastam Easet Logo" class="navbar-logo">
                
                <div class="navbar-nav-section">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <?php if ($levelID == 1001): ?>
                                <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
                            <?php endif; ?>
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
                    <li class="breadcrumb-item"><a href="<?= $levelID == 1001 ? 'admin_dashboard.php' : 'urusetia_dashboard.php'; ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Change Password</li>
                </ol>
            </nav>
            <h2>🔒 Change Password</h2>
            <p class="text-muted">Update your account password</p>
            
            <!-- User Information -->
            <div class="user-info-card">
                <div class="user-info-label">Logged in as:</div>
                <div class="user-info-value"><?= htmlspecialchars($userName); ?> (<?= $levelID == 1001 ? 'Admin' : 'Urusetia'; ?>)</div>
            </div>
            
            <div class="mt-3">
                <a href="<?= $levelID == 1001 ? 'admin_dashboard.php' : 'urusetia_dashboard.php'; ?>" class="btn btn-outline">⬅️ Back to Dashboard</a>
            </div>
        </div>

        <!-- Password Change Form -->
        <div class="form-container">
            <form method="POST">
                <div class="mb-4">
                    <label for="oldPassword" class="form-label">Current Password:</label>
                    <input type="password" class="form-control" name="oldPassword" id="oldPassword" required placeholder="Enter your current password">
                </div>

                <div class="mb-4">
                    <label for="newPassword" class="form-label">New Password:</label>
                    <input type="password" class="form-control" name="newPassword" id="newPassword" required placeholder="Enter new password" onkeyup="validatePassword()">
                    <div class="password-rules">
                        🔒 Must be at least 8 characters and include uppercase, lowercase, number, and symbol.
                    </div>
                    
                    <!-- Password Strength Meter -->
                    <div class="password-strength mt-2">
                        <div id="passwordStrengthBar" class="password-strength-bar"></div>
                    </div>
                    <div id="passwordStrengthText" class="password-feedback"></div>
                </div>

                <div class="mb-4">
                    <label for="confirmPassword" class="form-label">Confirm New Password:</label>
                    <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" required placeholder="Confirm new password" onkeyup="validatePassword()">
                    <div id="passwordFeedback" class="password-feedback"></div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= $levelID == 1001 ? 'admin_dashboard.php' : 'urusetia_dashboard.php'; ?>" class="btn btn-outline me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>

        <div class="text-center mt-5 footer">
            ICT Aset Management System © <?= date('Y'); ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

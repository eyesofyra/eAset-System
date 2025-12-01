<?php
session_start();
include 'db_connect.php';

// Restrict to Admin only
if (!isset($_SESSION['userID']) || $_SESSION['levelID'] != 1001) {
    header("Location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userIC = $_POST['userIC'];
    $userName = $_POST['userName'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $deptID = $_POST['deptID'] ?? null;
    $cawanganID = $_POST['cawanganID'] ?? null;
    $role = $_POST['role'];
    $levelID = ($role === 'admin') ? 1001 : 2001;

    if ($password !== $confirm) {
        echo "<script>alert('Password and confirmation do not match.');</script>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (userIC, userName, userPassword, deptID, cawanganID, levelID)
            VALUES (:userIC, :userName, :userPassword, :deptID, :cawanganID, :levelID)
        ");
        $stmt->execute([
            ':userIC' => $userIC,
            ':userName' => $userName,
            ':userPassword' => $hashedPassword,
            ':deptID' => $deptID,
            ':cawanganID' => $cawanganID,
            ':levelID' => $levelID
        ]);

        echo "<script>alert('✅ New user registered successfully!');window.location.href='manage_users.php';</script>";
        exit;
    }
}

// Fetch department list
$departments = $pdo->query("SELECT deptID, deptName FROM department ORDER BY deptName ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register New User - ICT Aset</title>
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
        }
        
        .password-requirements {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .role-description {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 5px;
            font-style: italic;
        }
    </style>

    <script>
        function loadCawangan() {
            const deptID = document.getElementById('deptID').value;
            const cawSelect = document.getElementById('cawanganID');

            cawSelect.innerHTML = '<option>Loading...</option>';
            cawSelect.disabled = true;

            if (!deptID) {
                cawSelect.innerHTML = '<option value="">-- Select Department First --</option>';
                cawSelect.disabled = false;
                return;
            }

            fetch('get_cawangan.php?deptID=' + deptID)
                .then(res => res.json())
                .then(data => {
                    cawSelect.innerHTML = '<option value="">-- Select Cawangan --</option>';
                    data.forEach(c => {
                        cawSelect.innerHTML += `<option value="${c.cawanganID}">${c.cawanganName}</option>`;
                    });
                    cawSelect.disabled = false;
                })
                .catch(error => {
                    cawSelect.innerHTML = '<option value="">Error loading cawangan</option>';
                    cawSelect.disabled = false;
                });
        }

        function toggleRoleFields() {
            const role = document.getElementById("role").value;
            const deptSection = document.getElementById("deptSection");
            
            if (role === "urusetia") {
                deptSection.style.display = "block";
            } else {
                deptSection.style.display = "none";
                // Clear department and cawangan selections for admin
                document.getElementById('deptID').value = '';
                document.getElementById('cawanganID').innerHTML = '<option value="">-- Select Department First --</option>';
            }
            
            // Update role description
            updateRoleDescription(role);
        }
        
        function updateRoleDescription(role) {
            const description = document.getElementById('roleDescription');
            if (role === 'admin') {
                description.textContent = 'Admin users have full access to all departments and system functions.';
            } else if (role === 'urusetia') {
                description.textContent = 'Urusetia users are restricted to their assigned department and cawangan.';
            } else {
                description.textContent = 'Select a role to see description.';
            }
        }

        function validatePassword() {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm').value;
            const feedback = document.getElementById('passwordFeedback');
            
            if (password !== confirm && confirm !== '') {
                feedback.textContent = 'Passwords do not match!';
                feedback.className = 'password-requirements text-danger';
            } else {
                feedback.textContent = '';
            }
        }

        window.onload = function() {
            toggleRoleFields();
        };
    </script>
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
                    <li class="breadcrumb-item"><a href="manage_users.php">Manage Users</a></li>
                    <li class="breadcrumb-item active">Register New User</li>
                </ol>
            </nav>
            <h2>👤 Register New User</h2>
            <p class="text-muted">Create a new user account with appropriate permissions</p>
            
            <div class="mt-3">
                <a href="manage_users.php" class="btn btn-outline">⬅️ Back to Manage Users</a>
            </div>
        </div>

        <!-- Registration Form -->
        <div class="form-container">
            <form method="POST">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="form-section-title">Basic Information</div>
                    
                    <div class="mb-3">
                        <label for="userIC" class="form-label">User IC (Login ID):</label>
                        <input type="text" class="form-control" name="userIC" id="userIC" required placeholder="Enter user IC number">
                    </div>

                    <div class="mb-3">
                        <label for="userName" class="form-label">Full Name:</label>
                        <input type="text" class="form-control" name="userName" id="userName" required placeholder="Enter full name">
                    </div>
                </div>

                <!-- Role and Permissions Section -->
                <div class="form-section">
                    <div class="form-section-title">Role and Permissions</div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role:</label>
                        <select class="form-select" name="role" id="role" required onchange="toggleRoleFields()">
                            <option value="">-- Select Role --</option>
                            <option value="admin">Admin</option>
                            <option value="urusetia">Urusetia</option>
                        </select>
                        <div id="roleDescription" class="role-description">Select a role to see description.</div>
                    </div>

                    <div id="deptSection" style="display:none;">
                        <div class="mb-3">
                            <label for="deptID" class="form-label">Department:</label>
                            <select class="form-select" name="deptID" id="deptID" onchange="loadCawangan()">
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept['deptID']); ?>"><?= htmlspecialchars($dept['deptName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="cawanganID" class="form-label">Cawangan:</label>
                            <select class="form-select" name="cawanganID" id="cawanganID">
                                <option value="">-- Select Cawangan --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Security Section -->
                <div class="form-section">
                    <div class="form-section-title">Security</div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" class="form-control" name="password" id="password" required placeholder="Enter password">
                        <div class="password-requirements">Use a strong password with letters, numbers, and symbols.</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm" class="form-label">Confirm Password:</label>
                        <input type="password" class="form-control" name="confirm" id="confirm" required placeholder="Confirm password" onkeyup="validatePassword()">
                        <div id="passwordFeedback" class="password-requirements"></div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="manage_users.php" class="btn btn-outline me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">💾 Register User</button>
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

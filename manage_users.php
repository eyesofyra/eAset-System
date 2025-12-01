<?php
session_start();
include 'db_connect.php';

// Restrict access to Admin only
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

// Handle user deletion
if (isset($_GET['delete'])) {
    $deleteID = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE userID = :id");
    $stmt->execute([':id' => $deleteID]);
    echo "<script>alert('✅ User deleted successfully.');window.location.href='manage_users.php';</script>";
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $updateStmt = $pdo->prepare("
        UPDATE users SET userName = :userName, deptID = :deptID, cawanganID = :cawanganID, levelID = :levelID
        WHERE userID = :userID
    ");
    $updateStmt->execute([
        ':userName' => $_POST['userName'],
        ':deptID' => $_POST['deptID'] ?: null,
        ':cawanganID' => $_POST['cawanganID'] ?: null,
        ':levelID' => $_POST['levelID'],
        ':userID' => $_POST['userID']
    ]);
    echo "<script>alert('✅ User updated successfully.');window.location.href='manage_users.php';</script>";
    exit;
}

// Fetch all users with department & cawangan
$stmt = $pdo->query("
    SELECT u.userID, u.userName, u.userIC, u.levelID, d.deptID, d.deptName, c.cawanganID, c.cawanganName
    FROM users u
    LEFT JOIN department d ON u.deptID = d.deptID
    LEFT JOIN cawangan c ON u.cawanganID = c.cawanganID
    ORDER BY u.userName ASC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get department list for dropdown
$departments = $pdo->query("SELECT deptID, deptName FROM department ORDER BY deptName ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users - ICT Aset</title>
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
    
    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
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
        padding: 12px 15px;
        border-bottom: 1px solid #eaeaea;
    }
    
    tr:nth-child(even) {
        background-color: #f9fafb;
    }
    
    tr:hover {
        background-color: #f0f4f8;
    }

    /* MODAL STYLING */
    .modal-content {
        border-radius: 10px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .modal-header {
        background-color: var(--deep-navy);
        color: var(--gold);
        border-bottom: 2px solid var(--gold);
    }
    
    .modal-title {
        font-weight: 600;
    }
    
    .btn-close {
        filter: invert(1);
    }
    
    .form-label {
        color: var(--deep-navy);
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 5px;
        padding: 8px 12px;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--gold);
        box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
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
        
        .btn {
            padding: 6px 12px;
            font-size: 14px;
        }
    }
</style>
<script>
function loadCawangan(deptID, cawanganSelectID, selectedID = null) {
    const cawSelect = document.getElementById(cawanganSelectID);
    if (!deptID) {
        cawSelect.innerHTML = '<option value="">-- Select Department First --</option>';
        return;
    }
    fetch('get_cawangan.php?deptID=' + deptID)
        .then(res => res.json())
        .then(data => {
            cawSelect.innerHTML = '<option value="">-- Select Cawangan --</option>';
            data.forEach(c => {
                let sel = selectedID && selectedID == c.cawanganID ? 'selected' : '';
                cawSelect.innerHTML += `<option value="${c.cawanganID}" ${sel}>${c.cawanganName}</option>`;
            });
        });
}
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
                    <li class="breadcrumb-item active">Manage Users</li>
                </ol>
            </nav>
            <h2>👥 Manage Users</h2>
            <p class="text-muted">Manage user accounts and permissions</p>
            
            <div class="mt-3">
                <a href="admin_dashboard.php" class="btn btn-outline">⬅️ Back to Dashboard</a>
                <a href="register_user.php" class="btn btn-primary">➕ Register New User</a>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User IC</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Cawangan</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['userIC']); ?></td>
                        <td><?= htmlspecialchars($u['userName']); ?></td>
                        <td>
                            <span class="badge <?= ($u['levelID'] == 1001) ? 'bg-primary' : 'bg-secondary'; ?>">
                                <?= ($u['levelID'] == 1001) ? 'Admin' : 'Urusetia'; ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($u['deptName'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($u['cawanganName'] ?? '-'); ?></td>
                        <td>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modal-<?= $u['userID']; ?>">
                                Edit
                            </button>
                            <a href="manage_users.php?delete=<?= $u['userID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">
                                Delete
                            </a>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="modal-<?= $u['userID']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="userID" value="<?= $u['userID']; ?>">
                                        <input type="hidden" name="update_user" value="1">

                                        <div class="mb-3">
                                            <label class="form-label">Full Name:</label>
                                            <input type="text" class="form-control" name="userName" value="<?= htmlspecialchars($u['userName']); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Role:</label>
                                            <select class="form-select" name="levelID" required>
                                                <option value="1001" <?= $u['levelID']==1001?'selected':''; ?>>Admin</option>
                                                <option value="2001" <?= $u['levelID']==2001?'selected':''; ?>>Urusetia</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Department:</label>
                                            <select class="form-select" name="deptID" id="dept-<?= $u['userID']; ?>" onchange="loadCawangan(this.value, 'caw-<?= $u['userID']; ?>')">
                                                <option value="">-- Select Department --</option>
                                                <?php foreach ($departments as $d): ?>
                                                    <option value="<?= $d['deptID']; ?>" <?= $u['deptID']==$d['deptID']?'selected':''; ?>>
                                                        <?= htmlspecialchars($d['deptName']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Cawangan:</label>
                                            <select class="form-select" name="cawanganID" id="caw-<?= $u['userID']; ?>">
                                                <?php if($u['cawanganID']): ?>
                                                    <option value="<?= $u['cawanganID']; ?>"><?= htmlspecialchars($u['cawanganName']); ?></option>
                                                <?php else: ?>
                                                    <option value="">-- Select Cawangan --</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const deptSelect = document.getElementById('dept-<?= $u['userID']; ?>');
                        const cawSelect = document.getElementById('caw-<?= $u['userID']; ?>');
                        if (deptSelect.value && cawSelect.options.length<=1) {
                            loadCawangan(deptSelect.value, 'caw-<?= $u['userID']; ?>', '<?= $u['cawanganID'] ?? ''; ?>');
                        }
                    });
                    </script>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <h4 class="text-muted">No users found</h4>
                <p class="text-muted">Register new users to get started</p>
                <a href="register_user.php" class="btn btn-primary">➕ Register New User</a>
            </div>
        <?php endif; ?>

        <div class="text-center mt-5 footer">
            ICT Aset Management System © <?= date('Y'); ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

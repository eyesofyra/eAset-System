<?php
session_start();
include 'db_connect.php';

// Restrict to Admin only
if (!isset($_SESSION['userID']) || $_SESSION['levelID'] != 1001) {
    header("Location: login.php");
    exit;
}

// Session timeout
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    echo "<script>alert('Session expired. Please login again.');window.location.href='login.php';</script>";
    exit;
}
$_SESSION['last_activity'] = time();

// Get user ID
if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit;
}
$userID = $_GET['id'];

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE userID = :id");
$stmt->bindParam(':id', $userID);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<script>alert('User not found.');window.location.href='manage_users.php';</script>";
    exit;
}

// Fetch departments
$stmtDept = $pdo->query("SELECT deptID, deptName FROM department ORDER BY deptName ASC");
$departments = $stmtDept->fetchAll(PDO::FETCH_ASSOC);

// Handle form update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userName = $_POST['userName'];
    $role = $_POST['role'];
    $deptID = $_POST['deptID'] ?? null;
    $newPassword = $_POST['newPassword'] ?? null;
    $confirmPassword = $_POST['confirmPassword'] ?? null;

    $levelID = ($role == 'admin') ? 1001 : 2001;

    if (!empty($newPassword)) {
        if ($newPassword !== $confirmPassword) {
            echo "<script>alert('New password and confirmation do not match.');</script>";
        } else {
            // Update with password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmtUpdate = $pdo->prepare("
                UPDATE users 
                SET userName = :userName, levelID = :levelID, deptID = :deptID, userPassword = :userPassword 
                WHERE userID = :id
            ");
            $stmtUpdate->execute([
                ':userName' => $userName,
                ':levelID' => $levelID,
                ':deptID' => $deptID,
                ':userPassword' => $hashedPassword,
                ':id' => $userID
            ]);
            echo "<script>alert('✅ User details and password updated successfully!');window.location.href='manage_users.php';</script>";
        }
    } else {
        // Update without password
        $stmtUpdate = $pdo->prepare("
            UPDATE users 
            SET userName = :userName, levelID = :levelID, deptID = :deptID 
            WHERE userID = :id
        ");
        $stmtUpdate->execute([
            ':userName' => $userName,
            ':levelID' => $levelID,
            ':deptID' => $deptID,
            ':id' => $userID
        ]);
        echo "<script>alert('✅ User details updated successfully!');window.location.href='manage_users.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - ICT Aset</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        form { max-width: 450px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 15px; padding: 8px 16px; }
    </style>
    <script>
        function toggleDept() {
        const role = document.getElementById("role").value;
        const deptSection = document.getElementById("deptSection");
        deptSection.style.display = (role === "urusetia") ? "block" : "none";
    }

    // ✅ Auto-run on page load
    window.onload = toggleDept;
        function toggleDept() {
            const role = document.getElementById("role").value;
            const deptSection = document.getElementById("deptSection");
            deptSection.style.display = (role === "urusetia") ? "block" : "none";
        }
    </script>
</head>
<body>
    <h2>Edit User Account</h2>
    <p><a href="manage_users.php">⬅️ Back to Manage Users</a></p>
    <hr>

    <form method="POST" action="">
        <label for="userIC">User IC (Login ID):</label>
        <input type="text" id="userIC" value="<?= htmlspecialchars($user['userIC']); ?>" disabled>

        <label for="userName">Full Name:</label>
        <input type="text" name="userName" id="userName" required value="<?= htmlspecialchars($user['userName']); ?>">

        <label for="role">Role:</label>
        <select name="role" id="role" required onchange="toggleDept()">
            <option value="admin" <?= ($user['levelID'] == 1001) ? 'selected' : ''; ?>>Admin</option>
            <option value="urusetia" <?= ($user['levelID'] == 2001) ? 'selected' : ''; ?>>Urusetia</option>
        </select>

        <div id="deptSection" style="display: <?= ($user['levelID'] == 2001) ? 'block' : 'none'; ?>;">
            <label for="deptID">Assign Department:</label>
            <select name="deptID" id="deptID">
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept['deptID']); ?>"
                        <?= ($user['deptID'] == $dept['deptID']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($dept['deptName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <label for="newPassword">New Password (optional):</label>
        <input type="password" name="newPassword" id="newPassword">

        <label for="confirmPassword">Confirm New Password:</label>
        <input type="password" name="confirmPassword" id="confirmPassword">

        <button type="submit">Update User</button>
    </form>
</body>
</html>

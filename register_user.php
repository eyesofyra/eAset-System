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
    $role = $_POST['role'];
    $levelID = ($role === 'admin') ? 1001 : 2001;

    if ($password !== $confirm) {
        echo "<script>alert('Password and confirmation do not match.');</script>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (userIC, userName, userPassword, deptID, levelID)
            VALUES (:userIC, :userName, :userPassword, :deptID, :levelID)
        ");
        $stmt->bindParam(':userIC', $userIC);
        $stmt->bindParam(':userName', $userName);
        $stmt->bindParam(':userPassword', $hashedPassword);
        $stmt->bindParam(':deptID', $deptID);
        $stmt->bindParam(':levelID', $levelID);

        if ($stmt->execute()) {
            echo "<script>alert('✅ New user registered successfully!');window.location.href='manage_users.php';</script>";
        } else {
            echo "<script>alert('❌ Failed to register user.');</script>";
        }
    }
}

// Get department list for dropdown
$stmtDept = $pdo->query("SELECT deptID, deptName FROM department ORDER BY deptName ASC");
$departments = $stmtDept->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register New User - ICT Aset</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        form { max-width: 450px; margin-top: 20px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 15px; padding: 8px 16px; }
        .note { font-size: 0.9em; color: gray; margin-top: 5px; }
    </style>
</head>
<body>
    <h2>Register New User</h2>
    <p><a href="manage_users.php">⬅️ Back to Manage Users</a> | <a href="logout.php">Logout</a></p>
    <hr>

    <form method="POST" action="">
        <label for="userIC">User IC (Login ID):</label>
        <input type="text" name="userIC" id="userIC" required>

        <label for="userName">Full Name:</label>
        <input type="text" name="userName" id="userName" required>

        <label for="role">Role:</label>
        <select name="role" id="role" required onchange="toggleDept()">
            <option value="">-- Select Role --</option>
            <option value="admin">Admin</option>
            <option value="urusetia">Urusetia</option>
        </select>

        <div id="deptSection" style="display:none;">
            <label for="deptID">Assign Department:</label>
            <select name="deptID" id="deptID">
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept['deptID']); ?>">
                        <?= htmlspecialchars($dept['deptName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm">Confirm Password:</label>
        <input type="password" name="confirm" id="confirm" required>

        <button type="submit">Register User</button>
    </form>

    <script>
        function toggleDept() {
            const role = document.getElementById("role").value;
            const deptSection = document.getElementById("deptSection");
            deptSection.style.display = (role === "urusetia") ? "block" : "none";
        }

        // ✅ Ensure correct display on reload or refresh
        window.onload = toggleDept;
    </script>
</body>
</html>

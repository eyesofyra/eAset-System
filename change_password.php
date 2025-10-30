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
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        form { max-width: 400px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type="password"] { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 15px; padding: 8px 16px; }
        a { text-decoration: none; color: #007BFF; }
        a:hover { text-decoration: underline; }
        .rules { font-size: 0.9em; color: gray; margin-top: 5px; }
    </style>
</head>
<body>
    <h2>Change Password</h2>
    <p>Logged in as: <strong><?= htmlspecialchars($userName); ?></strong> (<?= $levelID == 1001 ? 'Admin' : 'Urusetia'; ?>)</p>

    <a href="<?= $levelID == 1001 ? 'admin_dashboard.php' : 'urusetia_dashboard.php'; ?>">⬅️ Back to Dashboard</a>
    <hr>

    <form method="POST">
        <label>Old Password:</label>
        <input type="password" name="oldPassword" required>

        <label>New Password:</label>
        <input type="password" name="newPassword" required>
        <div class="rules">🔒 Must be at least 8 characters and include a number or symbol.</div>

        <label>Confirm New Password:</label>
        <input type="password" name="confirmPassword" required>

        <button type="submit">Update Password</button>
    </form>
</body>
</html>

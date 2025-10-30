<?php
session_start();

// If a user is already logged in, destroy the session before showing login form
if (isset($_SESSION['userID'])) {
    session_unset();
    session_destroy();
}

if (isset($_SESSION['userID'])) {
    // Redirect if already logged in
    if ($_SESSION['levelID'] == 1001) {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['levelID'] == 2001) {
        header("Location: urusetia_dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ICT Aset System - Login</title>
</head>
<body>
    <h2>ICT ASET SYSTEM JKDM LTAKL</h2>
    <form action="auth_login.php" method="POST">
        <label for="userIC">User IC:</label><br>
        <input type="text" name="userIC" id="userIC" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password" required><br><br>

        <label>Select Role:</label><br>
        <input type="radio" name="role" value="admin" required> Admin<br>
        <input type="radio" name="role" value="urusetia" required> Urusetia<br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>

<?php
session_start();

// If user is already logged in, go straight to their dashboard
if (isset($_SESSION['userID'])) {
    if ($_SESSION['levelID'] == 1001) {
        header("Location: admin_dashboard.php");
        exit;
    } elseif ($_SESSION['levelID'] == 2001) {
        header("Location: urusetia_dashboard.php");
        exit;
    }
}

// Otherwise, show a welcome message with login link
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ICT Aset JKDM LTAKL Information System</title>
</head>
<body>
    <h2>Welcome to JKDM ICT Aset System</h2>
    <p>This system is developed to manage ICT asset information efficiently and securely.</p>
    <ul>
        <li>Admin can manage and update all aset information.</li>
        <li>Urusetia can update nama pengguna and jawatan for their department only.</li>
    </ul>
    <br>
    <a href="login.php">➡️ Click here to Login</a>
</body>
</html>

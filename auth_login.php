<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userIC = $_POST['userIC'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Determine levelID based on role selected
    $levelID = ($role === "admin") ? 1001 : 2001;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE userIC = :userIC AND levelID = :levelID");
    $stmt->bindParam(':userIC', $userIC);
    $stmt->bindParam(':levelID', $levelID);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['userPassword'])) {
            // Start session
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['userName'] = $user['userName'];
            $_SESSION['levelID'] = $user['levelID'];
            $_SESSION['deptID'] = $user['deptID'];
            $_SESSION['last_activity'] = time();

            // Redirect based on role
            if ($user['levelID'] == 1001) {
                // ✅ Admin goes directly to main dashboard
                header("Location: admin_dashboard.php");
            } elseif ($user['levelID'] == 2001) {
                // ✅ Urusetia goes to their dashboard
                header("Location: urusetia_dashboard.php");
            }
            exit;
        } else {
            echo "<script>alert('Incorrect password.');window.history.back();</script>";
        }
    } else {
        echo "<script>alert('User not found or wrong role selected.');window.history.back();</script>";
    }
}
?>
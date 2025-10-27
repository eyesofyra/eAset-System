<?php
session_start();
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userIC = $_POST['userIC'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Users WHERE userIC='$userIC' AND userPassword='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['userID'] = $user['userID'];
        $_SESSION['levelID'] = $user['levelID'];
        $_SESSION['deptID'] = $user['deptID'];

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid IC or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - ICT Asset System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark-blue">

<div class="container d-flex justify-content-center align-items-center vh-100">
  <div class="card p-4 shadow-lg" style="width:400px;">
    <h3 class="text-center mb-3 text-primary">ICT Asset System</h3>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">User IC</label>
        <input type="text" name="userIC" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
  </div>
</div>

</body>
</html>
